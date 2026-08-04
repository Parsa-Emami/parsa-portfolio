<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class ExportStaticPortfolio extends Command
{
    protected $signature = 'portfolio:export-static
        {--output= : Absolute or project-relative output directory}
        {--base-url= : Public base URL, including the repository path}
        {--keep : Do not clean the output directory before exporting}';

    protected $description = 'Export the public Laravel portfolio as a GitHub Pages-compatible static site.';

    public function handle(Kernel $kernel, Filesystem $files): int
    {
        $outputPath = $this->resolveOutputPath((string) ($this->option('output') ?: config('static-export.output_path')));
        $baseUrl = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            $this->components->error('A valid absolute --base-url is required.');

            return self::FAILURE;
        }

        $this->prepareApplicationForStaticExport($baseUrl);

        if (! $this->option('keep')) {
            $files->deleteDirectory($outputPath);
        }

        $files->ensureDirectoryExists($outputPath);

        $this->components->task('Copying public assets', fn () => $this->copyPublicAssets($outputPath, $files));
        $this->components->task('Copying uploaded public media', fn () => $this->copyStorageAssets($outputPath, $files));

        $routes = [
            '/' => 'index.html',
            '/sitemap.xml' => 'sitemap.xml',
            '/robots.txt' => 'robots.txt',
            '/site.webmanifest' => 'site.webmanifest',
        ];

        Project::query()
            ->published()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->each(function (Project $project) use (&$routes): void {
                $routes['/projects/'.$project->slug] = 'projects/'.$project->slug.'/index.html';
            });

        foreach ($routes as $uri => $destination) {
            $this->renderRoute($kernel, $uri, $outputPath.DIRECTORY_SEPARATOR.$destination, $baseUrl, $files);
        }

        $this->renderNotFoundPage($outputPath, $files);
        $this->writeDeploymentFiles($outputPath, $baseUrl, $files);
        $this->validateExport($outputPath, $routes, $files);

        $this->newLine();
        $this->components->info('Static portfolio exported successfully.');
        $this->line('Output: '.$outputPath);
        $this->line('Base URL: '.$baseUrl);
        $this->line('Pages: '.count($routes).' + custom 404');

        return self::SUCCESS;
    }

    private function prepareApplicationForStaticExport(string $baseUrl): void
    {
        config([
            'app.url' => $baseUrl,
            'app.asset_url' => $baseUrl,
            'static-export.enabled' => true,
            'filesystems.disks.public.url' => $baseUrl.'/storage',
        ]);

        URL::forceRootUrl($baseUrl);
        URL::forceScheme((string) parse_url($baseUrl, PHP_URL_SCHEME));
    }

    private function renderRoute(
        Kernel $kernel,
        string $uri,
        string $destination,
        string $baseUrl,
        Filesystem $files,
    ): void {
        $host = (string) parse_url($baseUrl, PHP_URL_HOST);
        $scheme = (string) parse_url($baseUrl, PHP_URL_SCHEME);
        $port = $scheme === 'https' ? 443 : 80;

        $request = Request::create($uri, 'GET', server: [
            'HTTP_HOST' => $host,
            'HTTPS' => $scheme === 'https' ? 'on' : 'off',
            'SERVER_NAME' => $host,
            'SERVER_PORT' => $port,
            'REQUEST_SCHEME' => $scheme,
        ]);

        $response = $kernel->handle($request);

        try {
            if ($response->getStatusCode() >= 400) {
                throw new RuntimeException("Static export failed for {$uri}: HTTP {$response->getStatusCode()}.");
            }

            $content = (string) $response->getContent();

            if ($content === '') {
                throw new RuntimeException("Static export produced an empty response for {$uri}.");
            }

            $files->ensureDirectoryExists(dirname($destination));
            $files->put($destination, $content);
            $this->components->twoColumnDetail($uri, '<fg=green>exported</>');
        } finally {
            $kernel->terminate($request, $response);
        }
    }

    private function renderNotFoundPage(string $outputPath, Filesystem $files): void
    {
        $html = view('errors.404')->render();
        $files->put($outputPath.DIRECTORY_SEPARATOR.'404.html', $html);
    }

    private function copyPublicAssets(string $outputPath, Filesystem $files): void
    {
        $publicPath = public_path();

        if (! is_dir($publicPath)) {
            throw new RuntimeException('The public directory does not exist.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if (! $item->isFile() || $item->isLink()) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($publicPath) + 1));
            $firstSegment = explode('/', $relative, 2)[0];

            if (in_array($firstSegment, ['storage', 'hot'], true)) {
                continue;
            }

            if (in_array($relative, ['index.php', '.htaccess', 'robots.txt'], true) || str_ends_with($relative, '.php')) {
                continue;
            }

            $destination = $outputPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $files->ensureDirectoryExists(dirname($destination));
            $files->copy($item->getPathname(), $destination);
        }
    }

    private function copyStorageAssets(string $outputPath, Filesystem $files): void
    {
        $source = storage_path('app/public');

        if (! is_dir($source)) {
            return;
        }

        $destination = $outputPath.DIRECTORY_SEPARATOR.'storage';
        $files->ensureDirectoryExists($destination);
        $files->copyDirectory($source, $destination);
    }

    private function writeDeploymentFiles(string $outputPath, string $baseUrl, Filesystem $files): void
    {
        $files->put($outputPath.DIRECTORY_SEPARATOR.'.nojekyll', "\n");

        $cname = trim((string) config('static-export.cname'));

        if ($cname !== '') {
            $files->put($outputPath.DIRECTORY_SEPARATOR.'CNAME', $cname."\n");
        }

        $files->put(
            $outputPath.DIRECTORY_SEPARATOR.'build-meta.json',
            json_encode([
                'generated_at' => now()->toAtomString(),
                'base_url' => $baseUrl,
                'commit' => env('GITHUB_SHA') ?: env('APP_COMMIT_SHA'),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );
    }

    /**
     * @param array<string, string> $routes
     */
    private function validateExport(string $outputPath, array $routes, Filesystem $files): void
    {
        foreach ($routes as $destination) {
            $path = $outputPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $destination);

            if (! $files->exists($path) || $files->size($path) === 0) {
                throw new RuntimeException('Missing or empty exported file: '.$destination);
            }
        }

        $required = ['index.html', '404.html', 'site.webmanifest', 'robots.txt', 'sitemap.xml', '.nojekyll'];

        foreach ($required as $file) {
            if (! $files->exists($outputPath.DIRECTORY_SEPARATOR.$file)) {
                throw new RuntimeException('Static export validation failed: '.$file.' is missing.');
            }
        }

        if (! is_dir($outputPath.DIRECTORY_SEPARATOR.'build')) {
            throw new RuntimeException('The Vite build directory is missing. Run npm run build before exporting.');
        }

        $index = $files->get($outputPath.DIRECTORY_SEPARATOR.'index.html');

        if (str_contains($index, 'http://localhost') || str_contains($index, 'http://127.0.0.1')) {
            throw new RuntimeException('The generated HTML still contains a local development URL.');
        }
    }

    private function resolveOutputPath(string $path): string
    {
        if ($path === '') {
            return storage_path('app/static-export');
        }

        $isWindowsAbsolute = preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
        $isUnixAbsolute = str_starts_with($path, '/');

        return ($isWindowsAbsolute || $isUnixAbsolute)
            ? rtrim($path, '\\/')
            : base_path(rtrim($path, '\\/'));
    }
}

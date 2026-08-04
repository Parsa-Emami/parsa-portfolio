<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('portfolio.index') }}</loc><lastmod>{{ now()->toAtomString() }}</lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>
    @foreach($projects as $project)
        <url><loc>{{ route('portfolio.projects.show', $project) }}</loc><lastmod>{{ $project->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    @endforeach
</urlset>

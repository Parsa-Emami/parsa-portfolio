@extends('layouts.portfolio')

@section('title', $project->seo_title.' — '.($settings['name'] ?? config('portfolio.name')))
@section('description', $project->seo_description)
@section('canonical', route('portfolio.projects.show', $project))
@section('og_type', 'article')
@section('og_image', $project->og_image ?: $project->cover_image ?: ($settings['site_og_image'] ?? ''))

@push('structured-data')
<script nonce="{{ $cspNonce ?? '' }}" type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareSourceCode',
    'name' => $project->title,
    'description' => $project->seo_description,
    'dateCreated' => $project->year ? $project->year.'-01-01' : null,
    'author' => ['@type' => 'Person', 'name' => $settings['name'] ?? config('portfolio.name')],
    'codeRepository' => $project->github_url,
    'url' => route('portfolio.projects.show', $project),
    'programmingLanguage' => $project->technologies,
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<article class="case-study" style="--project-accent: {{ $project->accent }}">
    <header class="case-hero section-shell">
        <a class="back-link" href="{{ route('portfolio.index') }}#work" data-transition-link>← Back to selected work</a>
        <div class="case-heading">
            <p class="case-eyebrow" data-reveal>{{ $project->eyebrow }}</p>
            <h1 data-reveal>{{ $project->title }}</h1>
            <p class="case-lead" data-reveal>{{ $project->summary }}</p>
        </div>
        <dl class="case-facts" data-reveal>
            <div><dt>Role</dt><dd>{{ $project->role ?: 'Full-stack Development' }}</dd></div>
            @if($project->client)<div><dt>Context</dt><dd>{{ $project->client }}</dd></div>@endif
            <div><dt>Year</dt><dd>{{ $project->year ?: '—' }}</dd></div>
            <div><dt>Stack</dt><dd>{{ implode(', ', $project->technologies ?? []) }}</dd></div>
        </dl>
    </header>

    <section class="case-visual section-shell" data-project>
        <div class="case-canvas" data-parallax>
            @if ($project->cover_image)
                <img class="case-cover-image" src="{{ Storage::url($project->cover_image) }}" alt="{{ $project->cover_alt ?: $project->title }}" width="1800" height="1100">
            @else
                <div class="project-grid"></div><div class="case-monogram">{{ strtoupper(mb_substr($project->title, 0, 2)) }}</div>
            @endif
        </div>
    </section>

    <section class="case-introduction section-shell">
        <p class="case-section-label" data-reveal>Overview</p>
        <div class="case-introduction-copy" data-reveal>
            @foreach (preg_split('/\R{2,}/', trim($project->content ?? $project->summary)) as $paragraph)<p>{{ $paragraph }}</p>@endforeach
            <div class="case-actions">
                @if ($project->github_url)<a href="{{ $project->github_url }}" target="_blank" rel="noreferrer">View source ↗</a>@endif
                @if ($project->live_url)<a href="{{ $project->live_url }}" target="_blank" rel="noreferrer">Visit live project ↗</a>@endif
                @if ($project->video_url)<a href="{{ $project->video_url }}" target="_blank" rel="noreferrer">Watch project video ↗</a>@endif
            </div>
        </div>
    </section>

    @php($narratives = [
        ['index' => '01', 'label' => 'Challenge', 'value' => $project->challenge],
        ['index' => '02', 'label' => 'Solution', 'value' => $project->solution],
        ['index' => '03', 'label' => 'Architecture', 'value' => $project->architecture],
        ['index' => '04', 'label' => 'Outcome', 'value' => $project->results],
    ])
    <section class="case-narrative section-shell">
        @foreach($narratives as $item)
            @if($item['value'])
                <article class="narrative-row" data-reveal>
                    <span>{{ $item['index'] }}</span><h2>{{ $item['label'] }}</h2>
                    <div>@foreach(preg_split('/\R{2,}/', trim($item['value'])) as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>
                </article>
            @endif
        @endforeach
    </section>

    @if($project->media->isNotEmpty())
        <section class="case-gallery section-shell" aria-label="Project gallery">
            <div class="gallery-heading" data-reveal><p>Project gallery</p><span>{{ str_pad((string)$project->media->count(), 2, '0', STR_PAD_LEFT) }} pieces</span></div>
            <div class="gallery-grid">
                @foreach($project->media as $media)
                    <figure class="gallery-item gallery-item-{{ $media->display_size }}" data-gallery-item data-reveal>
                        @if($media->type === 'video')
                            <div class="gallery-video"><iframe src="{{ $media->external_url }}" title="{{ $media->alt_text ?: $project->title.' video' }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
                        @else
                            <button type="button" data-lightbox-open data-src="{{ $media->url }}" data-alt="{{ $media->alt_text ?: $project->title }}">
                                <img src="{{ $media->thumbnail_url }}" data-full="{{ $media->url }}" alt="{{ $media->alt_text ?: $project->title }}" loading="lazy" width="{{ $media->width ?: 1400 }}" height="{{ $media->height ?: 900 }}">
                                <span>Expand ↗</span>
                            </button>
                        @endif
                        @if($media->caption)<figcaption>{{ $media->caption }}</figcaption>@endif
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    <section class="project-navigation section-shell">
        @if($previousProject)<a href="{{ route('portfolio.projects.show', $previousProject) }}" data-transition-link><small>Previous project</small><strong>← {{ $previousProject->title }}</strong></a>@endif
        @if($nextProject)<a href="{{ route('portfolio.projects.show', $nextProject) }}" data-transition-link><small>Next project</small><strong>{{ $nextProject->title }} →</strong></a>@endif
    </section>
</article>

<div class="lightbox" data-lightbox hidden>
    <button type="button" data-lightbox-close aria-label="Close image">×</button>
    <img src="" alt="" data-lightbox-image>
</div>
@endsection

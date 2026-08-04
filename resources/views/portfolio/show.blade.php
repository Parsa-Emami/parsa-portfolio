@extends('layouts.portfolio')

@section('title', $project->title . ' — ' . ($settings['name'] ?? config('portfolio.name')))

@section('content')
    <article class="case-study" style="--project-accent: {{ $project->accent }}">
        <header class="case-hero section-shell">
            <a class="back-link" href="{{ route('portfolio.index') }}#work">← Back to work</a>

            <p class="case-eyebrow" data-reveal>{{ $project->eyebrow }}</p>
            <h1 data-reveal>{{ $project->title }}</h1>

            <div class="case-summary" data-reveal>
                <p>{{ $project->summary }}</p>
            </div>

            <dl class="case-facts" data-reveal>
                <div>
                    <dt>Role</dt>
                    <dd>{{ $project->role ?: 'Full-stack Development' }}</dd>
                </div>
                <div>
                    <dt>Year</dt>
                    <dd>{{ $project->year }}</dd>
                </div>
                <div>
                    <dt>Stack</dt>
                    <dd>{{ implode(', ', $project->technologies ?? []) }}</dd>
                </div>
            </dl>
        </header>

        <section class="case-visual section-shell" data-project>
            <div class="case-canvas">
                @if ($project->cover_image)
                    <img class="case-cover-image" src="{{ Storage::url($project->cover_image) }}" alt="{{ $project->title }} cover">
                @else
                    <div class="project-grid"></div>
                    <div class="case-monogram">{{ strtoupper(mb_substr($project->title, 0, 2)) }}</div>
                @endif
            </div>
        </section>

        <section class="case-content section-shell">
            <div class="case-content-label" data-reveal>Overview</div>
            <div class="case-content-body" data-reveal>
                @foreach (preg_split('/\R{2,}/', trim($project->content ?? $project->summary)) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach

                <div class="case-actions">
                    @if ($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" rel="noreferrer">View source ↗</a>
                    @endif
                    @if ($project->live_url)
                        <a href="{{ $project->live_url }}" target="_blank" rel="noreferrer">Visit project ↗</a>
                    @endif
                </div>
            </div>
        </section>

        @if ($nextProject)
            <section class="next-project section-shell">
                <p>Next project</p>
                <a href="{{ route('portfolio.projects.show', $nextProject) }}">
                    <span>{{ $nextProject->title }}</span>
                    <span>↗</span>
                </a>
            </section>
        @endif
    </article>
@endsection

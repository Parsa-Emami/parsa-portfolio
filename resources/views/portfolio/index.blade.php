@extends('layouts.portfolio')

@section('content')
    <section class="hero section-shell" id="top">
        <div class="hero-kicker" data-reveal>
            <span>Independent developer</span>
            <span>{{ config('portfolio.location') }}</span>
        </div>

        <div class="hero-title" aria-label="I build digital worlds and useful products">
            <div class="hero-line"><span data-hero-line>I build digital</span></div>
            <div class="hero-line hero-line-offset"><span data-hero-line>worlds & useful</span></div>
            <div class="hero-line"><span data-hero-line>products.</span></div>
        </div>

        <div class="hero-bottom">
            <p class="hero-intro" data-reveal>
                Laravel developer and creative technologist focused on expressive interfaces,
                maintainable systems and products people enjoy using.
            </p>

            <a class="scroll-cue" href="#work" data-reveal>
                <span>Selected work</span>
                <span class="scroll-cue-icon">↓</span>
            </a>
        </div>

        <div class="hero-orbit" aria-hidden="true" data-orbit>
            <span>Laravel</span>
            <span>Creative code</span>
            <span>Systems</span>
        </div>
    </section>

    <section class="work section-shell" id="work">
        <div class="section-heading" data-reveal>
            <p>01 / Selected work</p>
            <h2>Projects built to move, scale and stay understandable.</h2>
        </div>

        <div class="project-list">
            @forelse ($projects as $project)
                <article class="project-card" data-project style="--project-accent: {{ $project->accent }}">
                    <a href="{{ route('portfolio.projects.show', $project) }}" class="project-link" aria-label="View {{ $project->title }} case study">
                        <div class="project-meta">
                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span>{{ $project->eyebrow }}</span>
                            <span>{{ $project->year }}</span>
                        </div>

                        <div class="project-visual" aria-hidden="true">
                            <div class="project-grid"></div>
                            <div class="project-window">
                                <span></span><span></span><span></span>
                                <strong>{{ strtoupper(mb_substr($project->title, 0, 2)) }}</strong>
                            </div>
                            <div class="project-glow"></div>
                        </div>

                        <div class="project-copy">
                            <h3>{{ $project->title }}</h3>
                            <p>{{ $project->summary }}</p>

                            <div class="project-footer">
                                <ul>
                                    @foreach (array_slice($project->technologies ?? [], 0, 4) as $technology)
                                        <li>{{ $technology }}</li>
                                    @endforeach
                                </ul>
                                <span class="project-arrow">↗</span>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <p class="empty-state">Projects are being prepared.</p>
            @endforelse
        </div>
    </section>

    <section class="about section-shell" id="about">
        <div class="section-heading" data-reveal>
            <p>02 / About</p>
            <h2>I connect visual ideas to backend systems that can actually support them.</h2>
        </div>

        <div class="about-grid">
            <div class="about-statement" data-reveal>
                <p>
                    My work sits between product thinking, interface engineering and backend architecture.
                    I enjoy turning ambitious visual concepts into reliable Laravel applications.
                </p>
            </div>

            <div class="about-details" data-reveal>
                <div>
                    <span>Core stack</span>
                    <p>Laravel, PHP, Blade, Livewire, MySQL, JavaScript, Vite</p>
                </div>
                <div>
                    <span>Focus</span>
                    <p>Portfolio experiences, web products, internal tools and interactive systems</p>
                </div>
                <div>
                    <span>Approach</span>
                    <p>Clear architecture, deliberate motion, measured performance and maintainable code</p>
                </div>
            </div>
        </div>
    </section>

    <section class="manifesto" aria-label="Design philosophy">
        <div class="manifesto-track" data-marquee>
            <span>Build clearly</span><i>✦</i>
            <span>Move deliberately</span><i>✦</i>
            <span>Stay curious</span><i>✦</i>
            <span>Build clearly</span><i>✦</i>
            <span>Move deliberately</span><i>✦</i>
            <span>Stay curious</span><i>✦</i>
        </div>
    </section>

    <section class="contact section-shell" id="contact">
        <p class="contact-label" data-reveal>03 / Start a conversation</p>
        <h2 data-reveal>Have an idea worth<br>building properly?</h2>
        <div class="contact-row" data-reveal>
            @if (config('portfolio.email'))
                <a href="mailto:{{ config('portfolio.email') }}">{{ config('portfolio.email') }}</a>
            @else
                <span>Add PORTFOLIO_EMAIL to your .env</span>
            @endif
            <a href="{{ config('portfolio.github_url') }}" target="_blank" rel="noreferrer">GitHub ↗</a>
        </div>
    </section>
@endsection

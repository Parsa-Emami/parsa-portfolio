@extends('layouts.portfolio')

@section('content')
    <section class="hero section-shell" id="top">
        <div class="hero-kicker" data-reveal>
            <span>Independent developer</span>
            <span>{{ $settings['location'] }}</span>
        </div>

        <div class="hero-title" aria-label="{{ $settings['hero_line_1'] }} {{ $settings['hero_line_2'] }} {{ $settings['hero_line_3'] }}">
            <div class="hero-line"><span data-hero-line>{{ $settings['hero_line_1'] }}</span></div>
            <div class="hero-line hero-line-offset"><span data-hero-line>{{ $settings['hero_line_2'] }}</span></div>
            <div class="hero-line"><span data-hero-line>{{ $settings['hero_line_3'] }}</span></div>
        </div>

        <div class="hero-bottom">
            <p class="hero-intro" data-reveal>{{ $settings['intro'] }}</p>

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
                            @if ($project->cover_image)
                                <img class="project-cover-image" src="{{ Storage::url($project->cover_image) }}" alt="" loading="lazy">
                            @else
                                <div class="project-grid"></div>
                                <div class="project-window">
                                    <span></span><span></span><span></span>
                                    <strong>{{ strtoupper(mb_substr($project->title, 0, 2)) }}</strong>
                                </div>
                                <div class="project-glow"></div>
                            @endif
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
            <h2>{{ $settings['about_heading'] }}</h2>
        </div>

        <div class="about-grid">
            <div class="about-statement" data-reveal>
                <p>{{ $settings['about_body'] }}</p>
            </div>

            <div class="about-details" data-reveal>
                <div>
                    <span>Core stack</span>
                    <p>{{ $settings['core_stack'] }}</p>
                </div>
                <div>
                    <span>Focus</span>
                    <p>{{ $settings['focus'] }}</p>
                </div>
                <div>
                    <span>Approach</span>
                    <p>{{ $settings['approach'] }}</p>
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
        <h2 data-reveal>{!! nl2br(e($settings['contact_heading'])) !!}</h2>

        <div class="contact-grid">
            <div class="contact-links" data-reveal>
                @if ($settings['email'])
                    <a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a>
                @endif
                <a href="{{ $settings['github_url'] }}" target="_blank" rel="noreferrer">GitHub ↗</a>
                @if ($settings['linkedin_url'])
                    <a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noreferrer">LinkedIn ↗</a>
                @endif
            </div>

            <form class="contact-form" method="POST" action="{{ route('portfolio.contact.store') }}" data-reveal>
                @csrf

                @if (session('contact_success'))
                    <div class="contact-alert contact-alert-success">{{ session('contact_success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="contact-alert contact-alert-error">Please review the highlighted fields.</div>
                @endif

                <div class="contact-form-row">
                    <label>
                        <span>Name</span>
                        <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                        @error('name') <small>{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email') <small>{{ $message }}</small> @enderror
                    </label>
                </div>

                <label>
                    <span>Subject</span>
                    <input type="text" name="subject" value="{{ old('subject') }}" autocomplete="off">
                    @error('subject') <small>{{ $message }}</small> @enderror
                </label>

                <label>
                    <span>Tell me about the project</span>
                    <textarea name="message" rows="6" required>{{ old('message') }}</textarea>
                    @error('message') <small>{{ $message }}</small> @enderror
                </label>

                <div class="contact-honeypot" aria-hidden="true">
                    <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <button type="submit">Send message <span>↗</span></button>
            </form>
        </div>
    </section>
@endsection

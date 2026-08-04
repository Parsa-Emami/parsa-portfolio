@extends('layouts.portfolio')

@section('title', $settings['seo_title'] ?? (($settings['name'] ?? config('portfolio.name')).' — Laravel Developer & Creative Technologist'))
@section('description', $settings['seo_description'] ?? 'Selected Laravel, web product and interactive development work.')

@push('structured-data')
<script nonce="{{ $cspNonce ?? '' }}" type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $settings['name'] ?? config('portfolio.name'),
    'jobTitle' => $settings['job_title'] ?? 'Laravel Developer & Creative Technologist',
    'url' => route('portfolio.index'),
    'email' => ($settings['email'] ?? null) ? 'mailto:'.$settings['email'] : null,
    'sameAs' => array_values(array_filter([$settings['github_url'] ?? null, $settings['linkedin_url'] ?? null])),
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="hero section-shell" data-hero>
    <div class="hero-topline" data-reveal>
        <span>{{ $settings['location'] }}</span>
        <span>Portfolio / {{ now()->year }}</span>
    </div>

    <div class="hero-title" aria-label="{{ $settings['hero_line_1'] }} {{ $settings['hero_line_2'] }} {{ $settings['hero_line_3'] }}">
        <div class="hero-line"><span data-hero-line>{{ $settings['hero_line_1'] }}</span></div>
        <div class="hero-line hero-line-offset"><span data-hero-line>{{ $settings['hero_line_2'] }}</span></div>
        <div class="hero-line"><span data-hero-line>{{ $settings['hero_line_3'] }}</span></div>
    </div>

    <div class="hero-orbit" data-orbit aria-hidden="true">
        <div class="orbit-core">PE</div><i></i><i></i><i></i>
        <span>Laravel</span><span>Motion</span><span>Systems</span>
    </div>

    <div class="hero-bottom">
        <p class="hero-intro" data-reveal>{{ $settings['intro'] }}</p>
        <dl class="hero-stats" data-reveal>
            <div><dt>{{ str_pad((string) $projects->count(), 2, '0', STR_PAD_LEFT) }}</dt><dd>Selected projects</dd></div>
            <div><dt>{{ $skills->flatten()->count() }}+</dt><dd>Core capabilities</dd></div>
        </dl>
        <a class="scroll-cue" href="#work" data-magnetic><span>Explore work</span><i>↓</i></a>
    </div>
</section>

<section class="work section-shell" id="work">
    <div class="section-heading" data-reveal>
        <p>01 / Selected work</p>
        <h2>Products, platforms and interactive systems built to keep evolving.</h2>
    </div>

    <div class="project-list">
        @forelse ($projects as $project)
            <article class="project-card" data-project style="--project-accent: {{ $project->accent }}">
                <a href="{{ route('portfolio.projects.show', $project) }}" class="project-link" data-transition-link aria-label="View {{ $project->title }} case study">
                    <div class="project-meta">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>{{ $project->eyebrow }}</span>
                        <span>{{ $project->year }}</span>
                    </div>
                    <div class="project-visual" data-parallax>
                        @if ($project->cover_image)
                            <img class="project-cover-image" src="{{ Storage::url($project->cover_image) }}" alt="{{ $project->cover_alt ?: $project->title }}" loading="lazy" width="1400" height="900">
                        @else
                            <div class="project-grid"></div>
                            <div class="project-window"><span></span><span></span><span></span><strong>{{ strtoupper(mb_substr($project->title, 0, 2)) }}</strong></div>
                            <div class="project-glow"></div>
                        @endif
                        <span class="project-view">View case study ↗</span>
                    </div>
                    <div class="project-copy">
                        <p>{{ $project->role ?: 'Product & Development' }}</p>
                        <h3>{{ $project->title }}</h3>
                        <p class="project-summary">{{ $project->summary }}</p>
                        <div class="project-footer"><ul>@foreach (array_slice($project->technologies ?? [], 0, 5) as $technology)<li>{{ $technology }}</li>@endforeach</ul><span>↗</span></div>
                    </div>
                </a>
            </article>
        @empty
            <p class="empty-state">Projects are being prepared.</p>
        @endforelse
    </div>
</section>

<section class="about section-shell" id="about">
    <div class="section-heading" data-reveal><p>02 / About</p><h2>{{ $settings['about_heading'] }}</h2></div>
    <div class="about-grid">
        <div class="about-statement" data-reveal><p>{{ $settings['about_body'] }}</p></div>
        <div class="about-details" data-reveal>
            <div><span>Core stack</span><p>{{ $settings['core_stack'] }}</p></div>
            <div><span>Focus</span><p>{{ $settings['focus'] }}</p></div>
            <div><span>Approach</span><p>{{ $settings['approach'] }}</p></div>
        </div>
    </div>
</section>

<section class="capabilities section-shell" id="capabilities">
    <div class="section-heading" data-reveal><p>03 / Capabilities</p><h2>A broad toolkit, organized around shipping reliable work.</h2></div>
    <div class="skill-groups">
        @foreach ($skills as $category => $items)
            <section class="skill-group" data-reveal>
                <div class="skill-group-heading"><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $category }}</h3></div>
                <div class="skill-list">
                    @foreach ($items as $skill)
                        <div class="skill-item"><span class="skill-code">{{ $skill->short_label ?: strtoupper(mb_substr($skill->name, 0, 3)) }}</span><strong>{{ $skill->name }}</strong>@if($skill->proficiency)<i style="--level: {{ $skill->proficiency }}%"><b></b></i><small>{{ $skill->proficiency }}%</small>@endif</div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</section>

<section class="experience section-shell" id="experience">
    <div class="section-heading" data-reveal><p>04 / Experience</p><h2>Working across product thinking, architecture and implementation.</h2></div>
    <div class="timeline">
        @forelse ($experiences as $experience)
            <article class="timeline-item" data-reveal>
                <div class="timeline-period">{{ $experience->period_label }}</div>
                <div class="timeline-role"><h3>{{ $experience->title }}</h3><p>{{ $experience->organization }}@if($experience->location) · {{ $experience->location }}@endif</p></div>
                <div class="timeline-copy"><p>{{ $experience->description }}</p>@if($experience->achievements)<ul>@foreach($experience->achievements as $achievement)<li>{{ $achievement }}</li>@endforeach</ul>@endif</div>
            </article>
        @empty
            <p class="empty-state">Experience timeline is being prepared.</p>
        @endforelse
    </div>
</section>

<section class="manifesto" aria-label="Design philosophy">
    @php($manifesto = preg_split('/\s*[·|]\s*/', $settings['manifesto'] ?? 'Build clearly · Move deliberately · Stay curious'))
    <div class="manifesto-track" data-marquee>@for($i=0;$i<3;$i++)@foreach($manifesto as $phrase)<span>{{ $phrase }}</span><i>✦</i>@endforeach @endfor</div>
</section>

<section class="contact section-shell" id="contact">
    <p class="contact-label" data-reveal>05 / Start a conversation</p>
    <h2 data-reveal>{!! nl2br(e($settings['contact_heading'])) !!}</h2>
    <div class="contact-grid">
        <div class="contact-links" data-reveal>
            <p>{{ $settings['contact_intro'] }}</p>
            @if ($settings['email'])<a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a>@endif
            <a href="{{ $settings['github_url'] }}" target="_blank" rel="noreferrer">GitHub ↗</a>
            @if ($settings['linkedin_url'])<a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noreferrer">LinkedIn ↗</a>@endif
            @if ($settings['resume_file'])<a href="{{ Storage::url($settings['resume_file']) }}" target="_blank" rel="noreferrer">Download résumé ↗</a>@endif
        </div>

        @if (config('static-export.enabled'))
            @php($staticContactEndpoint = trim((string) config('static-export.contact_endpoint')))
            @if ($staticContactEndpoint !== '')
                <form class="contact-form" method="POST" action="{{ $staticContactEndpoint }}" data-reveal>
                    <div class="contact-alert" role="status" aria-live="polite">This form is securely handled by an external form endpoint.</div>
                    <div class="contact-form-row">
                        <label><span>Name</span><input type="text" name="name" autocomplete="name" required></label>
                        <label><span>Email</span><input type="email" name="email" autocomplete="email" required></label>
                    </div>
                    <label><span>Subject</span><input type="text" name="subject" autocomplete="off"></label>
                    <label><span>Tell me about the project</span><textarea name="message" rows="6" required></textarea></label>
                    <input type="hidden" name="source" value="{{ route('portfolio.index') }}">
                    <button type="submit"><span>Send message</span><i>↗</i></button>
                </form>
            @elseif ($settings['email'])
                <form class="contact-form" method="GET" action="mailto:{{ $settings['email'] }}" data-static-contact-form data-contact-email="{{ $settings['email'] }}" data-reveal>
                    <div class="contact-alert" data-form-status role="status" aria-live="polite">Submitting opens your default email application.</div>
                    <div class="contact-form-row">
                        <label><span>Name</span><input type="text" name="name" autocomplete="name" required></label>
                        <label><span>Email</span><input type="email" name="email" autocomplete="email" required></label>
                    </div>
                    <label><span>Subject</span><input type="text" name="subject" autocomplete="off"></label>
                    <label><span>Tell me about the project</span><textarea name="message" rows="6" required></textarea></label>
                    <button type="submit"><span>Prepare email</span><i>↗</i></button>
                </form>
            @else
                <div class="contact-form" data-reveal>
                    <div class="contact-alert is-success">The GitHub Pages edition has no server-side inbox. Continue the conversation through GitHub.</div>
                    <a class="availability" href="{{ $settings['github_url'] }}" target="_blank" rel="noreferrer">Open GitHub profile ↗</a>
                </div>
            @endif
        @else
            <form class="contact-form" method="POST" action="{{ route('portfolio.contact.store') }}" data-contact-form data-reveal>
                @csrf
                <div class="contact-alert" data-form-status role="status" aria-live="polite">{{ session('contact_success') ?: session('contact_error') }}</div>
                <div class="contact-form-row">
                    <label><span>Name</span><input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required><small data-error="name">@error('name'){{ $message }}@enderror</small></label>
                    <label><span>Email</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required><small data-error="email">@error('email'){{ $message }}@enderror</small></label>
                </div>
                <label><span>Subject</span><input type="text" name="subject" value="{{ old('subject') }}" autocomplete="off"><small data-error="subject">@error('subject'){{ $message }}@enderror</small></label>
                <label><span>Tell me about the project</span><textarea name="message" rows="6" required>{{ old('message') }}</textarea><small data-error="message">@error('message'){{ $message }}@enderror</small></label>
                <div class="contact-honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <input type="hidden" name="started_at" value="{{ now()->timestamp }}">
                <button type="submit" data-submit-button><span>Send message</span><i>↗</i></button>
            </form>
        @endif
    </div>
</section>
@endsection

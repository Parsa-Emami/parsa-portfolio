<section class="s-projects" id="projects">
    <div class="s-projects__grid">
        
        {{-- فرض می‌کنیم این متغیر از کنترلر لاراول ارسال می‌شود --}}
        @foreach($projects as $project)
            <a href="{{ route('project.show', $project->slug ?? '#') }}" class="c-project">
                
                <div class="c-project__image-wrapper">
                    {{-- اتریبیوت data-parallax به JS می‌گوید که این عکس را انیمیت کند --}}
                    <img src="{{ $project->image_url ?? 'آدرس-عکس-پیشفرض.jpg' }}" 
                         alt="{{ $project->title }}" 
                         class="c-project__img" 
                         data-parallax>
                </div>
                
                <div class="c-project__info">
                    <h3 class="c-project__name">{{ $project->title }}</h3>
                    <span class="c-project__tech">{{ $project->category ?? 'Web Development' }}</span>
                </div>
                
            </a>
        @endforeach
        
    </div>
</section>
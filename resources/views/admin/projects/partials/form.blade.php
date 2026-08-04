@if ($errors->any())
    <div class="admin-error-summary"><strong>فرم ذخیره نشد.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="admin-form-grid">
    <section class="admin-panel admin-form-section">
        <div class="admin-panel-heading"><div><p>Content</p><h2>اطلاعات اصلی</h2></div></div>
        <label><span>عنوان پروژه *</span><input type="text" name="title" value="{{ old('title', $project->title) }}" required></label>
        <label><span>Slug</span><input type="text" name="slug" value="{{ old('slug', $project->slug) }}" dir="ltr" placeholder="auto-generated"></label>
        <label><span>Eyebrow</span><input type="text" name="eyebrow" value="{{ old('eyebrow', $project->eyebrow) }}" dir="ltr"></label>
        <label><span>خلاصه *</span><textarea name="summary" rows="5" required>{{ old('summary', $project->summary) }}</textarea></label>
        <label><span>متن کامل Case Study</span><textarea name="content" rows="12">{{ old('content', $project->content) }}</textarea></label>
    </section>

    <aside class="admin-form-sidebar">
        <section class="admin-panel admin-form-section">
            <div class="admin-panel-heading"><div><p>Publishing</p><h2>انتشار</h2></div></div>
            <label><span>ترتیب نمایش</span><input type="number" name="sort_order" value="{{ old('sort_order', $project->sort_order) }}" min="0" required></label>
            <label><span>زمان انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at', optional($project->published_at)->format('Y-m-d\TH:i')) }}"></label>
            <input type="hidden" name="is_published" value="0">
            <label class="admin-checkbox"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project->is_published))><span>منتشر شود</span></label>
            <input type="hidden" name="is_featured" value="0">
            <label class="admin-checkbox"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $project->is_featured))><span>پروژه ویژه</span></label>
        </section>

        <section class="admin-panel admin-form-section">
            <div class="admin-panel-heading"><div><p>Visual</p><h2>تصویر و رنگ</h2></div></div>
            @if ($project->cover_image)
                <img class="admin-cover-preview" src="{{ Storage::url($project->cover_image) }}" alt="">
                <input type="hidden" name="remove_cover_image" value="0">
                <label class="admin-checkbox"><input type="checkbox" name="remove_cover_image" value="1"><span>حذف تصویر فعلی</span></label>
            @endif
            <label><span>تصویر کاور</span><input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp"></label>
            <label><span>رنگ تأکیدی</span><input type="color" name="accent" value="{{ old('accent', $project->accent ?: '#d7ff3f') }}"></label>
        </section>
    </aside>
</div>

<section class="admin-panel admin-form-section">
    <div class="admin-panel-heading"><div><p>Details</p><h2>جزئیات فنی</h2></div></div>
    <div class="admin-input-grid">
        <label><span>نقش</span><input type="text" name="role" value="{{ old('role', $project->role) }}"></label>
        <label><span>سال</span><input type="number" name="year" value="{{ old('year', $project->year) }}" min="2000" max="{{ now()->year + 2 }}"></label>
        <label class="admin-input-span"><span>تکنولوژی‌ها؛ جداشده با ویرگول</span><input type="text" name="technologies" value="{{ old('technologies', implode(', ', $project->technologies ?? [])) }}" dir="ltr"></label>
        <label><span>GitHub URL</span><input type="url" name="github_url" value="{{ old('github_url', $project->github_url) }}" dir="ltr"></label>
        <label><span>Live URL</span><input type="url" name="live_url" value="{{ old('live_url', $project->live_url) }}" dir="ltr"></label>
    </div>
</section>

<div class="admin-sticky-actions">
    <a class="admin-secondary-button" href="{{ route('admin.projects.index') }}">انصراف</a>
    <button class="admin-primary-button" type="submit">ذخیره پروژه</button>
</div>

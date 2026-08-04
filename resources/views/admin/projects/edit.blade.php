@extends('layouts.admin')
@section('title','ویرایش '.$project->title)
@section('heading','ویرایش پروژه')
@section('eyebrow',$project->title)
@section('content')
<form class="admin-form admin-project-form" method="POST" action="{{ route('admin.projects.update',$project) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.projects.partials.form')</form>
<section class="admin-panel admin-gallery-manager">
<div class="admin-panel-heading"><div><p>Media manager</p><h2>گالری پروژه</h2></div><span>{{ $project->media->count() }} رسانه</span></div>
<form class="admin-media-upload" method="POST" action="{{ route('admin.projects.media.store',$project) }}" enctype="multipart/form-data">@csrf
<div class="admin-input-grid"><label class="admin-input-span"><span>تصاویر جدید؛ حداکثر ۱۲ فایل</span><input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple></label><label class="admin-input-span"><span>یا آدرس ویدئو Embed</span><input type="url" name="external_url" dir="ltr" placeholder="https://www.youtube.com/embed/..."></label><label><span>Alt پیش‌فرض</span><input type="text" name="alt_text"></label><label><span>اندازه نمایش</span><select name="display_size"><option value="standard">استاندارد</option><option value="wide">عریض</option><option value="portrait">عمودی</option></select></label><label class="admin-input-span"><span>توضیح</span><textarea name="caption" rows="2"></textarea></label></div><button class="admin-primary-button" type="submit">افزودن به گالری</button></form>
@if($project->media->isNotEmpty())<div class="admin-media-grid" data-sortable-media data-reorder-url="{{ route('admin.projects.media.reorder',$project) }}">
@foreach($project->media as $media)<article class="admin-media-card" data-media-id="{{ $media->id }}">
<div class="admin-media-preview">@if($media->type==='image')<img src="{{ $media->thumbnail_url }}" alt="">@else<div class="admin-video-placeholder">VIDEO</div>@endif<span class="admin-drag-handle">↕</span></div>
<form method="POST" action="{{ route('admin.projects.media.update',[$project,$media]) }}">@csrf @method('PUT')<input type="text" name="alt_text" value="{{ $media->alt_text }}" placeholder="Alt text"><textarea name="caption" rows="2" placeholder="Caption">{{ $media->caption }}</textarea><select name="display_size"><option value="standard" @selected($media->display_size==='standard')>استاندارد</option><option value="wide" @selected($media->display_size==='wide')>عریض</option><option value="portrait" @selected($media->display_size==='portrait')>عمودی</option></select><input type="hidden" name="is_featured" value="0"><label class="admin-checkbox"><input type="checkbox" name="is_featured" value="1" @checked($media->is_featured)><span>رسانه شاخص</span></label><button class="admin-secondary-button" type="submit">ذخیره</button></form>
<form method="POST" action="{{ route('admin.projects.media.destroy',[$project,$media]) }}" data-confirm="این رسانه حذف شود؟">@csrf @method('DELETE')<button class="admin-danger-link" type="submit">حذف</button></form>
</article>@endforeach</div>@else<p class="admin-empty">هنوز رسانه‌ای اضافه نشده است.</p>@endif
</section>
@endsection

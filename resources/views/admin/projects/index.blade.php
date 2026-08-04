@extends('layouts.admin')
@section('title','مدیریت پروژه‌ها')
@section('heading','پروژه‌ها')
@section('eyebrow','Portfolio content')
@section('content')
<div class="admin-page-actions"><p>Case Study، گالری، ترتیب نمایش و وضعیت انتشار را مدیریت کن.</p><a class="admin-primary-button" href="{{ route('admin.projects.create') }}">پروژه جدید</a></div>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table admin-projects-table"><thead><tr><th>پروژه</th><th>وضعیت</th><th>گالری</th><th>ترتیب</th><th>سال</th><th>عملیات</th></tr></thead><tbody>
@forelse($projects as $project)<tr><td><div class="admin-project-cell"><span class="admin-project-swatch" style="--swatch:{{ $project->accent }}"></span><div><strong>{{ $project->title }}</strong><small>{{ $project->eyebrow }}</small></div></div></td><td><span @class(['admin-status','is-live'=>$project->is_published])>{{ $project->is_published ? 'منتشرشده':'پیش‌نویس' }}</span></td><td>{{ $project->media_count }}</td><td>{{ $project->sort_order }}</td><td>{{ $project->year ?: '—' }}</td><td><div class="admin-actions-inline"><a href="{{ route('portfolio.projects.show',$project) }}" target="_blank">نمایش</a><a href="{{ route('admin.projects.edit',$project) }}">ویرایش</a><form method="POST" action="{{ route('admin.projects.destroy',$project) }}" data-confirm="این پروژه و تمام رسانه‌های آن حذف شود؟">@csrf @method('DELETE')<button type="submit">حذف</button></form></div></td></tr>
@empty<tr><td colspan="6" class="admin-empty">پروژه‌ای وجود ندارد.</td></tr>@endforelse
</tbody></table></div><div class="admin-pagination">{{ $projects->links() }}</div></section>
@endsection

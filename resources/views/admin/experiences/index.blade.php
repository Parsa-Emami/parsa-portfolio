@extends('layouts.admin')
@section('title','سوابق')
@section('heading','سوابق و Timeline')
@section('eyebrow','Experience')
@section('content')
<div class="admin-page-actions"><p>مسیر حرفه‌ای و سوابق قابل نمایش در سایت را مدیریت کن.</p><a class="admin-primary-button" href="{{ route('admin.experiences.create') }}">سابقه جدید</a></div>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>عنوان</th><th>مجموعه</th><th>بازه</th><th>وضعیت</th><th>ترتیب</th><th></th></tr></thead><tbody>
@forelse($experiences as $experience)<tr><td><strong>{{ $experience->title }}</strong></td><td>{{ $experience->organization ?: '—' }}</td><td>{{ $experience->period_label }}</td><td><span @class(['admin-status','is-live'=>$experience->is_published])>{{ $experience->is_published ? 'منتشر':'مخفی' }}</span></td><td>{{ $experience->sort_order }}</td><td><div class="admin-actions-inline"><a href="{{ route('admin.experiences.edit',$experience) }}">ویرایش</a><form method="POST" action="{{ route('admin.experiences.destroy',$experience) }}" data-confirm="این سابقه حذف شود؟">@csrf @method('DELETE')<button>حذف</button></form></div></td></tr>@empty<tr><td colspan="6" class="admin-empty">سابقه‌ای ثبت نشده است.</td></tr>@endforelse
</tbody></table></div></section>
@endsection

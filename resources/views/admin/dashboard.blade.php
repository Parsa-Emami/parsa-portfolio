@extends('layouts.admin')
@section('title','داشبورد مدیریت')
@section('heading','داشبورد')
@section('eyebrow','نمای کلی')
@section('content')
<section class="admin-stats admin-stats-wide">
    <article><span>کل پروژه‌ها</span><strong>{{ $projectCount }}</strong><small>{{ $publishedProjectCount }} منتشرشده</small></article>
    <article><span>رسانه‌ها</span><strong>{{ $mediaCount }}</strong><small>تصویر و ویدئو</small></article>
    <article><span>مهارت‌ها</span><strong>{{ $skillCount }}</strong><small>قابل مدیریت</small></article>
    <article><span>سوابق</span><strong>{{ $experienceCount }}</strong><small>Timeline</small></article>
    <article><span>پیام‌های جدید</span><strong>{{ $unreadMessageCount }}</strong><small>از {{ $messageCount }} پیام</small></article>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading"><div><p>Inbox</p><h2>آخرین پیام‌ها</h2></div><a class="admin-secondary-button" href="{{ route('admin.messages.index') }}">مشاهده همه</a></div>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>فرستنده</th><th>موضوع</th><th>وضعیت</th><th>زمان</th><th></th></tr></thead><tbody>
    @forelse($latestMessages as $message)<tr @class(['is-unread'=>!$message->read_at])><td><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small></td><td>{{ $message->subject ?: 'بدون موضوع' }}</td><td>{{ $message->status }}</td><td>{{ $message->created_at->diffForHumans() }}</td><td><a href="{{ route('admin.messages.show',$message) }}">مشاهده</a></td></tr>@empty<tr><td colspan="5" class="admin-empty">هنوز پیامی ثبت نشده است.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection

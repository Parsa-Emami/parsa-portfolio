@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')
@section('heading', 'داشبورد')
@section('eyebrow', 'نمای کلی')

@section('content')
    <section class="admin-stats">
        <article><span>کل پروژه‌ها</span><strong>{{ $projectCount }}</strong></article>
        <article><span>پروژه منتشرشده</span><strong>{{ $publishedProjectCount }}</strong></article>
        <article><span>کل پیام‌ها</span><strong>{{ $messageCount }}</strong></article>
        <article><span>پیام خوانده‌نشده</span><strong>{{ $unreadMessageCount }}</strong></article>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div><p>Inbox</p><h2>آخرین پیام‌ها</h2></div>
            <a class="admin-secondary-button" href="{{ route('admin.messages.index') }}">مشاهده همه</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>فرستنده</th><th>موضوع</th><th>زمان</th><th></th></tr></thead>
                <tbody>
                    @forelse ($latestMessages as $message)
                        <tr @class(['is-unread' => !$message->read_at])>
                            <td><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small></td>
                            <td>{{ $message->subject ?: 'بدون موضوع' }}</td>
                            <td>{{ $message->created_at->diffForHumans() }}</td>
                            <td><a href="{{ route('admin.messages.show', $message) }}">مشاهده</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-empty">هنوز پیامی ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

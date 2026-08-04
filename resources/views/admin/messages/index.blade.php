@extends('layouts.admin')
@section('title', 'پیام‌های تماس')
@section('heading', 'پیام‌ها')
@section('eyebrow', 'Contact inbox')
@section('content')
    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>فرستنده</th><th>موضوع</th><th>وضعیت</th><th>زمان</th><th></th></tr></thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr @class(['is-unread' => !$message->read_at])>
                            <td><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small></td>
                            <td>{{ $message->subject ?: 'بدون موضوع' }}</td>
                            <td><span @class(['admin-status', 'is-live' => $message->read_at])>{{ $message->read_at ? 'خوانده‌شده' : 'جدید' }}</span></td>
                            <td>{{ $message->created_at->format('Y/m/d H:i') }}</td>
                            <td><a href="{{ route('admin.messages.show', $message) }}">مشاهده</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty">پیامی وجود ندارد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $messages->links() }}</div>
    </section>
@endsection

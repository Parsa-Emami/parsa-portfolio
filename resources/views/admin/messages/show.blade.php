@extends('layouts.admin')
@section('title', 'پیام ' . $message->name)
@section('heading', 'جزئیات پیام')
@section('eyebrow', $message->created_at->format('Y/m/d H:i'))
@section('content')
    <section class="admin-panel admin-message-card">
        <div class="admin-message-header">
            <div><h2>{{ $message->subject ?: 'بدون موضوع' }}</h2><p>از {{ $message->name }} — <a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p></div>
            <a class="admin-primary-button" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . ($message->subject ?: 'Portfolio inquiry')) }}">پاسخ با ایمیل</a>
        </div>
        <div class="admin-message-body">{!! nl2br(e($message->message)) !!}</div>
        <div class="admin-message-meta"><span>IP: {{ $message->ip_address ?: '—' }}</span><span>{{ $message->created_at->diffForHumans() }}</span></div>
    </section>

    <div class="admin-sticky-actions">
        <a class="admin-secondary-button" href="{{ route('admin.messages.index') }}">بازگشت</a>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('این پیام حذف شود؟')">
            @csrf @method('DELETE')
            <button class="admin-danger-button" type="submit">حذف پیام</button>
        </form>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'گزارش فعالیت')
@section('eyebrow', 'Security & Operations')
@section('heading', 'گزارش فعالیت مدیران')

@section('content')
<section class="admin-panel">
    <form method="GET" class="admin-filter-bar">
        <label>
            <span>جستجو در مسیر، عملیات یا Request ID</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="admin.projects.update">
        </label>
        <button class="admin-button" type="submit">جستجو</button>
        @if($search !== '')
            <a class="admin-button is-secondary" href="{{ route('admin.activity.index') }}">پاک‌کردن</a>
        @endif
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>زمان</th>
                <th>مدیر</th>
                <th>عملیات</th>
                <th>مسیر</th>
                <th>وضعیت</th>
                <th>Request ID</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <strong>{{ $log->user?->name ?? 'کاربر حذف‌شده' }}</strong>
                        @if($log->user?->email)<small>{{ $log->user->email }}</small>@endif
                    </td>
                    <td>
                        <code>{{ $log->action }}</code>
                        @if($log->payload_keys)
                            <small>Fields: {{ implode(', ', $log->payload_keys) }}</small>
                        @endif
                    </td>
                    <td>
                        <span>{{ $log->method }}</span>
                        <small>{{ $log->path }}</small>
                    </td>
                    <td><span class="admin-status {{ $log->status_code >= 400 ? 'is-draft' : 'is-published' }}">{{ $log->status_code }}</span></td>
                    <td><code title="{{ $log->request_id }}">{{ Str::limit($log->request_id, 18) }}</code></td>
                </tr>
            @empty
                <tr><td colspan="6">هنوز فعالیتی ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection

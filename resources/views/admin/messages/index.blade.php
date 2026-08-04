@extends('layouts.admin')
@section('title','پیام‌ها') @section('heading','صندوق پیام‌ها') @section('eyebrow','Contact inbox')
@section('content')
<div class="admin-filter-tabs"><a href="{{ route('admin.messages.index') }}" @class(['is-active'=>$status===''])>فعال</a><a href="{{ route('admin.messages.index',['status'=>'unread']) }}" @class(['is-active'=>$status==='unread'])>خوانده‌نشده</a><a href="{{ route('admin.messages.index',['status'=>'replied']) }}" @class(['is-active'=>$status==='replied'])>پاسخ‌داده‌شده</a><a href="{{ route('admin.messages.index',['status'=>'archived']) }}" @class(['is-active'=>$status==='archived'])>آرشیو</a></div>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>فرستنده</th><th>موضوع</th><th>وضعیت</th><th>زمان</th><th></th></tr></thead><tbody>
@forelse($messages as $message)<tr @class(['is-unread'=>!$message->read_at])><td><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small></td><td>{{ $message->subject ?: 'بدون موضوع' }}</td><td>{{ $message->status }}</td><td>{{ $message->created_at->diffForHumans() }}</td><td><a href="{{ route('admin.messages.show',$message) }}">مشاهده</a></td></tr>@empty<tr><td colspan="5" class="admin-empty">پیامی در این بخش وجود ندارد.</td></tr>@endforelse
</tbody></table></div><div class="admin-pagination">{{ $messages->links() }}</div></section>
@endsection

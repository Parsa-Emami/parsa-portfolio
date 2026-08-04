<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $query = ContactMessage::query()->latest();

        if ($status === 'unread') $query->unread();
        if ($status === 'replied') $query->whereNotNull('replied_at');
        if ($status === 'archived') $query->whereNotNull('archived_at');
        if ($status === '') $query->active();

        return view('admin.messages.index', [
            'messages' => $query->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function show(ContactMessage $message): View
    {
        $message->markAsRead();
        return view('admin.messages.show', compact('message'));
    }

    public function reply(Request $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validate(['reply_message' => ['required', 'string', 'min:10', 'max:10000']]);
        Mail::to($message->email)->queue(new ContactReplyMail($message, $validated['reply_message']));
        $message->update([
            'reply_message' => $validated['reply_message'],
            'replied_at' => now(),
            'status' => 'replied',
            'read_at' => $message->read_at ?? now(),
        ]);

        return back()->with('success', 'پاسخ ارسال و ثبت شد.');
    }

    public function archive(ContactMessage $message): RedirectResponse
    {
        $message->update(['archived_at' => $message->archived_at ? null : now()]);
        return back()->with('success', $message->archived_at ? 'پیام آرشیو شد.' : 'پیام از آرشیو خارج شد.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();
        return redirect()->route('admin.messages.index')->with('success', 'پیام حذف شد.');
    }
}

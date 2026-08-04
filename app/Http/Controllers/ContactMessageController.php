<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');

        ContactMessage::query()->create([
            ...$data,
            'status' => 'unread',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with('contact_success', 'Thanks — your message has been received.');
    }
}

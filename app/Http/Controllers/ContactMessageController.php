<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse|JsonResponse
    {
        $startedAt = (int) $request->input('started_at', 0);
        if ($startedAt > 0 && (now()->timestamp - $startedAt) < 3) {
            return $this->response($request, false, 'Please take a moment to review your message.', 422);
        }

        $data = $request->safe()->except(['website', 'started_at']);
        $message = ContactMessage::query()->create([
            ...$data,
            'status' => 'unread',
            'ip_address' => null,
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);

        $recipient = SiteSetting::values()['email'] ?? config('portfolio.admin.email');
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($recipient)->queue(new NewContactMessageMail($message));
            } catch (\Throwable $exception) {
                Log::warning('Portfolio contact notification could not be sent.', ['exception' => $exception->getMessage()]);
            }
        }

        return $this->response($request, true, 'Thanks — your message has been received.');
    }

    private function response(ContactRequest $request, bool $success, string $message, int $status = 200): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $status);
        }

        return back()->with($success ? 'contact_success' : 'contact_error', $message);
    }
}

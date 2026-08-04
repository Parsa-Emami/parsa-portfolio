<?php

namespace Tests\Feature;

use App\Mail\ContactReplyMail;
use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_contact_submission_hashes_ip_and_sends_notification(): void
    {
        Mail::fake();
        SiteSetting::query()->create([
            'key' => 'email',
            'label' => 'Email',
            'value' => 'owner@example.com',
            'group' => 'Contact',
            'type' => 'email',
            'sort_order' => 1,
        ]);
        SiteSetting::forgetCache();

        $this->postJson(route('portfolio.contact.store'), [
            'name' => 'Serious Client',
            'email' => 'client@example.com',
            'subject' => 'Laravel platform',
            'message' => 'I would like to discuss a substantial Laravel platform and its delivery timeline.',
            'website' => '',
            'started_at' => now()->subSeconds(10)->timestamp,
        ])->assertOk()->assertJson(['success' => true]);

        $message = ContactMessage::query()->firstOrFail();
        $this->assertNull($message->ip_address);
        $this->assertNotNull($message->ip_hash);
        $this->assertSame(64, strlen($message->ip_hash));
        Mail::assertSent(NewContactMessageMail::class);
    }

    public function test_admin_can_reply_to_contact_message(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $message = ContactMessage::query()->create([
            'name' => 'Client',
            'email' => 'client@example.com',
            'subject' => 'Project enquiry',
            'message' => 'This is a detailed project enquiry that requires a thoughtful response.',
            'status' => 'unread',
        ]);

        $this->actingAs($admin)->post(route('admin.messages.reply', $message), [
            'reply_message' => 'Thank you. I would be happy to discuss the project scope and next steps.',
        ])->assertSessionHas('success');

        $message->refresh();
        $this->assertSame('replied', $message->status);
        $this->assertNotNull($message->replied_at);
        Mail::assertSent(ContactReplyMail::class, fn (ContactReplyMail $mail) => $mail->hasTo('client@example.com'));
    }
}

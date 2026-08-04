<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_contact_form(): void
    {
        $response = $this->post(route('portfolio.contact.store'), [
            'name' => 'Portfolio Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'New Laravel project',
            'message' => 'I would like to discuss a new Laravel application with you.',
            'website' => '',
        ]);

        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'visitor@example.com',
            'status' => 'unread',
        ]);
    }

    public function test_contact_message_must_be_long_enough(): void
    {
        $this->post(route('portfolio.contact.store'), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'Too short',
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, ContactMessage::query()->count());
    }
}

<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage, public string $replyText) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Re: '.($this->contactMessage->subject ?: 'Your portfolio enquiry'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-reply');
    }
}

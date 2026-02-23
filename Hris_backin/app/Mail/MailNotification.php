<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $emailRequest;
    public $subjectLine;
    public $data=[];
    public $viewName;
    /**
     * Create a new message instance.
     */
    public function __construct($emailRequest, $subjectLine, $viewName = 'emails.mrf_notify')
    {
        $this->emailRequest = (object) $emailRequest; // Convert to an object for Blade template compatibility
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
         return new Content(
            view: $this->viewName,
            with: ['emailRequest' => $this->emailRequest],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

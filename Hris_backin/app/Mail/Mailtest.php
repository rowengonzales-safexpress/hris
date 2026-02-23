<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Mailtest extends Mailable
{
    use Queueable, SerializesModels;
    public $jobRequest;
    public $subjectLine;
    public $data=[];
    /**
     * Create a new message instance.
     */
    public function __construct($jobRequest, $subjectLine)
    {

        $this->jobRequest = (object) $jobRequest; // Convert to an object for Blade template compatibility
        $this->subjectLine = $subjectLine;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: ['jobRequest' => $this->jobRequest, 'subjectLine' => $this->subjectLine],
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

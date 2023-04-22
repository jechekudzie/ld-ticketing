<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct(Ticket $ticket, $attachments = [])
    {
        $this->ticket = $ticket;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $subject = 'Ticket #' . $this->ticket->ticket_number . ' - ' . $this->ticket->subject;
        $fromEmail = 'support@leadingdigital.africa';

        $message = $this->view('emails.confirmation')
            ->subject($subject)
            ->from($fromEmail);

        foreach ($this->attachments as $attachment) {
            $message->attach($attachment->file_path, [
                'as' => $attachment->file_name,
                'mime' => $attachment->mime_type,
            ]);
        }

        return $message;
    }

}

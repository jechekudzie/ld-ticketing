<?php

namespace App\Mail;

use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResponseEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $comment;

    /**
     * Create a new message instance.
     *
     * @param TicketComment $comment
     */
    public function __construct(TicketComment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        $subject = 'Ticket #' . $this->comment->ticket->ticket_number . ' - ' . $this->comment->ticket->subject;
        $mail = $this->from('support@leadingdigital.africa') // Replace with your email address
        ->subject($subject)
            ->view('emails.response');

        // Attach files to the email
        foreach ($this->comment->attachments as $attachment) {
            $mail->attach($attachment->file_path, ['as' => $attachment->file_name]);
        }

        return $mail;
    }

}

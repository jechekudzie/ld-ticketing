<?php

namespace App\Http\Controllers;


use App\Mail\ResponseEmail;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationEmail;
use App\Models\TicketComment;
use Illuminate\Support\Facades\File;

use Webklex\PHPIMAP\Message;

class MailController extends Controller
{
    //

    public function index()
    {
        $tickets = Ticket::all();
        return view('tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        $comments = $ticket->ticket_comments()->with('attachments')->get();

        return view('tickets.show', compact('ticket', 'comments'));
    }


    public function handleEmail(Message $message)
    {
        // Extract email subject, body, and sender email address
        $subject = $message->getSubject();
        $body = $message->getTextBody(); // or $message->getHTMLBody() for HTML content
        $fromEmail = $message->getFrom()[0]->mail;
        $attachments = $message->getAttachments();
        //the body of the email becomes the ticket descriptions
        $description = $body;

        // Check if the email subject contains a valid ticket number
        if (preg_match('/LD\d+/', $subject, $matches)) {
            $ticketNumber = $matches[0];

            // Find the ticket with the given ticket number
            $ticket = Ticket::where('ticket_number', $ticketNumber)->first();

            if ($ticket) {
                // Create a new comment associated with the ticket
                $comment = TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'email' => $fromEmail,
                    'message' => $description,
                ]);

                $attachableType = 'App\Models\TicketComment';
                $attachableId = $comment->id;
                $this->saveAttachments($message, $attachableId, $attachableType);


                // You can send a reply confirmation email or any other action you want to perform here
            } else {
                // Create a new ticket if the ticket number doesn't exist
                $ticket = $this->createNewTicket(null, $fromEmail, $subject, $description, $attachments);

                $attachableType = 'App\Models\Ticket';
                $attachableId = $ticket->id;
                $this->saveAttachments($message, $attachableId, $attachableType);

            }
        } else {

            // Create a new ticket
            $ticket = $this->createNewTicket(null, $fromEmail, $subject, $description, $attachments);
            $attachableType = 'App\Models\Ticket';
            $attachableId = $ticket->id;

            // Save the email thread ID for future reference
            $comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'email' => $fromEmail,
                'message' => $description,

            ]);

            $this->saveAttachments($message, $attachableId, $attachableType);

        }
        //Mark email as read/seen
        $message->setFlag(['Seen']);

    }

    public function sendResponse(Request $request, $ticketId)
    {
        // Get the ticket
        $ticket = Ticket::findOrFail($ticketId);

        // Get the response text from the request data
        $responseText = $request->input('responseText');

        // Save your response as a comment
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'email' => 'support@leadingdigital.africa', // Replace with your email address
            'message' => $responseText,
        ]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = $request->file('attachments');

            foreach ($attachments as $attachment) {
                // Save the attachment file
                $fileName = $attachment->getClientOriginalName();

                $file_name = time() . $fileName;
                $filePath = $attachment->move('attachments', $file_name);

                // Create a new TicketAttachment record and associate it with the comment
                TicketAttachment::create([
                    'attachable_id' => $comment->id,
                    'attachable_type' => 'App\Models\TicketComment',
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                ]);
            }
        }


        // Send the response to the customer via email
        Mail::to($ticket->email)->send(new ResponseEmail($comment));

        return 'Response sent successfully.';
    }


    private function saveAttachments($message, $attachableId, $attachableType)
    {
        if ($message->hasAttachments()) {

            $attachments = $message->getAttachments();
            $attachmentFolder = 'public/attachments';

            // Create the attachments folder if it doesn't exist
            if (!File::exists($attachmentFolder)) {
                File::makeDirectory($attachmentFolder, 0755, true);
            }

            foreach ($attachments as $attachment) {
                // Save the attachment file
                $fileName = $attachment->getName();
                $filePath = $attachmentFolder . '/' . $fileName;
                file_put_contents($filePath, $attachment->getContent());

                // Create a new TicketAttachment record and associate it with the ticket or comment
                TicketAttachment::create([
                    'attachable_id' => $attachableId,
                    'attachable_type' => $attachableType,
                    'file_name' => time() . $fileName,
                    'file_path' => 'attachments/' . $fileName,
                ]);
            }
        }
    }

    private function createNewTicket($ticketNumber, $fromEmail, $subject, $description, $attachments)
    {
        // Generate the ticket number if it is null
        if (is_null($ticketNumber)) {
            $prefix = 'LD';
            $zeroFillLength = 6;
            $nextTicketId = Ticket::max('id') + 1;
            $ticketNumber = $prefix . str_pad($nextTicketId, $zeroFillLength, '0', STR_PAD_LEFT);
        }

        // Create a new ticket with the ticket number
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'email' => $fromEmail,
            'subject' => $subject,
            'description' => $description,
        ]);

        // Send a confirmation email to the sender
        Mail::to($fromEmail)->send(new ConfirmationEmail($ticket));

        // Process attachments only if the email has attachments


        return $ticket;
    }
}

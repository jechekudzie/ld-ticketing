<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Http\Controllers\MailController;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Fetch incoming emails';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $client = Client::account('default');
        $client->connect();

        $mailbox = $client->getFolder('INBOX');

        $messages = $mailbox->query()
            ->unseen() // Only fetch unread emails
            ->get();

        $mailController = new MailController;

        foreach ($messages as $message) {
            $mailController->handleEmail($message);

            // Mark email as read
            $message->markAsSeen();
        }
    }
}

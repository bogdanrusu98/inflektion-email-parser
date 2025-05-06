<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuccessfulEmail;

class ParseEmails extends Command
{
    protected $signature = 'emails:parse';
    protected $description = 'Parse raw emails and extract plain text';

    public function handle()
    {
        $emails = SuccessfulEmail::whereNull('raw_text')->get();

        if ($emails->isEmpty()) {
            $this->info('No emails to parse.');
            return;
        }

        foreach ($emails as $email) {
            // Clear text
            $plainText = strip_tags($email->email); // Clear HTML
            $plainText = preg_replace('/[^[:print:]\n]/', '', $plainText); // clear characters

            // Save in db
            $email->raw_text = $plainText;
            $email->save();

            $this->info("Parsed email ID: {$email->id}");
        }

        $this->info('All emails parsed successfully.');
    }
}

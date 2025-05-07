<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuccessfulEmail;

class ParseEmails extends Command
{
    protected $signature = 'emails:parse';
    protected $description = 'Parse raw emails and extract plain text body content';

    public function handle()
    {
        // Fetch emails where 'raw_text' is missing or empty
        $emails = SuccessfulEmail::where(function ($query) {
            $query->whereNull('raw_text')
                  ->orWhere('raw_text', '');
        })->get();

        if ($emails->isEmpty()) {
            $this->info('No emails to parse.');
            return;
        }

        foreach ($emails as $email) {
            // Try to extract plain text body from raw email content
            $plainText = $this->extractPlainTextBody($email->email);

            if ($plainText) {
                $email->raw_text = $plainText;
                $email->save();

                $this->info("Parsed email ID: {$email->id}");
            } else {
                $this->warn("No plain text found for email ID: {$email->id}");
            }
        }

        $this->info('All emails parsed successfully.');
    }

    /**
     * Extracts plain text body content from a raw MIME email
     *
     * @param string $rawEmail
     * @return string|null
     */
    private function extractPlainTextBody(string $rawEmail): ?string
    {
        // Split by MIME boundary markers (---...)
        $parts = preg_split('/--[_=a-zA-Z0-9\-]+/', $rawEmail);

        foreach ($parts as $part) {
            // Look for plain text parts with quoted-printable encoding
            if (str_contains($part, 'Content-Type: text/plain') &&
                str_contains($part, 'Content-Transfer-Encoding: quoted-printable')) {

                // Extract the body (after headers)
                $body = preg_split("/\r?\n\r?\n/", $part, 2);

                if (isset($body[1])) {
                    // Decode quoted-printable and strip HTML tags (if any)
                    $decoded = quoted_printable_decode($body[1]);
                    return strip_tags(trim($decoded));
                }
            }
        }

        return null;
    }
}

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
        $emails = SuccessfulEmail::where(function ($query) {
            $query->whereNull('raw_text')
                  ->orWhere('raw_text', '');
        })->get();

        if ($emails->isEmpty()) {
            $this->info('No emails to parse.');
            return;
        }

        foreach ($emails as $email) {
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
     * Extract plain text body from MIME email
     */
    private function extractPlainTextBody(string $rawEmail): ?string
    {
        $parts = preg_split('/--[_=a-zA-Z0-9\-]+/', $rawEmail);

        foreach ($parts as $part) {
            if (str_contains($part, 'Content-Type: text/plain')) {
                $body = preg_split("/\r?\n\r?\n/", $part, 2);
                if (isset($body[1])) {
                    $text = $body[1];
                    if (str_contains($part, 'quoted-printable')) {
                        $text = quoted_printable_decode($text);
                    }
                    return $this->cleanText($text);
                }
            }
        }

        foreach ($parts as $part) {
            if (str_contains($part, 'Content-Type: text/html')) {
                $body = preg_split("/\r?\n\r?\n/", $part, 2);
                if (isset($body[1])) {
                    $html = $body[1];
                    if (str_contains($part, 'quoted-printable')) {
                        $html = quoted_printable_decode($html);
                    }
                    return $this->cleanText(strip_tags($html));
                }
            }
        }

        return null;
    }

    /**
     * Clean up string by removing redundant spaces, decoding HTML entities etc.
     */
    private function cleanText(string $input): string
    {
        return trim(
            html_entity_decode(
                preg_replace('/\s+/', ' ', $input)
            )
        );
    }
}

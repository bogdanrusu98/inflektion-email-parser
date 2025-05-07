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
        // Separate MIME parts by boundary
        $parts = preg_split('/--[_=a-zA-Z0-9\-]+/', $rawEmail);
    
        foreach ($parts as $part) {
            // Try text/plain first
            if (str_contains($part, 'Content-Type: text/plain')) {
                $body = preg_split("/\r?\n\r?\n/", $part, 2);
                if (isset($body[1])) {
                    $text = $body[1];
                    if (str_contains($part, 'quoted-printable')) {
                        $text = quoted_printable_decode($text);
                    }
                    return cleanText($text);
                }
            }
        }
    
        // Fallback: try text/html
        foreach ($parts as $part) {
            if (str_contains($part, 'Content-Type: text/html')) {
                $body = preg_split("/\r?\n\r?\n/", $part, 2);
                if (isset($body[1])) {
                    $html = $body[1];
                    if (str_contains($part, 'quoted-printable')) {
                        $html = quoted_printable_decode($html);
                    }
                    return cleanText(strip_tags($html));
                }
            }
        }
    
        return null;
    }
    
    // Helper method to cleanup weird characters and HTML entities
    function cleanText(string $input): string
    {
        return trim(
            html_entity_decode(
                preg_replace('/\s+/', ' ', $input)
            )
        );
    }
    

    
}

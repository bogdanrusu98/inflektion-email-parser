<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuccessfulEmail;

class EmailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'affiliate_id' => 'required|integer',
            'envelope' => 'required|string',
            'from' => 'required|email',
            'subject' => 'required|string',
            'dkim' => 'nullable|string',
            'SPF' => 'nullable|string',
            'spam_score' => 'nullable|numeric',
            'email' => 'required|string',
            'sender_ip' => 'nullable|string',
            'to' => 'required|string',
            'timestamp' => 'required|integer',
        ]);

        // Parsing

        $plainText = $data['email'];

        // Replace <li> with "- text\n"
        $plainText = preg_replace('/<li>(.*?)<\/li>/i', "- $1\n", $plainText);

        // Add \n after </p>, </h1>–</h6>
        $plainText = preg_replace('/<\/(p|h[1-6])>/i', "\n", $plainText);

        // Clear HTML tags
        $plainText = strip_tags($plainText);

        // Spaces and \n
        $plainText = preg_replace('/\r\n|\r/', "\n", $plainText); 
        $plainText = preg_replace('/[ \t]+/', ' ', $plainText); 
        $plainText = preg_replace('/\n{2,}/', "\n\n", $plainText); 

        $data['raw_text'] = trim($plainText);


        $email = SuccessfulEmail::create($data);

        return response()->json($email, 201);
    }

    public function destroy($id)
    {
        $email = \App\Models\SuccessfulEmail::find($id);
    
        if (! $email) {
            return response()->json(['message' => 'Email not found'], 404);
        }
    
        $email->delete();
    
        return response()->json(['message' => 'Email soft-deleted successfully']);
    }

    public function show($id)
    {
        $email = SuccessfulEmail::find($id);

        if (! $email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        return response()->json($email);
    }

    public function index()
{
    $emails = \App\Models\SuccessfulEmail::whereNull('deleted_at')->get();

    return response()->json($emails);
}

public function update(Request $request, $id)
{
    $email = \App\Models\SuccessfulEmail::find($id);

    if (! $email) {
        return response()->json(['message' => 'Email not found'], 404);
    }

    // Validate
    $data = $request->validate([
        'affiliate_id' => 'sometimes|integer',
        'envelope' => 'sometimes|string',
        'from' => 'sometimes|email',
        'subject' => 'sometimes|string',
        'dkim' => 'nullable|string',
        'SPF' => 'nullable|string',
        'spam_score' => 'nullable|numeric',
        'email' => 'sometimes|string',
        'sender_ip' => 'nullable|string',
        'to' => 'sometimes|string',
        'timestamp' => 'sometimes|integer',
    ]);

    // Check if email was modified - change raw text
    if (isset($data['email'])) {
        $plainText = $data['email'];
        $plainText = preg_replace('/<li>(.*?)<\/li>/i', "- $1\n", $plainText);
        $plainText = preg_replace('/<\/(p|h[1-6])>/i', "\n", $plainText);
        $plainText = strip_tags($plainText);
        $plainText = preg_replace('/\r\n|\r/', "\n", $plainText);
        $plainText = preg_replace('/[ \t]+/', ' ', $plainText);
        $plainText = preg_replace('/\n{2,}/', "\n\n", $plainText);
        $data['raw_text'] = trim($plainText);
    }

    $email->update($data);

    return response()->json($email);
}





}

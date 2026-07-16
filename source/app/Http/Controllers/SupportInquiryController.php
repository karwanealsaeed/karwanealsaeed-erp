<?php

namespace App\Http\Controllers;

use App\Models\SupportInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SupportInquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('support_inquiries')) {
            return back()
                ->withInput()
                ->with('support_error', 'Support table database me nahi bani. Please php artisan migrate run karen.');
        }

        $data = $request->validateWithBag('supportInquiry', [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name.required' => 'Name zaroori hai.',
            'mobile.required' => 'Mobile / WhatsApp zaroori hai.',
            'email.email' => 'Email valid nahi hai.',
            'message.required' => 'Message zaroori hai.',
        ]);

        try {
            $inquiry = SupportInquiry::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'service_type' => $data['service_type'] ?? null,
                'subject' => $data['subject'] ?? null,
                'message' => $data['message'],
                'status' => 'New',
            ]);

            return back()
                ->with('support_success', 'Your inquiry has been submitted successfully. Inquiry No: ' . $inquiry->inquiry_no)
                ->with('support_reply', $inquiry->smart_reply);
        } catch (\Throwable $exception) {
            Log::error('Support inquiry submit failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('support_error', 'Support submit nahi hui. Error logs me detail save ho gayi hai.');
        }
    }
}
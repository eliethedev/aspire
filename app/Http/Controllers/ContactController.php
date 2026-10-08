<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Models\ContactInquiry;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Public inquiry form for prospective users (teachers, school heads,
     * supervisors). No login required.
     */
    public function create()
    {
        return view('contact.create');
    }

    public function store(Request $request)
    {
        // Honeypot: bots fill this hidden field, humans never see it.
        if ($request->filled('website')) {
            return redirect()
                ->route('contact.thanks')
                ->with('success', 'Thank you! Your inquiry has been received.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'role' => ['required', 'in:'.implode(',', ContactInquiry::ROLES)],
            'school_name' => ['nullable', 'string', 'max:190'],
            'topic' => ['required', 'in:'.implode(',', ContactInquiry::TOPICS)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $inquiry = ContactInquiry::create($validated);

        $this->notificationService->notifyByRole(
            'admin',
            NotificationType::CONTACT_INQUIRY,
            'New Contact Inquiry',
            "{$inquiry->name} ({$inquiry->roleLabel()}): {$inquiry->subject}",
            null,
            route('admin.contact-inquiries.show', $inquiry)
        );

        return redirect()
            ->route('contact.thanks')
            ->with('success', 'Thank you! Your inquiry has been received. Our team will get back to you soon.');
    }

    public function thanks()
    {
        if (! session()->has('success')) {
            return redirect()->route('contact.create');
        }

        return view('contact.thanks');
    }
}

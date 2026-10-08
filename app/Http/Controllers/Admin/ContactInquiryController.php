<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function index(Request $request)
    {
        $inquiries = ContactInquiry::query()
            ->with(['resolver'])
            ->when($request->status, fn ($q, $status) => $q->byStatus($status))
            ->when($request->role, fn ($q, $role) => $q->byRole($role))
            ->when($request->topic, fn ($q, $topic) => $q->byTopic($topic))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('school_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => ContactInquiry::query()->count(),
            'new' => ContactInquiry::query()->byStatus(ContactInquiry::STATUS_NEW)->count(),
            'in_progress' => ContactInquiry::query()->byStatus(ContactInquiry::STATUS_IN_PROGRESS)->count(),
            'resolved' => ContactInquiry::query()->byStatus(ContactInquiry::STATUS_RESOLVED)->count(),
        ];

        return view('admin.contact-inquiries.index', compact('inquiries', 'counts'));
    }

    public function show(ContactInquiry $contactInquiry)
    {
        $contactInquiry->load(['resolver']);

        return view('admin.contact-inquiries.show', compact('contactInquiry'));
    }

    public function update(Request $request, ContactInquiry $contactInquiry)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = $contactInquiry->only(['status', 'admin_note']);
        $wasResolved = $contactInquiry->isResolved();

        $contactInquiry->status = $validated['status'];
        $contactInquiry->admin_note = $validated['admin_note'] ?? $contactInquiry->admin_note;

        if ($validated['status'] === ContactInquiry::STATUS_RESOLVED && ! $wasResolved) {
            $contactInquiry->resolved_by = $request->user()->id;
            $contactInquiry->resolved_at = now();
        } elseif ($validated['status'] !== ContactInquiry::STATUS_RESOLVED) {
            $contactInquiry->resolved_by = null;
            $contactInquiry->resolved_at = null;
        }

        $contactInquiry->save();

        app(AuditLogService::class)->logUpdate(
            'contact_inquiries',
            $contactInquiry,
            $old,
            $contactInquiry->only(['status', 'admin_note']),
            "Updated contact inquiry status to {$contactInquiry->status}"
        );

        return redirect()
            ->route('admin.contact-inquiries.show', $contactInquiry)
            ->with('success', 'Inquiry updated.');
    }

    public function destroy(ContactInquiry $contactInquiry)
    {
        app(AuditLogService::class)->logDelete(
            'contact_inquiries',
            $contactInquiry,
            "Deleted contact inquiry: {$contactInquiry->subject}"
        );

        $contactInquiry->delete();

        return redirect()
            ->route('admin.contact-inquiries.index')
            ->with('success', 'Inquiry deleted.');
    }
}

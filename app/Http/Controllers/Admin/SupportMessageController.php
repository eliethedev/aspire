<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = SupportMessage::query()
            ->with(['user'])
            ->when($request->status, fn ($q, $status) => $q->byStatus($status))
            ->when($request->type, fn ($q, $type) => $q->byType($type))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => SupportMessage::query()->count(),
            'open' => SupportMessage::query()->open()->count(),
            'in_progress' => SupportMessage::query()->byStatus(SupportMessage::STATUS_IN_PROGRESS)->count(),
            'resolved' => SupportMessage::query()->byStatus(SupportMessage::STATUS_RESOLVED)->count(),
        ];

        return view('admin.support-messages.index', compact('messages', 'counts'));
    }

    public function show(SupportMessage $supportMessage)
    {
        $supportMessage->load(['user', 'resolver']);

        return view('admin.support-messages.show', compact('supportMessage'));
    }

    public function update(Request $request, SupportMessage $supportMessage)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = $supportMessage->only(['status', 'admin_note']);
        $wasResolved = $supportMessage->isResolved();

        $supportMessage->status = $validated['status'];
        $supportMessage->admin_note = $validated['admin_note'] ?? $supportMessage->admin_note;

        if ($validated['status'] === SupportMessage::STATUS_RESOLVED && ! $wasResolved) {
            $supportMessage->resolved_by = $request->user()->id;
            $supportMessage->resolved_at = now();
        } elseif ($validated['status'] !== SupportMessage::STATUS_RESOLVED) {
            $supportMessage->resolved_by = null;
            $supportMessage->resolved_at = null;
        }

        $supportMessage->save();

        app(AuditLogService::class)->logUpdate(
            'support_messages',
            $supportMessage,
            $old,
            $supportMessage->only(['status', 'admin_note']),
            "Updated support message status to {$supportMessage->status}"
        );

        return redirect()
            ->route('admin.support-messages.show', $supportMessage)
            ->with('success', 'Support message updated.');
    }

    public function destroy(Request $request, SupportMessage $supportMessage)
    {
        app(AuditLogService::class)->logDelete(
            'support_messages',
            $supportMessage,
            "Deleted support message: {$supportMessage->subject}"
        );

        $supportMessage->delete();

        return redirect()
            ->route('admin.support-messages.index')
            ->with('success', 'Support message deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    protected NotificationService $notificationService;
    protected PHPMailerService $mailer;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailer)
    {
        $this->notificationService = $notificationService;
        $this->mailer = $mailer;
    }

    public function index(Request $request)
    {
        $announcements = Announcement::with('sender')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:in_app,email,both',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,supervisor,teacher,school_head',
            'link' => 'nullable|string|max:500',
            'send_now' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'target_roles' => $validated['target_roles'] ?? null,
            'link' => $validated['link'] ?? null,
            'status' => 'draft',
            'sent_by' => $request->user()->id,
        ]);

        if (!empty($validated['send_now'])) {
            $this->sendAnnouncement($announcement);
        }

        app(AuditLogService::class)->logCreate(
            'announcements', $announcement,
            "Created announcement: {$announcement->title}"
        );

        $message = !empty($validated['send_now'])
            ? 'Announcement has been created and sent successfully.'
            : 'Announcement has been saved as draft.';

        return redirect()->route('admin.announcements.index')->with('success', $message);
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('sender');
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        if ($announcement->isSent()) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'Cannot edit a sent announcement.');
        }

        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->isSent()) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'Cannot edit a sent announcement.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:in_app,email,both',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,supervisor,teacher,school_head',
            'link' => 'nullable|string|max:500',
            'send_now' => 'nullable|boolean',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'target_roles' => $validated['target_roles'] ?? null,
            'link' => $validated['link'] ?? null,
        ]);

        if (!empty($validated['send_now'])) {
            $this->sendAnnouncement($announcement);
        }

        app(AuditLogService::class)->logUpdate(
            'announcements', $announcement,
            [], $announcement->toArray(),
            "Updated announcement: {$announcement->title}"
        );

        $message = !empty($validated['send_now'])
            ? 'Announcement has been updated and sent successfully.'
            : 'Announcement has been updated.';

        return redirect()->route('admin.announcements.index')->with('success', $message);
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->isSent()) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'Cannot delete a sent announcement.');
        }

        app(AuditLogService::class)->logDelete(
            'announcements', $announcement,
            "Deleted announcement: {$announcement->title}"
        );

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement draft has been deleted.');
    }

    public function send(Request $request, Announcement $announcement)
    {
        if ($announcement->isSent()) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'This announcement has already been sent.');
        }

        $this->sendAnnouncement($announcement);

        app(AuditLogService::class)->log(
            'sent', 'announcements', (string) $announcement->getKey(),
            "Sent announcement: {$announcement->title}",
            'success', [], $announcement->toArray(),
            ['target_roles' => $announcement->target_roles, 'type' => $announcement->type]
        );

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement has been sent successfully.');
    }

    protected function sendAnnouncement(Announcement $announcement): void
    {
        $query = User::query();

        if (!$announcement->targetsAll()) {
            $query->whereIn('role', $announcement->target_roles);
        }

        $users = $query->get();

        $sendInApp = in_array($announcement->type, ['in_app', 'both']);
        $sendEmail = in_array($announcement->type, ['email', 'both']);

        foreach ($users as $user) {
            if ($sendInApp) {
                $this->notificationService->createNotification(
                    $user,
                    'announcement',
                    $announcement->title,
                    $announcement->message,
                    $announcement->link
                );
            }

            if ($sendEmail && $user->email) {
                $this->mailer->sendGenericEmail(
                    $user->email,
                    $user->name,
                    $announcement->title,
                    $this->buildEmailBody($announcement, $user)
                );
            }
        }

        $announcement->markAsSent();
    }

    protected function buildEmailBody(Announcement $announcement, User $user): string
    {
        $linkHtml = $announcement->link
            ? "<p style='margin: 16px 0; text-align: center;'>
                <a href='{$announcement->link}' style='display: inline-block; padding: 12px 24px; background: #6366f1; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;'>View Details</a>
               </p>"
            : '';

        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 20px;'>" . e($announcement->title) . "</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #6b7280; font-size: 14px; margin: 0 0 8px 0;'>Dear " . e($user->name) . ",</p>
                                <div style='color: #374151; font-size: 15px; line-height: 1.7;'>" . nl2br(e($announcement->message)) . "</div>
                                {$linkHtml}
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated announcement from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }
}

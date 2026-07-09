<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ObservationController extends Controller
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
        $schoolHead = Auth::user()->schoolHeadProfile;

        if (!$schoolHead) {
            return redirect()->route('school-head.dashboard')->with('error', 'School head profile not found.');
        }

        $query = Observation::with(['observer'])
            ->where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $observations = $query
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->stage, fn($q, $s) => $q->where('stage', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Observation::where('observee_id', $schoolHead->id)
                ->where('observee_type', SchoolHeadProfile::class)->count(),
            'upcoming' => Observation::where('observee_id', $schoolHead->id)
                ->where('observee_type', SchoolHeadProfile::class)
                ->whereIn('status', ['scheduled'])->count(),
            'completed' => Observation::where('observee_id', $schoolHead->id)
                ->where('observee_type', SchoolHeadProfile::class)
                ->where('status', 'completed')->count(),
        ];

        return view('school-head.observations.index', compact('observations', 'stats'));
    }

    public function show(Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403, 'You are not authorized to view this observation.');
        }

        $observation->load([
            'observer',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
        ]);

        return view('school-head.observations.show', compact('observation'));
    }

    public function confirm(Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if (!$observation->canConfirm()) {
            return back()->with('error', 'This observation cannot be confirmed at this time.');
        }

        $observation->confirm();

        app(AuditLogService::class)->log(
            'confirmed', 'observations', (string) $observation->getKey(),
            "School head confirmed observation #{$observation->getKey()}",
            'success', [], $observation->toArray()
        );

        $observer = $observation->observer;
        if ($observer) {
            $link = route('supervisor.observations.show', $observation);
            $this->notificationService->notifyObservationConfirmed($observer, Auth::user()->name, $link);

            $name = Auth::user()->name;
            $subject = "Observation Confirmed – {$observation->subject}";
            $this->mailer->sendGenericEmail(
                $observer->email, $observer->name, $subject,
                $this->buildConfirmedEmail($name, $subject, $observation, $link)
            );
        }

        return back()->with('success', 'You have confirmed the observation schedule.');
    }

    public function reject(Request $request, Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if (!$observation->canConfirm()) {
            return back()->with('error', 'This observation cannot be rejected at this time.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'in:scheduling_conflict,health_concern,insufficient_preparation,other'],
            'rejection_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['rejection_reason'];
        $notes = $validated['rejection_notes'] ?? null;

        $observation->reject($reason, $notes);

        app(AuditLogService::class)->log(
            'rejected', 'observations', (string) $observation->getKey(),
            "School head rejected observation #{$observation->getKey()}: {$reason}",
            'success', [], $observation->toArray(),
            ['rejection_reason' => $reason, 'rejection_notes' => $notes]
        );

        $observer = $observation->observer;
        if ($observer) {
            $link = route('supervisor.observations.show', $observation);
            $this->notificationService->notifyObservationRejected($observer, Auth::user()->name, str_replace('_', ' ', ucwords($reason)), $link);

            $name = Auth::user()->name;
            $subject = "Observation Rejected – {$observation->subject}";
            $this->mailer->sendGenericEmail(
                $observer->email, $observer->name, $subject,
                $this->buildRejectedEmail($name, $subject, $observation, $reason, $notes, $link)
            );
        }

        return back()->with('success', 'You have rejected the observation schedule. Your supervisor will be notified.');
    }

    public function uploadPlan(Request $request, Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if ($observation->stage !== 'pre_observation_planning') {
            return back()->with('error', 'Plan can only be uploaded during the Pre-Observation Planning stage.');
        }

        $validated = $request->validate([
            'plan_file' => ['required', 'file', 'mimes:pdf,doc,docx,pptx,xlsx', 'max:20480'],
            'plan_type' => ['required', 'string', 'in:leadership_plan,lesson_plan,other'],
        ]);

        $file = $request->file('plan_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filePath = $file->storeAs('leadership_plans', $filename, 'public');

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'lesson_plan_file' => $filePath,
                'lesson_plan_notes' => $validated['plan_type'],
            ]
        );

        app(AuditLogService::class)->log(
            'plan_uploaded', 'observations', (string) $observation->getKey(),
            "School head uploaded plan for observation #{$observation->getKey()}",
            'success', [], $observation->toArray()
        );

        $observer = $observation->observer;
        if ($observer) {
            $name = Auth::user()->name;
            $link = route('supervisor.observations.show', $observation);
            $this->notificationService->notifyLessonPlanUploaded($observer, $name, $link);

            $subject = "Leadership Plan Uploaded – {$observation->subject}";
            $this->mailer->sendGenericEmail(
                $observer->email, $observer->name, $subject,
                $this->buildPlanUploadedEmail($name, $subject, $observation, $link)
            );
        }

        return back()->with('success', 'Plan uploaded successfully.');
    }

    protected function buildConfirmedEmail(string $name, string $subject, Observation $observation, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #059669; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Observation Confirmed</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$name}</strong> has confirmed the following observation:
                                </p>
                                <table style='background-color: #ecfdf5; border-left: 4px solid #059669; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>School Year:</strong> {$observation->school_year}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Date:</strong> ' . ($observation->observation_date?->format('M d, Y') ?? 'No date') . '</td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated notification from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }

    protected function buildRejectedEmail(string $name, string $subject, Observation $observation, string $reason, ?string $notes, string $link): string
    {
        $reasonLabel = str_replace('_', ' ', ucwords($reason));
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #dc2626; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Observation Rejected</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$name}</strong> has rejected the following observation:
                                </p>
                                <table style='background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Reason:</strong> {$reasonLabel}</td></tr>
                                </table>
                                " . ($notes ? "<p style='color: #374151; font-size: 14px; line-height: 1.6; margin: 0 0 16px 0;'><strong>Notes:</strong> {$notes}</p>" : "") . "
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated notification from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }

    protected function buildPlanUploadedEmail(string $name, string $subject, Observation $observation, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #2563eb; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Leadership Plan Uploaded</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$name}</strong> has uploaded a leadership plan for the following observation:
                                </p>
                                <table style='background-color: #f0fdf4; border-left: 4px solid #2563eb; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>School Year:</strong> {$observation->school_year}</td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated notification from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }
}

<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\PreObservationPlanning;
use App\Models\Teacher;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')->with('error', 'Teacher profile not found.');
        }

        $query = Observation::with(['observer'])
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
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
            'total' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)->count(),
            'upcoming' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereIn('status', ['scheduled'])->count(),
            'completed' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->where('status', 'completed')->count(),
        ];

        return view('teacher.observations.index', compact('observations', 'stats'));
    }

    public function show(Observation $observation)
    {
        $teacher = Auth::user()->teacher;

        if ($observation->observee_id !== $teacher?->id || $observation->observee_type !== Teacher::class) {
            abort(403, 'You are not authorized to view this observation.');
        }

        $observation->load([
            'observer',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
        ]);

        return view('teacher.observations.show', compact('observation'));
    }

    public function uploadLessonPlan(Request $request, Observation $observation)
    {
        $teacher = Auth::user()->teacher;

        if ($observation->observee_id !== $teacher?->id || $observation->observee_type !== Teacher::class) {
            abort(403);
        }

        if ($observation->stage !== 'pre_observation_planning') {
            return back()->with('error', 'Lesson plan can only be uploaded during the Pre-Observation Planning stage.');
        }

        $validated = $request->validate([
            'lesson_plan_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $file = $request->file('lesson_plan_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filePath = $file->storeAs('lesson_plans', $filename, 'public');

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['lesson_plan_file' => $filePath]
        );

        $this->notifyLessonPlanUploaded($observation, $teacher);

        return back()->with('success', 'Lesson plan uploaded successfully.');
    }

    protected function notifyLessonPlanUploaded(Observation $observation, Teacher $teacher): void
    {
        $teacherUser = $teacher->user;
        $teacherName = $teacherUser?->name ?? 'A teacher';
        $subject = "Lesson Plan Uploaded – {$observation->subject}";

        // Notify the observer (supervisor)
        $observer = $observation->observer;
        if ($observer && $observer instanceof \App\Models\User) {
            $link = route('supervisor.observations.show', $observation);
            $this->notificationService->notifyLessonPlanUploaded($observer, $teacherName, $link);
            $this->mailer->sendGenericEmail(
                $observer->email, $observer->name, $subject,
                $this->buildLessonPlanUploadedEmail($teacherName, $subject, $observation, $link)
            );
        }

        // Notify the school head(s) of the teacher's school
        $school = $teacher->school;
        if ($school) {
            $schoolHeads = $school->users()->where('role', 'school_head')->get();
            foreach ($schoolHeads as $schoolHead) {
                $link = route('supervisor.observations.show', $observation);
                $this->notificationService->notifyLessonPlanUploaded($schoolHead, $teacherName, $link);
                $this->mailer->sendGenericEmail(
                    $schoolHead->email, $schoolHead->name, $subject,
                    $this->buildLessonPlanUploadedEmail($teacherName, $subject, $observation, $link)
                );
            }
        }
    }

    protected function buildLessonPlanUploadedEmail(string $teacherName, string $subject, Observation $observation, string $observationLink): string
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
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Lesson Plan Uploaded</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$teacherName}</strong> has uploaded a lesson plan for the following observation:
                                </p>
                                <table style='background-color: #f0fdf4; border-left: 4px solid #2563eb; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Grade Level:</strong> {$observation->grade_level}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>School Year:</strong> {$observation->school_year}</td></tr>
                                </table>
                                <p style='text-align: center; margin: 30px 0 0 0;'>
                                    <a href='{$observationLink}'
                                       style='display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 6px; font-size: 15px; font-weight: bold;'>
                                        View Observation
                                    </a>
                                </p>
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

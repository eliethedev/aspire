<?php

use App\Http\Controllers\Admin\AIController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CotIndicatorController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\ObservationController;
use App\Http\Controllers\Admin\PpstStandardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SupportMessageController as AdminSupportMessageController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Profile\AdminProfileController;
use App\Http\Controllers\Profile\SchoolHeadProfileController;
use App\Http\Controllers\Profile\SupervisorProfileController;
use App\Http\Controllers\Profile\TeacherProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolHead\LessonPlanController;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\SupportMessageController;
use App\Http\Controllers\Supervisor\CoachingAgreementController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\Teacher\CoachingController;
use App\Http\Controllers\Teacher\FeedbackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserInvitationController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'profile.complete'])->name('dashboard');

// Invitation-based password setup (public route for invited users)
Route::get('/auth/set-password/{token}', [SetPasswordController::class, 'show'])->name('auth.set-password');
Route::post('/auth/set-password', [SetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('auth.set-password.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User preferences (e.g., hide/re-show the observation rating sheet tip)
    Route::post('/preferences/observation-tip-dismiss', [UserPreferenceController::class, 'dismissObservationTip'])
        ->name('preferences.observation-tip-dismiss');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->middleware('throttle:30,1')->name('notifications.recent');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->middleware('throttle:30,1')->name('notifications.mark-read');
    Route::post('/notifications/{id}/mark-unread', [NotificationController::class, 'markAsUnread'])->middleware('throttle:30,1')->name('notifications.mark-unread');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('throttle:10,1')->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'destroy'])->middleware('throttle:30,1')->name('notifications.destroy');
    // Legacy per-role URL — kept for older links, redirects to the unified page.
    Route::get('/notifications/{role}', [NotificationController::class, 'show'])->name('notifications.show');

    // Support messages (bug reports / feedback to the admin team)
    Route::get('/support', [SupportMessageController::class, 'index'])->name('support.index');
    Route::get('/support/create', [SupportMessageController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportMessageController::class, 'store'])->middleware('throttle:10,1')->name('support.store');
    Route::get('/support/{supportMessage}', [SupportMessageController::class, 'show'])->name('support.show');
});

// School management routes (admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/status', [App\Http\Controllers\Admin\DashboardController::class, 'systemStatus'])->name('dashboard.status');
    Route::get('/calendar', [App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');

    // Profile
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

    Route::resource('schools', App\Http\Controllers\Admin\SchoolController::class);

    Route::post('/schools/{school}/users', [App\Http\Controllers\Admin\SchoolController::class, 'addUser'])->name('schools.users.add');
    Route::delete('/schools/{school}/users/{user}', [App\Http\Controllers\Admin\SchoolController::class, 'removeUser'])->name('schools.users.remove');
    Route::get('/schools/{school}/users', [App\Http\Controllers\Admin\SchoolController::class, 'users'])->name('schools.users.index');

    // User management routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordReset'])->name('users.sendPasswordReset');

    // User invitation routes
    Route::get('invitations', [UserInvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitations/create', [UserInvitationController::class, 'create'])->name('invitations.create');
    Route::post('invitations', [UserInvitationController::class, 'store'])->middleware('throttle:invitations')->name('invitations.store');
    Route::get('invitations/{invitation}', [UserInvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitations/{invitation}/resend', [UserInvitationController::class, 'resend'])->middleware('throttle:invitations')->name('invitations.resend');
    Route::post('invitations/{invitation}/cancel', [UserInvitationController::class, 'cancel'])->name('invitations.cancel');
    Route::delete('invitations/{invitation}', [UserInvitationController::class, 'destroy'])->name('invitations.destroy');

    // Supervisor management routes
    Route::resource('supervisors', App\Http\Controllers\Admin\SupervisorController::class);
    Route::get('/supervisors/schools/list', [App\Http\Controllers\Admin\SupervisorController::class, 'schools'])->name('supervisors.schools');
    Route::get('/supervisors/positions/list', [App\Http\Controllers\Admin\SupervisorController::class, 'positions'])->name('supervisors.positions');

    // Teacher management routes
    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);
    Route::post('/teachers/{teacher}/career-assessment', [App\Http\Controllers\Admin\TeacherController::class, 'storeCareerAssessment'])->name('teachers.career-assessment');

    // Audit log management
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Observation management
    Route::get('/observations', [ObservationController::class, 'index'])->middleware('throttle:search')->name('observations.index');
    Route::get('/observations/{observation}', [ObservationController::class, 'show'])->name('observations.show');
    Route::get('/observations/{observation}/cot-document', [ObservationController::class, 'downloadCotDocument'])->middleware('throttle:exports')->name('observations.cot-document');
    Route::get('/observations/{observation}/epoc-document', [ObservationController::class, 'downloadEpocDocument'])->middleware('throttle:exports')->name('observations.epoc-document');

    // Announcement management
    Route::resource('announcements', AnnouncementController::class);
    Route::post('announcements/{announcement}/send', [AnnouncementController::class, 'send'])->name('announcements.send');

    // AI settings management
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AIController::class, 'index'])->name('index');
        Route::post('/', [AIController::class, 'update'])->name('update');
        Route::post('/test', [AIController::class, 'test'])->name('test');
        Route::post('/test-provider', [AIController::class, 'testProvider'])->name('test-provider');
        Route::post('/restore', [AIController::class, 'restore'])->name('restore');
        Route::post('/emergency', [AIController::class, 'emergency'])->name('emergency');
        Route::post('/providers', [AIController::class, 'storeCustomProvider'])->name('providers.store');
        Route::post('/providers/{customAiProvider}', [AIController::class, 'updateCustomProvider'])->name('providers.update');
        Route::delete('/providers/{customAiProvider}', [AIController::class, 'destroyCustomProvider'])->name('providers.destroy');
        Route::post('/providers/{customAiProvider}/test', [AIController::class, 'testCustomProvider'])->name('providers.test');
    });

    // AI usage & cost monitoring
    Route::prefix('ai-usage')->name('ai-usage.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AiUsageController::class, 'index'])->name('index');
        Route::get('/users/{user}', [App\Http\Controllers\Admin\AiUsageController::class, 'show'])->name('users.show');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Form template management
    Route::prefix('form-templates')->name('form-templates.')->group(function () {
        Route::get('/', [FormTemplateController::class, 'index'])->name('index');
        Route::get('/create', [FormTemplateController::class, 'create'])->name('create');
        Route::post('/', [FormTemplateController::class, 'store'])->name('store');
        Route::get('/{formTemplate}/edit', [FormTemplateController::class, 'edit'])->name('edit');
        Route::put('/{formTemplate}', [FormTemplateController::class, 'update'])->name('update');
        Route::delete('/{formTemplate}', [FormTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{formTemplate}/activate', [FormTemplateController::class, 'activate'])->name('activate');
        Route::post('/{formTemplate}/duplicate', [FormTemplateController::class, 'duplicate'])->name('duplicate');
    });

    // PPST standards management
    Route::prefix('ppst-standards')->name('ppst-standards.')->group(function () {
        Route::get('/', [PpstStandardController::class, 'index'])->name('index');
        Route::get('/create', [PpstStandardController::class, 'create'])->name('create');
        Route::post('/', [PpstStandardController::class, 'store'])->name('store');
        Route::get('/{ppstStandard}/edit', [PpstStandardController::class, 'edit'])->name('edit');
        Route::put('/{ppstStandard}', [PpstStandardController::class, 'update'])->name('update');
        Route::post('/{ppstStandard}/toggle-active', [PpstStandardController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{ppstStandard}', [PpstStandardController::class, 'destroy'])->name('destroy');
    });

    // COT/PPST indicator version management
    Route::prefix('cot-indicators')->name('cot-indicators.')->group(function () {
        Route::get('/', [CotIndicatorController::class, 'index'])->name('index');
        Route::get('/create', [CotIndicatorController::class, 'create'])->name('create');
        Route::post('/', [CotIndicatorController::class, 'store'])->name('store');
        Route::get('/{cotIndicatorVersion}/edit', [CotIndicatorController::class, 'edit'])->name('edit');
        Route::put('/{cotIndicatorVersion}', [CotIndicatorController::class, 'update'])->name('update');
        Route::delete('/{cotIndicatorVersion}', [CotIndicatorController::class, 'destroy'])->name('destroy');
        Route::post('/{cotIndicatorVersion}/publish', [CotIndicatorController::class, 'publish'])->name('publish');
        Route::post('/{cotIndicatorVersion}/unpublish', [CotIndicatorController::class, 'unpublish'])->name('unpublish');
        Route::post('/{cotIndicatorVersion}/archive', [CotIndicatorController::class, 'archive'])->name('archive');
        Route::get('/{cotIndicatorVersion}/template', [CotIndicatorController::class, 'downloadTemplate'])
            ->middleware('throttle:exports')->name('template');

        Route::post('/{cotIndicatorVersion}/indicators', [CotIndicatorController::class, 'storeIndicator'])->name('indicators.store');
        Route::post('/{cotIndicatorVersion}/indicators/from-standard', [CotIndicatorController::class, 'addStandardIndicator'])->name('indicators.from-standard');
        Route::put('/{cotIndicatorVersion}/indicators', [CotIndicatorController::class, 'updateIndicator'])->name('indicators.update');
        Route::delete('/{cotIndicatorVersion}/indicators', [CotIndicatorController::class, 'destroyIndicator'])->name('indicators.destroy');
        Route::post('/{cotIndicatorVersion}/indicators/reorder', [CotIndicatorController::class, 'reorderIndicators'])->name('indicators.reorder');
        Route::post('/{cotIndicatorVersion}/indicators/{cotIndicator}/move', [CotIndicatorController::class, 'moveIndicator'])->name('indicators.move');
    });

    // Support messages from users (bug reports / feedback)
    Route::get('/support-messages', [AdminSupportMessageController::class, 'index'])->name('support-messages.index');
    Route::get('/support-messages/{supportMessage}', [AdminSupportMessageController::class, 'show'])->name('support-messages.show');
    Route::patch('/support-messages/{supportMessage}', [AdminSupportMessageController::class, 'update'])->name('support-messages.update');
    Route::delete('/support-messages/{supportMessage}', [AdminSupportMessageController::class, 'destroy'])->name('support-messages.destroy');
});

// Teacher routes
Route::middleware(['auth', 'role:teacher', 'profile.complete'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [App\Http\Controllers\Teacher\DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/calendar', [App\Http\Controllers\Teacher\CalendarController::class, 'index'])->name('calendar.index');

    // Profile
    Route::get('/profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [TeacherProfileController::class, 'update'])->name('profile.update');

    Route::get('/observations', [App\Http\Controllers\Teacher\ObservationController::class, 'index'])->middleware('throttle:search')->name('observations.index');
    Route::get('/observations/{observation}', [App\Http\Controllers\Teacher\ObservationController::class, 'show'])->name('observations.show');
    Route::get('/observations/{observation}/cot-document', [App\Http\Controllers\Teacher\ObservationController::class, 'downloadCotDocument'])->middleware('throttle:exports')->name('observations.cot-document');
    Route::post('/observations/{observation}/upload-lesson-plan', [App\Http\Controllers\Teacher\ObservationController::class, 'uploadLessonPlan'])->middleware('throttle:uploads')->name('observations.upload-lesson-plan');
    Route::patch('/observations/{observation}/update-location', [App\Http\Controllers\Teacher\ObservationController::class, 'updateLocation'])->name('observations.update-location');
    Route::post('/observations/{observation}/confirm', [App\Http\Controllers\Teacher\ObservationController::class, 'confirm'])->name('observations.confirm');
    Route::post('/observations/{observation}/reject', [App\Http\Controllers\Teacher\ObservationController::class, 'reject'])->name('observations.reject');

    // Feedback & Coaching
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::get('/coaching', [CoachingController::class, 'index'])->name('coaching.index');
    Route::get('/coaching/{agreement}', [CoachingController::class, 'show'])->name('coaching.show');
    Route::post('/coaching/{agreement}/sign', [CoachingController::class, 'sign'])->name('coaching.sign');
});

// Supervisor routes
Route::middleware(['auth', 'role:supervisor', 'profile.complete'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');

    // Profile
    Route::get('/profile', [SupervisorProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/observations', [CalendarController::class, 'getObservationsByDate'])->name('calendar.observations');

    Route::get('/teachers', [SupervisorController::class, 'teachers'])->name('teachers.index');
    Route::get('/teachers/{teacher}', [SupervisorController::class, 'teacherProfile'])->name('teachers.show');
        Route::post('/teachers/{teacher}/career-assessment', [SupervisorController::class, 'storeCareerAssessment'])->name('teachers.career-assessment');
        Route::put('/teachers/{teacher}/career-assessment/{assessment}', [SupervisorController::class, 'updateCareerAssessment'])->name('teachers.career-assessment.update');
    Route::get('/career-progression', [SupervisorController::class, 'careerProgression'])->name('career.index');
    Route::get('/career-monitor', [SupervisorController::class, 'careerMonitor'])->name('career.monitor');
    Route::post('/teachers/{teacher}/career-stage/allow', [SupervisorController::class, 'allowCareerStage'])->name('career.allow');
    Route::post('/teachers/{teacher}/career-stage/announce', [SupervisorController::class, 'announceCareerStage'])->name('career.announce');
    Route::post('/career-advancements/{advancement}/cancel', [SupervisorController::class, 'cancelCareerAdvancement'])->name('career.cancel');
    Route::get('/school-heads', [SupervisorController::class, 'schoolHeads'])->name('school-heads.index');
    Route::get('/school-heads/{schoolHead}', [SupervisorController::class, 'schoolHeadProfile'])->name('school-heads.show');
    Route::get('/school-heads/{schoolHead}/observations', [SupervisorController::class, 'schoolHeadObservationHistory'])->name('school-heads.observations');
    Route::get('/observations', [SupervisorController::class, 'observations'])->middleware('throttle:search')->name('observations.index');
    Route::get('/observations/create', [SupervisorController::class, 'createObservation'])->name('observations.create');
    Route::post('/observations', [SupervisorController::class, 'storeObservation'])->name('observations.store');
    Route::get('/observations/{observation}', [SupervisorController::class, 'showObservation'])->name('observations.show');
    Route::get('/observations/{observation}/cancel', [SupervisorController::class, 'showCancelForm'])->name('observations.cancel-form');
    Route::post('/observations/{observation}/cancel', [SupervisorController::class, 'cancel'])->name('observations.cancel');

    // Stage-specific routes
    Route::get('/observations/{observation}/pre-observation-planning', [SupervisorController::class, 'preObservationPlanning'])->name('observations.preObservationPlanning');
    Route::post('/observations/{observation}/pre-observation-planning', [SupervisorController::class, 'storePreObservationPlanning'])->name('observations.storePreObservationPlanning');
    Route::post('/observations/{observation}/request-lesson-plan', [SupervisorController::class, 'requestLessonPlan'])->name('observations.request-lesson-plan');
    Route::get('/observations/{observation}/pre-conference', [SupervisorController::class, 'preConference'])->name('observations.preConference');
    Route::post('/observations/{observation}/pre-conference', [SupervisorController::class, 'storePreConference'])->name('observations.storePreConference');
    Route::post('/observations/{observation}/agenda-checklist', [SupervisorController::class, 'saveAgendaChecklist'])->name('observations.agenda-checklist');
    Route::get('/observations/{observation}/observation', [SupervisorController::class, 'observation'])->name('observations.observation');
    Route::post('/observations/{observation}/observation', [SupervisorController::class, 'storeObservationData'])->name('observations.storeObservationData');
    Route::post('/observations/{observation}/autosave', [SupervisorController::class, 'autosave'])->name('observations.autosave');
    Route::get('/observations/{observation}/post-conference', [SupervisorController::class, 'postConference'])->name('observations.postConference');
    Route::post('/observations/{observation}/post-conference', [SupervisorController::class, 'storePostConference'])->name('observations.storePostConference');
    Route::post('/observations/{observation}/finalize', [SupervisorController::class, 'finalize'])->name('observations.finalize');
    Route::get('/observations/{observation}/epoc', [SupervisorController::class, 'epocEvaluation'])->name('observations.epoc');
    Route::post('/observations/{observation}/epoc', [SupervisorController::class, 'storeEPOC'])->name('observations.storeEPOC');
    Route::get('/observations/{observation}/epoc/download', [SupervisorController::class, 'downloadEpoc'])->name('observations.epoc.download');

    Route::get('/reports', [SupervisorController::class, 'reports'])->name('reports.index');
    Route::get('/reports/export', [SupervisorController::class, 'exportReports'])->middleware('throttle:exports')->name('reports.export');

    // Teacher observation history
    Route::get('/teachers/{observeeId}/observations', [SupervisorController::class, 'teacherObservationHistory'])->name('observations.teacher-history');

    // AI-powered insights
    Route::post('/observations/{observation}/generate-ai-insights', [SupervisorController::class, 'generateAiInsights'])->middleware('ai.rate.limit')->name('observations.generate-ai-insights');
    Route::get('/observations/{observation}/ai-insights-status', [SupervisorController::class, 'aiInsightsStatus'])->name('observations.ai-insights-status');
    Route::post('/observations/{observation}/generate-ai-suggestions', [SupervisorController::class, 'generateAiSuggestions'])->middleware('ai.rate.limit')->name('observations.generate-ai-suggestions');
    Route::delete('/observations/{observation}/clear-ai-insights', [SupervisorController::class, 'clearAiInsights'])->name('observations.clear-ai-insights');
    Route::post('/observations/{observation}/generate-ai-comparison', [SupervisorController::class, 'generateAiComparison'])->middleware('ai.rate.limit')->name('observations.generate-ai-comparison');
    Route::post('/observations/{observation}/generate-observation-suggestions', [SupervisorController::class, 'generateObservationSuggestions'])->middleware('ai.rate.limit')->name('observations.generate-observation-suggestions');

    // Goal-specific AI tasks (dynamic routing + fallback + per-task limits)
    Route::prefix('observations/{observation}/ai-tasks')->name('ai-tasks.')->middleware('ai.rate.limit')->group(function () {
        Route::post('/lesson-plan-suggestions', [\App\Http\Controllers\Supervisor\AITaskController::class, 'lessonPlanSuggestions'])->name('lesson-plan-suggestions');
        Route::post('/lesson-plan-summary', [\App\Http\Controllers\Supervisor\AITaskController::class, 'lessonPlanSummary'])->name('lesson-plan-summary');
        Route::post('/cot-ratings/{cotRating}/analysis', [\App\Http\Controllers\Supervisor\AITaskController::class, 'cotIndicatorAnalysis'])->name('cot-indicator-analysis');
        Route::post('/overall-recommendation', [\App\Http\Controllers\Supervisor\AITaskController::class, 'overallRecommendation'])->name('overall-recommendation');
    });

    // Post-Observation Report
    Route::get('/observations/{observation}/report', [SupervisorController::class, 'downloadReport'])->middleware('throttle:exports')->name('observations.report');
    Route::get('/observations/{observation}/report/pdf', [SupervisorController::class, 'downloadReportPDF'])->middleware('throttle:exports')->name('observations.report-pdf');

    // Completed COT Document
    Route::post('/observations/{observation}/cot-document/generate', [SupervisorController::class, 'generateCotDocument'])->middleware('throttle:exports')->name('observations.cot-document.generate');
    Route::get('/observations/{observation}/cot-document/preview', [SupervisorController::class, 'previewCotDocument'])->middleware('throttle:exports')->name('observations.cot-document.preview');
    Route::get('/observations/{observation}/cot-document/download', [SupervisorController::class, 'downloadCotDocument'])->middleware('throttle:exports')->name('observations.cot-document.download');
    Route::get('/observations/{observation}/cot-document/pdf', [SupervisorController::class, 'downloadCotPdf'])->middleware('throttle:exports')->name('observations.cot-document.pdf');

    // Indicator Trends & Progress Comparison
    Route::get('/observations/{observation}/indicator-trends', [SupervisorController::class, 'indicatorTrends'])->name('observations.indicator-trends');
    Route::get('/observations/{observation}/progress-comparison', [SupervisorController::class, 'progressComparison'])->name('observations.progress-comparison');
    Route::get('/observations/{observation}/pd-recommendations', [SupervisorController::class, 'pdRecommendations'])->name('observations.pd-recommendations');

    // Feedback Management
    Route::get('/feedback', [App\Http\Controllers\Supervisor\FeedbackController::class, 'center'])->name('feedback.center');
    Route::prefix('observations/{observation}/feedback')->name('feedback.')->group(function () {
        Route::get('/', [App\Http\Controllers\Supervisor\FeedbackController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Supervisor\FeedbackController::class, 'create'])->name('create');
        Route::post('/generate', [App\Http\Controllers\Supervisor\FeedbackController::class, 'generate'])->middleware('ai.rate.limit')->name('generate');
        Route::post('/generate-ai', [App\Http\Controllers\Supervisor\FeedbackController::class, 'generateAi'])->middleware('ai.rate.limit')->name('generate-ai');
        Route::get('/{feedback}/edit', [App\Http\Controllers\Supervisor\FeedbackController::class, 'edit'])->name('edit');
        Route::patch('/{feedback}', [App\Http\Controllers\Supervisor\FeedbackController::class, 'update'])->name('update');
        Route::post('/{feedback}/publish', [App\Http\Controllers\Supervisor\FeedbackController::class, 'publish'])->name('publish');
        Route::get('/{feedback}/export', [App\Http\Controllers\Supervisor\FeedbackController::class, 'export'])->middleware('throttle:exports')->name('export');
        Route::delete('/{feedback}', [App\Http\Controllers\Supervisor\FeedbackController::class, 'destroy'])->name('destroy');
    });

    // Coaching Agreements
    Route::get('/coaching', [CoachingAgreementController::class, 'index'])->name('coaching.index');
    Route::get('/observations/{observation}/coaching/create', [CoachingAgreementController::class, 'create'])->name('coaching.create');
    Route::post('/coaching', [CoachingAgreementController::class, 'store'])->name('coaching.store');
    Route::get('/coaching/{agreement}', [CoachingAgreementController::class, 'show'])->name('coaching.show');
    Route::get('/coaching/{agreement}/edit', [CoachingAgreementController::class, 'edit'])->name('coaching.edit');
    Route::patch('/coaching/{agreement}', [CoachingAgreementController::class, 'update'])->name('coaching.update');
    Route::post('/coaching/{agreement}/sign', [CoachingAgreementController::class, 'sign'])->name('coaching.sign');
    Route::get('/coaching/{agreement}/export', [CoachingAgreementController::class, 'export'])->middleware('throttle:exports')->name('coaching.export');
    Route::delete('/coaching/{agreement}', [CoachingAgreementController::class, 'destroy'])->name('coaching.destroy');
});

// School Head routes
Route::middleware(['auth', 'role:school_head', 'profile.complete'])->prefix('school-head')->name('school-head.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SchoolHead\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [App\Http\Controllers\SchoolHead\CalendarController::class, 'index'])->name('calendar.index');

    // Profile
    Route::get('/profile', [SchoolHeadProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [SchoolHeadProfileController::class, 'update'])->name('profile.update');

    // Observations (My Performance)
    Route::get('/observations', [App\Http\Controllers\SchoolHead\ObservationController::class, 'index'])->middleware('throttle:search')->name('observations.index');
    Route::get('/observations/create', [App\Http\Controllers\SchoolHead\ObservationController::class, 'createObservation'])->name('observations.create');
    Route::post('/observations', [App\Http\Controllers\SchoolHead\ObservationController::class, 'storeObservation'])->name('observations.store');
    Route::get('/observations/{observation}', [App\Http\Controllers\SchoolHead\ObservationController::class, 'show'])->name('observations.show');
    Route::get('/co-observations', [App\Http\Controllers\SchoolHead\ObservationController::class, 'coObservations'])->middleware('throttle:search')->name('co-observations.index');
    Route::post('/observations/{observation}/confirm', [App\Http\Controllers\SchoolHead\ObservationController::class, 'confirm'])->name('observations.confirm');
    Route::post('/observations/{observation}/reject', [App\Http\Controllers\SchoolHead\ObservationController::class, 'reject'])->name('observations.reject');
    Route::post('/observations/{observation}/upload-plan', [App\Http\Controllers\SchoolHead\ObservationController::class, 'uploadPlan'])->middleware('throttle:uploads')->name('observations.upload-plan');

    // Observation workflow stages (school head as observer)
    Route::get('/observations/{observation}/pre-observation-planning', [App\Http\Controllers\SchoolHead\ObservationController::class, 'preObservationPlanning'])->name('observations.preObservationPlanning');
    Route::post('/observations/{observation}/pre-observation-planning', [App\Http\Controllers\SchoolHead\ObservationController::class, 'storePreObservationPlanning'])->name('observations.storePreObservationPlanning');
    Route::post('/observations/{observation}/request-lesson-plan', [App\Http\Controllers\SchoolHead\ObservationController::class, 'requestLessonPlan'])->name('observations.request-lesson-plan');
    Route::get('/observations/{observation}/pre-conference', [App\Http\Controllers\SchoolHead\ObservationController::class, 'preConference'])->name('observations.preConference');
    Route::post('/observations/{observation}/pre-conference', [App\Http\Controllers\SchoolHead\ObservationController::class, 'storePreConference'])->name('observations.storePreConference');
    Route::post('/observations/{observation}/agenda-checklist', [App\Http\Controllers\SchoolHead\ObservationController::class, 'saveAgendaChecklist'])->name('observations.agenda-checklist');
    Route::get('/observations/{observation}/observation', [App\Http\Controllers\SchoolHead\ObservationController::class, 'observation'])->name('observations.observation');
    Route::post('/observations/{observation}/observation', [App\Http\Controllers\SchoolHead\ObservationController::class, 'storeObservationData'])->name('observations.storeObservationData');
    Route::get('/observations/{observation}/post-conference', [App\Http\Controllers\SchoolHead\ObservationController::class, 'postConference'])->name('observations.postConference');
    Route::post('/observations/{observation}/post-conference', [App\Http\Controllers\SchoolHead\ObservationController::class, 'storePostConference'])->name('observations.storePostConference');

    // AI-powered insights (school head as observer)
    Route::post('/observations/{observation}/generate-ai-insights', [App\Http\Controllers\SchoolHead\ObservationController::class, 'generateAiInsights'])->middleware('ai.rate.limit')->name('observations.generate-ai-insights');
    Route::get('/observations/{observation}/ai-insights-status', [App\Http\Controllers\SchoolHead\ObservationController::class, 'aiInsightsStatus'])->name('observations.ai-insights-status');
    Route::delete('/observations/{observation}/clear-ai-insights', [App\Http\Controllers\SchoolHead\ObservationController::class, 'clearAiInsights'])->name('observations.clear-ai-insights');
    Route::post('/observations/{observation}/generate-ai-comparison', [App\Http\Controllers\SchoolHead\ObservationController::class, 'generateAiComparison'])->middleware('ai.rate.limit')->name('observations.generate-ai-comparison');
    Route::post('/observations/{observation}/generate-ai-suggestions', [App\Http\Controllers\SchoolHead\ObservationController::class, 'generateAiSuggestions'])->middleware('ai.rate.limit')->name('observations.generate-ai-suggestions');

    // Cancellation (school head as observer)
    Route::get('/observations/{observation}/cancel', [App\Http\Controllers\SchoolHead\ObservationController::class, 'showCancelForm'])->name('observations.cancel-form');
    Route::post('/observations/{observation}/cancel', [App\Http\Controllers\SchoolHead\ObservationController::class, 'cancel'])->name('observations.cancel');

    // Report download
    Route::get('/observations/{observation}/report', [App\Http\Controllers\SchoolHead\ObservationController::class, 'downloadReport'])->middleware('throttle:exports')->name('observations.report');
    Route::get('/observations/{observation}/report/pdf', [App\Http\Controllers\SchoolHead\ObservationController::class, 'downloadReportPDF'])->middleware('throttle:exports')->name('observations.report-pdf');
    Route::get('/observations/{observation}/cot-document', [App\Http\Controllers\SchoolHead\ObservationController::class, 'downloadCotDocument'])->middleware('throttle:exports')->name('observations.cot-document');
    Route::get('/teachers/{observeeId}/observations', [App\Http\Controllers\SchoolHead\ObservationController::class, 'teacherHistory'])->name('observations.teacher-history');

    // Indicator Trends & Progress Comparison
    Route::get('/observations/{observation}/indicator-trends', [App\Http\Controllers\SchoolHead\ObservationController::class, 'indicatorTrends'])->name('observations.indicator-trends');
    Route::get('/observations/{observation}/progress-comparison', [App\Http\Controllers\SchoolHead\ObservationController::class, 'progressComparison'])->name('observations.progress-comparison');
    Route::get('/observations/{observation}/pd-recommendations', [App\Http\Controllers\SchoolHead\ObservationController::class, 'pdRecommendations'])->name('observations.pd-recommendations');

    // Teacher management
    Route::get('/teachers', [App\Http\Controllers\SchoolHead\TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/{teacher}', [App\Http\Controllers\SchoolHead\TeacherController::class, 'show'])->name('teachers.show');

    // Career advancement approvals
    Route::get('/career-advancements', [App\Http\Controllers\SchoolHead\CareerAdvancementController::class, 'index'])->name('career.advancements.index');
    Route::post('/career-advancements/{advancement}/approve', [App\Http\Controllers\SchoolHead\CareerAdvancementController::class, 'approve'])->name('career.advancements.approve');
    Route::post('/career-advancements/{advancement}/reject', [App\Http\Controllers\SchoolHead\CareerAdvancementController::class, 'reject'])->name('career.advancements.reject');

    // Lesson Plans
    Route::get('/lesson-plans', [LessonPlanController::class, 'index'])->name('lesson-plans.index');
    Route::get('/lesson-plans/{plan}', [LessonPlanController::class, 'show'])->name('lesson-plans.show');

    // AI Feedback
    Route::get('/feedback', [App\Http\Controllers\SchoolHead\FeedbackController::class, 'index'])->name('feedback.index');

    // Coaching
    Route::get('/coaching', [App\Http\Controllers\SchoolHead\CoachingController::class, 'index'])->name('coaching.index');
    Route::get('/coaching/{agreement}', [App\Http\Controllers\SchoolHead\CoachingController::class, 'show'])->name('coaching.show');

    // Analytics & Reports
    Route::get('/reports', [App\Http\Controllers\SchoolHead\ReportController::class, 'index'])->name('reports.index');
});

// School-specific routes
Route::middleware(['school', 'require.school', 'auth'])->prefix('{school}')->name('school.')->group(function () {
    Route::get('/dashboard', function () {
        return view('school.dashboard');
    })->name('dashboard');

    // Add more school-specific routes here
});

require __DIR__.'/auth.php';

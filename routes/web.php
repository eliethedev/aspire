<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Invitation-based password setup (public route for invited users)
Route::get('/auth/set-password/{token}', [SetPasswordController::class, 'show'])->name('auth.set-password');
Route::post('/auth/set-password', [SetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('auth.set-password.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/{role}', [NotificationController::class, 'show'])->name('notifications.show');
});

// School management routes (admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Profile\AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Profile\AdminProfileController::class, 'update'])->name('profile.update');

    Route::resource('schools', \App\Http\Controllers\Admin\SchoolController::class);
    
    Route::post('/schools/{school}/users', [\App\Http\Controllers\Admin\SchoolController::class, 'addUser'])->name('schools.users.add');
    Route::delete('/schools/{school}/users/{user}', [\App\Http\Controllers\Admin\SchoolController::class, 'removeUser'])->name('schools.users.remove');
    Route::get('/schools/{school}/users', [\App\Http\Controllers\Admin\SchoolController::class, 'users'])->name('schools.users.index');
    
    // User management routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordReset'])->name('users.sendPasswordReset');
    
    // User invitation routes
    Route::resource('invitations', UserInvitationController::class);
    Route::post('invitations/{invitation}/resend', [UserInvitationController::class, 'resend'])->name('invitations.resend');
    Route::post('invitations/{invitation}/cancel', [UserInvitationController::class, 'cancel'])->name('invitations.cancel');
    
    // Supervisor management routes
    Route::resource('supervisors', \App\Http\Controllers\Admin\SupervisorController::class);
    Route::get('/supervisors/schools/list', [\App\Http\Controllers\Admin\SupervisorController::class, 'schools'])->name('supervisors.schools');
    Route::get('/supervisors/positions/list', [\App\Http\Controllers\Admin\SupervisorController::class, 'positions'])->name('supervisors.positions');
    
    // Teacher management routes
    Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);

    // Audit log management
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');

    // Observation management
    Route::get('/observations', [\App\Http\Controllers\Admin\ObservationController::class, 'index'])->name('observations.index');
    Route::get('/observations/{observation}', [\App\Http\Controllers\Admin\ObservationController::class, 'show'])->name('observations.show');

    // Announcement management
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);
    Route::post('announcements/{announcement}/send', [\App\Http\Controllers\Admin\AnnouncementController::class, 'send'])->name('announcements.send');

    // AI settings management
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AIController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AIController::class, 'update'])->name('update');
        Route::post('/test', [\App\Http\Controllers\Admin\AIController::class, 'test'])->name('test');
        Route::post('/test-provider', [\App\Http\Controllers\Admin\AIController::class, 'testProvider'])->name('test-provider');
    });

    // Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Form template management
    Route::prefix('form-templates')->name('form-templates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FormTemplateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\FormTemplateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\FormTemplateController::class, 'store'])->name('store');
        Route::get('/{formTemplate}/edit', [\App\Http\Controllers\Admin\FormTemplateController::class, 'edit'])->name('edit');
        Route::put('/{formTemplate}', [\App\Http\Controllers\Admin\FormTemplateController::class, 'update'])->name('update');
        Route::delete('/{formTemplate}', [\App\Http\Controllers\Admin\FormTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{formTemplate}/activate', [\App\Http\Controllers\Admin\FormTemplateController::class, 'activate'])->name('activate');
        Route::post('/{formTemplate}/duplicate', [\App\Http\Controllers\Admin\FormTemplateController::class, 'duplicate'])->name('duplicate');
    });
});

// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Profile\TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Profile\TeacherProfileController::class, 'update'])->name('profile.update');

    Route::get('/observations', [\App\Http\Controllers\Teacher\ObservationController::class, 'index'])->name('observations.index');
    Route::get('/observations/{observation}', [\App\Http\Controllers\Teacher\ObservationController::class, 'show'])->name('observations.show');
    Route::post('/observations/{observation}/upload-lesson-plan', [\App\Http\Controllers\Teacher\ObservationController::class, 'uploadLessonPlan'])->name('observations.upload-lesson-plan');
    Route::post('/observations/{observation}/confirm', [\App\Http\Controllers\Teacher\ObservationController::class, 'confirm'])->name('observations.confirm');
    Route::post('/observations/{observation}/reject', [\App\Http\Controllers\Teacher\ObservationController::class, 'reject'])->name('observations.reject');

    // Feedback & Coaching
    Route::get('/feedback', [\App\Http\Controllers\Teacher\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [\App\Http\Controllers\Teacher\FeedbackController::class, 'show'])->name('feedback.show');
    Route::get('/coaching', [\App\Http\Controllers\Teacher\CoachingController::class, 'index'])->name('coaching.index');
    Route::get('/coaching/{agreement}', [\App\Http\Controllers\Teacher\CoachingController::class, 'show'])->name('coaching.show');
    Route::post('/coaching/{agreement}/sign', [\App\Http\Controllers\Teacher\CoachingController::class, 'sign'])->name('coaching.sign');
});

// Supervisor routes
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Profile\SupervisorProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Profile\SupervisorProfileController::class, 'update'])->name('profile.update');

    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/observations', [\App\Http\Controllers\CalendarController::class, 'getObservationsByDate'])->name('calendar.observations');
    
    Route::get('/teachers', [SupervisorController::class, 'teachers'])->name('teachers.index');
    Route::get('/teachers/{teacher}', [SupervisorController::class, 'teacherProfile'])->name('teachers.show');
    Route::get('/school-heads', [SupervisorController::class, 'schoolHeads'])->name('school-heads.index');
    Route::get('/school-heads/{schoolHead}/observations', [SupervisorController::class, 'schoolHeadObservationHistory'])->name('school-heads.observations');
    Route::get('/observations', [SupervisorController::class, 'observations'])->name('observations.index');
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
    Route::get('/observations/{observation}/observation', [SupervisorController::class, 'observation'])->name('observations.observation');
    Route::post('/observations/{observation}/observation', [SupervisorController::class, 'storeObservationData'])->name('observations.storeObservationData');
    Route::get('/observations/{observation}/post-conference', [SupervisorController::class, 'postConference'])->name('observations.postConference');
    Route::post('/observations/{observation}/post-conference', [SupervisorController::class, 'storePostConference'])->name('observations.storePostConference');
    
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('reports.index');
    Route::get('/reports/export', [SupervisorController::class, 'exportReports'])->name('reports.export');

    // Teacher observation history
    Route::get('/teachers/{observeeId}/observations', [SupervisorController::class, 'teacherObservationHistory'])->name('observations.teacher-history');

    // AI-powered insights
    Route::post('/observations/{observation}/generate-ai-insights', [SupervisorController::class, 'generateAiInsights'])->middleware('ai.rate.limit')->name('observations.generate-ai-insights');
    Route::post('/observations/{observation}/generate-ai-suggestions', [SupervisorController::class, 'generateAiSuggestions'])->middleware('ai.rate.limit')->name('observations.generate-ai-suggestions');
    Route::delete('/observations/{observation}/clear-ai-insights', [SupervisorController::class, 'clearAiInsights'])->name('observations.clear-ai-insights');
    Route::post('/observations/{observation}/generate-ai-comparison', [SupervisorController::class, 'generateAiComparison'])->middleware('ai.rate.limit')->name('observations.generate-ai-comparison');
    Route::post('/observations/{observation}/generate-observation-suggestions', [SupervisorController::class, 'generateObservationSuggestions'])->middleware('ai.rate.limit')->name('observations.generate-observation-suggestions');

    // Post-Observation Report
    Route::get('/observations/{observation}/report', [SupervisorController::class, 'downloadReport'])->name('observations.report');

    // Feedback Management
    Route::get('/feedback', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'center'])->name('feedback.center');
    Route::prefix('observations/{observation}/feedback')->name('feedback.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'create'])->name('create');
        Route::post('/generate', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'generate'])->middleware('ai.rate.limit')->name('generate');
        Route::post('/generate-ai', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'generateAi'])->middleware('ai.rate.limit')->name('generate-ai');
        Route::get('/{feedback}/edit', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'edit'])->name('edit');
        Route::patch('/{feedback}', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'update'])->name('update');
        Route::post('/{feedback}/publish', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'publish'])->name('publish');
        Route::get('/{feedback}/export', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'export'])->name('export');
        Route::delete('/{feedback}', [\App\Http\Controllers\Supervisor\FeedbackController::class, 'destroy'])->name('destroy');
    });

    // Coaching Agreements
    Route::get('/coaching', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'index'])->name('coaching.index');
    Route::get('/observations/{observation}/coaching/create', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'create'])->name('coaching.create');
    Route::post('/coaching', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'store'])->name('coaching.store');
    Route::get('/coaching/{agreement}', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'show'])->name('coaching.show');
    Route::get('/coaching/{agreement}/edit', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'edit'])->name('coaching.edit');
    Route::patch('/coaching/{agreement}', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'update'])->name('coaching.update');
    Route::post('/coaching/{agreement}/sign', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'sign'])->name('coaching.sign');
    Route::get('/coaching/{agreement}/export', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'export'])->name('coaching.export');
    Route::delete('/coaching/{agreement}', [\App\Http\Controllers\Supervisor\CoachingAgreementController::class, 'destroy'])->name('coaching.destroy');
});

// School Head routes
Route::middleware(['auth', 'role:school_head'])->prefix('school-head')->name('school-head.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Profile\SchoolHeadProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Profile\SchoolHeadProfileController::class, 'update'])->name('profile.update');

    // Observations (My Performance)
    Route::get('/observations', [\App\Http\Controllers\SchoolHead\ObservationController::class, 'index'])->name('observations.index');
    Route::get('/observations/{observation}', [\App\Http\Controllers\SchoolHead\ObservationController::class, 'show'])->name('observations.show');
    Route::post('/observations/{observation}/confirm', [\App\Http\Controllers\SchoolHead\ObservationController::class, 'confirm'])->name('observations.confirm');
    Route::post('/observations/{observation}/reject', [\App\Http\Controllers\SchoolHead\ObservationController::class, 'reject'])->name('observations.reject');
    Route::post('/observations/{observation}/upload-plan', [\App\Http\Controllers\SchoolHead\ObservationController::class, 'uploadPlan'])->name('observations.upload-plan');

    // Teacher management
    Route::get('/teachers', [\App\Http\Controllers\SchoolHead\TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/{teacher}', [\App\Http\Controllers\SchoolHead\TeacherController::class, 'show'])->name('teachers.show');

    // Lesson Plans
    Route::get('/lesson-plans', [\App\Http\Controllers\SchoolHead\LessonPlanController::class, 'index'])->name('lesson-plans.index');
    Route::get('/lesson-plans/{plan}', [\App\Http\Controllers\SchoolHead\LessonPlanController::class, 'show'])->name('lesson-plans.show');

    // AI Feedback
    Route::get('/feedback', [\App\Http\Controllers\SchoolHead\FeedbackController::class, 'index'])->name('feedback.index');

    // Coaching
    Route::get('/coaching', [\App\Http\Controllers\SchoolHead\CoachingController::class, 'index'])->name('coaching.index');
    Route::get('/coaching/{agreement}', [\App\Http\Controllers\SchoolHead\CoachingController::class, 'show'])->name('coaching.show');

    // Analytics & Reports
    Route::get('/reports', [\App\Http\Controllers\SchoolHead\ReportController::class, 'index'])->name('reports.index');
});

// School-specific routes
Route::middleware(['school', 'require.school', 'auth'])->prefix('{school}')->name('school.')->group(function () {
    Route::get('/dashboard', function () {
        return view('school.dashboard');
    })->name('dashboard');
    
    // Add more school-specific routes here
});

require __DIR__.'/auth.php';

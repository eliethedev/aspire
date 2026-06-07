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
});

// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/observations', [\App\Http\Controllers\Teacher\ObservationController::class, 'index'])->name('observations.index');
    Route::get('/observations/{observation}', [\App\Http\Controllers\Teacher\ObservationController::class, 'show'])->name('observations.show');
    Route::post('/observations/{observation}/upload-lesson-plan', [\App\Http\Controllers\Teacher\ObservationController::class, 'uploadLessonPlan'])->name('observations.upload-lesson-plan');
});

// Supervisor routes
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/observations', [\App\Http\Controllers\CalendarController::class, 'getObservationsByDate'])->name('calendar.observations');
    
    Route::get('/teachers', [SupervisorController::class, 'teachers'])->name('teachers.index');
    Route::get('/observations', [SupervisorController::class, 'observations'])->name('observations.index');
    Route::get('/observations/create', [SupervisorController::class, 'createObservation'])->name('observations.create');
    Route::post('/observations', [SupervisorController::class, 'storeObservation'])->name('observations.store');
    Route::get('/observations/{observation}', [SupervisorController::class, 'showObservation'])->name('observations.show');
    
    // Stage-specific routes
    Route::get('/observations/{observation}/pre-observation-planning', [SupervisorController::class, 'preObservationPlanning'])->name('observations.preObservationPlanning');
    Route::post('/observations/{observation}/pre-observation-planning', [SupervisorController::class, 'storePreObservationPlanning'])->name('observations.storePreObservationPlanning');
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
});

// School-specific routes
Route::middleware(['school', 'require.school', 'auth'])->prefix('{school}')->name('school.')->group(function () {
    Route::get('/dashboard', function () {
        return view('school.dashboard');
    })->name('dashboard');
    
    // Add more school-specific routes here
});

require __DIR__.'/auth.php';

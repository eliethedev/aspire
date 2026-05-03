<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// School management routes (admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::apiResource('schools', SchoolController::class);
    
    Route::post('/schools/{school}/users', [SchoolController::class, 'addUser'])->name('schools.users.add');
    Route::delete('/schools/{school}/users/{user}', [SchoolController::class, 'removeUser'])->name('schools.users.remove');
    Route::get('/schools/{school}/users', [SchoolController::class, 'users'])->name('schools.users.index');
});

// School-specific routes
Route::middleware(['school', 'require.school', 'auth'])->prefix('{school}')->name('school.')->group(function () {
    Route::get('/dashboard', function () {
        return view('school.dashboard');
    })->name('dashboard');
    
    // Add more school-specific routes here
});

require __DIR__.'/auth.php';

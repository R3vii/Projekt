<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{id}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');

    // Switch Project
    Route::post('/projects/switch', [DashboardController::class, 'switchProject'])->name('projects.switch');
    Route::get('/projects/{project}/download', [App\Http\Controllers\DashboardController::class, 'downloadProject'])->name('projects.download');

    // Kanban Board
    Route::get('/', [TaskController::class, 'index'])->name('board.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/status', [TaskController::class, 'changeStatus'])->name('tasks.change-status');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/push-to-github', [TaskController::class, 'pushToGithub'])->name('tasks.push-github');

    // Comments
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Files
    Route::post('/tasks/{task}/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Leader + Admin routes
    Route::middleware('leader_or_admin')->group(function () {
        Route::post('/files/{file}/approve', [FileController::class, 'approve'])->name('files.approve');
        Route::post('/files/{file}/reject', [FileController::class, 'reject'])->name('files.reject');

        Route::prefix('admin')->name('admin.')->group(function () {
            // Tasks CRUD
            Route::resource('tasks', AdminTaskController::class)->except(['show']);
        });
    });

    // Admin only routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)->except(['show']);
    });
});

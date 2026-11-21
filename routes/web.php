<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NoteListController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('avatars/{filename}', function ($filename) {
    $path = storage_path('app/avatars/' . $filename);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
})->name('avatar.serve')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('tasks', [TaskListController::class, 'index'])->name('tasks.index');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('projects.notes.store');

    Route::patch('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('notes', [NoteListController::class, 'index'])->name('notes.index');
    Route::post('notes', [NoteListController::class, 'store'])->name('notes.store');
    Route::get('notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::patch('notes/{note}/pin', [NoteController::class, 'pin'])->name('notes.pin');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::delete('calendar/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('documents/{document}/preview-file', [DocumentController::class, 'previewFile'])->name('documents.preview-file');


    });

require __DIR__.'/settings.php';
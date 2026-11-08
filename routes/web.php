<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NoteListController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('tasks', [TaskListController::class, 'index'])->name('tasks.index');
    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('projects.notes.store');
    Route::patch('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('notes', [NoteListController::class, 'index'])->name('notes.index');
    Route::post('notes', [NoteListController::class, 'store'])->name('notes.store');
});

require __DIR__.'/settings.php';
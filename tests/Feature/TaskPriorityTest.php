<?php

use App\Models\Task;
use App\Support\TaskPriority;

it('marks an overdue task as overdue', function () {
    $task = Task::factory()->make(['due_at' => now()->subDay(), 'status' => 'todo']);
    expect(TaskPriority::level($task))->toBe('overdue');
});

it('marks a task without deadline but high importance as soon', function () {
    $task = Task::factory()->make(['due_at' => null, 'importance' => 3, 'status' => 'todo']);
    expect(TaskPriority::level($task))->toBe('soon');
});

it('gives a higher score to a more urgent task', function () {
    $urgent = Task::factory()->make(['due_at' => now()->addHour(), 'importance' => 3, 'status' => 'todo']);
    $relaxed = Task::factory()->make(['due_at' => now()->addWeek(), 'importance' => 1, 'status' => 'todo']);

    expect(TaskPriority::score($urgent))->toBeGreaterThan(TaskPriority::score($relaxed));
});
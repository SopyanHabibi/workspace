<?php

use App\Models\User;

it('forbids updating another user\'s task', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Project']);
    $task = $project->tasks()->create([
        'title' => 'Secret task',
        'importance' => 2,
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('tasks.update', $task), ['status' => 'done'])
        ->assertForbidden();
});

it('forbids deleting another user\'s task', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Project']);
    $task = $project->tasks()->create([
        'title' => 'Secret task',
        'importance' => 2,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();
});
<?php

use App\Models\User;

it('forbids updating another user\'s note', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Project']);
    $note = $project->notes()->create([
        'title' => 'Secret note',
        'type' => 'project',
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('notes.update', $note), ['title' => 'Hacked'])
        ->assertForbidden();
});

it('forbids deleting another user\'s note', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Project']);
    $note = $project->notes()->create([
        'title' => 'Secret note',
        'type' => 'project',
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('notes.destroy', $note))
        ->assertForbidden();
});
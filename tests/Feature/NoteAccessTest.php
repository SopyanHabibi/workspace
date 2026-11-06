<?php

use App\Models\User;

it('forbids deleting another user\'s note', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Rahasia']);
    $note = $project->notes()->create(['title' => 'Catatan rahasia']);

    $this->actingAs(User::factory()->create())
        ->delete(route('notes.destroy', $note))
        ->assertForbidden();
});
<?php

use App\Models\User;

it('forbids viewing another user\'s project', function () {
    $owner = User::factory()->create();
    $project = $owner->workspaces()->create(['name' => 'WS'])
        ->projects()->create(['name' => 'Rahasia']);

    $this->actingAs(User::factory()->create())
        ->get(route('projects.show', $project))
        ->assertForbidden();
});
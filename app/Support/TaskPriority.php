<?php

namespace App\Support;

use App\Models\Task;
use Carbon\Carbon;

class TaskPriority
{
    /**
     * Skor makin tinggi = makin mendesak. Dipakai untuk mengurutkan, bukan disimpan.
     */
    public static function score(Task $task): float
    {
        if (! $task->due_at) {
            return $task->importance; // tanpa deadline, cuma importance yang menentukan
        }

        $hoursLeft = Carbon::now()->diffInHours($task->due_at, false);

        // Makin dekat deadline (atau sudah lewat), makin tinggi skornya.
        $urgency = $hoursLeft <= 0 ? 100 : max(0, 100 - $hoursLeft);

        return $urgency + ($task->importance * 10);
    }
}
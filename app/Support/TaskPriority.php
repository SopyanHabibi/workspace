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
        if ($task->status === 'done') {
            return -1;
        }

        if (! $task->due_at) {
            return $task->importance;
        }

        $hoursLeft = Carbon::now()->diffInHours($task->due_at, false);

        // Makin dekat deadline (atau sudah lewat), makin tinggi skornya.
        $urgency = $hoursLeft <= 0 ? 100 : max(0, 100 - $hoursLeft);

        return $urgency + ($task->importance * 10);
    }

    /**
     * Kategori urgensi untuk ditampilkan sebagai badge di UI.
     * overdue > urgent > soon > normal
     */
    public static function level(Task $task): string
    {
        if ($task->status === 'done') {
            return 'done';
        }

        if (! $task->due_at) {
            return $task->importance >= 3 ? 'soon' : 'normal';
        }

        $hoursLeft = Carbon::now()->diffInHours($task->due_at, false);

        if ($hoursLeft <= 0) {
            return 'overdue';
        }

        if ($hoursLeft <= 24 || $task->importance >= 3) {
            return 'urgent';
        }

        if ($hoursLeft <= 72) {
            return 'soon';
        }

        return 'normal';
    }
}
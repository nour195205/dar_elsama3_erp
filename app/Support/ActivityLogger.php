<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function record(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $properties = []
    ): void {
        try {
            ActivityLog::query()->create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'properties' => $properties ?: null,
                'ip' => request()?->ip(),
            ]);
        } catch (\Throwable) {
            // avoid breaking primary flow if logging fails
        }
    }

    public static function forModel(string $action, string $description, ?Model $subject = null, array $properties = []): void
    {
        if ($subject) {
            self::record(
                $action,
                $description,
                $subject::class,
                (int) $subject->getKey(),
                $properties
            );

            return;
        }

        self::record($action, $description, null, null, $properties);
    }
}

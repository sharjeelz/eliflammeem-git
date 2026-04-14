<?php

use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Auth;

if (! function_exists('activity_async')) {
    /**
     * Log activity asynchronously using a queued job.
     *
     * @param  string  $description  Description of the activity
     * @param  \Illuminate\Database\Eloquent\Model|null  $performedOn  The model being acted on (optional)
     * @param  array|null  $properties  Extra context/properties
     * @param  string|null  $logName  Log name (optional)
     * @param  int|null  $userId  Causer ID (optional, defaults to current user)
     */
    function activity_async(
        string $description,
        $performedOn = null,
        ?array $properties = [],
        ?string $logName = null,
        ?int $userId = null
    ): void {
        $userId = $userId ?? Auth::user()?->id;

        LogActivityJob::dispatch(
            $description,
            $userId,
            $logName,
            $properties,
            $performedOn
        )->onQueue('activity-log');
    }
}

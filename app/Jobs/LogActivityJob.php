<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $description;

    public ?int $userId;

    public ?string $logName;

    public ?array $properties;

    public $performedOn;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $description,
        ?int $userId = null,
        ?string $logName = null,
        ?array $properties = [],
        $performedOn = null
    ) {
        $this->description = $description;
        $this->userId = $userId;
        $this->logName = $logName;
        $this->properties = $properties;
        $this->performedOn = $performedOn;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activity = activity($this->logName ?? 'default')
            ->causedBy($this->userId)
            ->withProperties($this->properties ?? []);

        // ✅ Only call performedOn() if a model is provided
        if ($this->performedOn) {
            $activity->performedOn($this->performedOn);
        }

        $activity->log($this->description);
    }
}

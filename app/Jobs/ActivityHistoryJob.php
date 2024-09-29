<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ActivityHistory;

class ActivityHistoryJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;



    /**
     * Create a new job instance.
     */
    public function __construct(public $data, public $platform, public $browser) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ActivityHistory::create([
            'user_id' => $this->data['user_id'],
            'model' => $this->data['model'],
            'action' => $this->data['action'],
            'data' => $this->data['data'],
            'platform' => $this->platform,
            'browser' => $this->browser,
        ]);
    }
}

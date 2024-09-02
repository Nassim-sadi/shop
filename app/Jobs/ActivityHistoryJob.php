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

    public  $data;
    public $result;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $result)
    {
        $this->data = $data;
        $this->result = $result;
    }

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
            'platform' => $this->result->os->family,
            'browser' => $this->result->originalUserAgent,
        ]);
    }
}

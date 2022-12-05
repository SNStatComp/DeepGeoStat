<?php

namespace App\Events;

use App\Models\LabelEvidence;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabelEvidenceCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $labelEvidence;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LabelEvidence $labelEvidence)
    {
        $this->labelEvidence = $labelEvidence;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Team.'.$this->labelEvidence->team_id);
    }
}

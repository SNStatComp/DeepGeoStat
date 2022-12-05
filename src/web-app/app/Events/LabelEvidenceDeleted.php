<?php

namespace App\Events;

use App\Models\LabelEvidence;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabelEvidenceDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $team;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LabelEvidence $labelEvidence)
    {
        $this->team = $labelEvidence->team;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Team.'.$this->team->id);
    }
}

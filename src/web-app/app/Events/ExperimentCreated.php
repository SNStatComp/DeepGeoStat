<?php

namespace App\Events;

use App\Models\Experiment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExperimentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $experiment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Experiment $experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Team.'.$this->experiment->team->id);
    }
}

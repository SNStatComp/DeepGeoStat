<?php

namespace App\Observers;

use App\Events\DataCreationSuccessful;
use App\Events\PredictionCreated;
use App\Events\PredictionDeleted;
use App\Models\Prediction;

class PredictionObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Prediction "created" event.
     *
     * @param  \App\Models\Prediction  $prediction
     * @return void
     */
    public function created(Prediction $prediction)
    {
        DataCreationSuccessful::dispatch($prediction->user, "Successfully created your predictions {$prediction->title}.");

        PredictionCreated::dispatch($prediction);
    }

    /**
     * Handle the Prediction "deleted" event.
     *
     * @param  \App\Models\Prediction  $prediction
     * @return void
     */
    public function deleted(Prediction $prediction)
    {
        PredictionDeleted::dispatch($prediction);
    }
}

<?php

namespace App\Observers;

use App\Models\Experiment;
use Illuminate\Support\Facades\Http;

class ExperimentObserver
{
    /**
     * Handle the Experiment "created" event.
     *
     * @param  \App\Models\Experiment  $experiment
     * @return void
     */
    public function created(Experiment $experiment)
    {
        // Http::post(config('services.api.url').'/experiments', $experiment->createExperimentData());
    }

    /**
     * Handle the Experiment "deleted" event.
     *
     * @param  \App\Models\Experiment  $experiment
     * @return void
     */
    public function deleted(Experiment $experiment)
    {
        Http::delete(config('services.api.url').'/experiments/'.$experiment->id);
    }
}

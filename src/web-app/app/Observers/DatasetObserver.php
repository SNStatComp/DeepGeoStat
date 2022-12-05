<?php

namespace App\Observers;

use App\Events\DataCreationSuccessful;
use App\Events\DatasetCreated;
use App\Events\DatasetDeleted;
use App\Models\Dataset;

class DatasetObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Dataset "created" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function created(Dataset $dataset)
    {
        DataCreationSuccessful::dispatch($dataset->user, "Successfully created your dataset {$dataset->title}.");

        DatasetCreated::dispatch($dataset);
    }

    /**
     * Handle the Dataset "deleted" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function deleted(Dataset $dataset)
    {
        DatasetDeleted::dispatch($dataset);
    }
}

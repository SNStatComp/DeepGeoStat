<?php

namespace App\Observers;

use App\Enums\LabelEvidenceTypeEnum;
use App\Events\DataCreationSuccessful;
use App\Events\LabelEvidenceCreated;
use App\Events\LabelEvidenceDeleted;
use App\Models\LabelEvidence;

class LabelEvidenceObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the LabelEvidence "created" event.
     *
     * @param  \App\Models\LabelEvidence  $labelEvidence
     * @return void
     */
    public function created(LabelEvidence $labelEvidence)
    {
        if ($labelEvidence->type === LabelEvidenceTypeEnum::Register) {
            DataCreationSuccessful::dispatch($labelEvidence->user, "Successfully created your label evidence {$labelEvidence->title}.");
        }

        LabelEvidenceCreated::dispatch($labelEvidence);
    }

    /**
     * Handle the LabelEvidence "deleted" event.
     *
     * @param  \App\Models\LabelEvidence  $labelEvidence
     * @return void
     */
    public function deleted(LabelEvidence $labelEvidence)
    {
        LabelEvidenceDeleted::dispatch($labelEvidence);
    }
}

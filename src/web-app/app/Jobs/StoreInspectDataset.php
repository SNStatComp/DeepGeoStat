<?php

namespace App\Jobs;

use App\Events\DataCreationFailed;
use App\Events\DataCreationSuccessful;
use App\Models\Dataset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreInspectDataset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $dataset;

    public $user;

    public $inspectData;

    public $labels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Dataset $dataset, User $user, $inspectData, $labels)
    {
        $this->dataset = $dataset;
        $this->user = $user;
        $this->inspectData = $inspectData;
        $this->labels = $labels;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inspectDataset = DB::transaction(function () {
            $inspectDataset = $this->dataset->inspects()->create([
                'created_at' => $this->inspectData['created_at'],
            ]);

            $labels = collect($this->labels)->map(function ($label) use ($inspectDataset) {
                return [
                    'inspect_dataset_id' => $inspectDataset->id,
                    'grid_id' => $label['grid_id'],
                    'label_class_id' => $label['label_class_id'],
                    'information' => json_encode([
                        'confidence' => [
                            'level' => $label['confidence'],
                            'reason' => $label['confidence_reason'],
                        ],
                    ]),
                ];
            })->toArray();

            foreach (array_chunk($labels, 5000) as $chunk) {
                $inspectDataset->labels()->insert($chunk);
            }

            return $inspectDataset;
        });

        DataCreationSuccessful::dispatch($this->user, "Successfully consolidated your dataset {$this->dataset->title}.");
    }

    public function failed(Throwable $exception)
    {
        DataCreationFailed::dispatch($this->user, 'Something went wrong trying to store your dataset consolidation, please try again later.');
    }
}

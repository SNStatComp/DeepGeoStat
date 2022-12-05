<?php

namespace App\Jobs;

use App\Events\DataCreationFailed;
use App\Models\Experiment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class StorePredictions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $predictionData;

    public $predictions;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($predictionData, $predictions)
    {
        $this->predictionData = $predictionData;
        $this->predictions = $predictions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $experiment = Experiment::findOrFail($this->predictionData['experiment_id']);
            $labelClasses = $experiment->team->labelClasses->pluck('id', 'title');
            $user = User::find($this->predictionData['user_id']);

            $prediction = $experiment->predictions()->create([
                'user_id' => $user->id,
                'title' => $this->predictionData['input']['title'],
                'description' => '',
            ]);

            $predictions = collect($this->predictionData['grids'])->map(function ($grid, $index) use ($prediction, $labelClasses) {
                return [
                    'prediction_id' => $prediction->id,
                    ...$grid,
                    'label_class_id' => $labelClasses[$this->predictions[$index]['label']],
                ];
            })->toArray();

            foreach (array_chunk($predictions, 5000) as $chunk) {
                $prediction->labels()->insert($chunk);
            }
        });
    }

    public function failed(Throwable $exception)
    {
        DataCreationFailed::dispatch($this->user, 'Something went wrong trying to store your predictions, please try again later.');
    }
}

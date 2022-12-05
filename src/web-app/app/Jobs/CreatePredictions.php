<?php

namespace App\Jobs;

use App\Actions\DeepGeoStat\GetDatasetGrids;
use App\Events\DataCreationFailed;
use App\Models\Experiment;
use App\Models\Mask;
use App\Models\SourceDataset;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CreatePredictions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    public $experiment;

    public $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Experiment $experiment, $input)
    {
        $this->user = $user;
        $this->experiment = $experiment;
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $predictionGrids = GetDatasetGrids::execute($this->experiment->team, $this->input);

        $predictionKey = (string) Str::orderedUuid();
        while (Cache::has("prediction.{$predictionKey}")) {
            $predictionKey = (string) Str::orderedUuid();
        }

        Cache::put("prediction.{$predictionKey}", [
            'id' => $predictionKey,
            'user_id' => $this->user->id,
            'experiment_id' => $this->experiment->id,
            'input' => $this->input,
            'grids' => $predictionGrids,
        ], now()->addWeek());

        $sourceDatasets = SourceDataset::with(['sourceGridCells', 'sourceGridCells.gridCell', 'sourceGridCells.dataset'])->findOrFail($this->input['datasets']);
        $sourceDatasetsGrids = $sourceDatasets->pluck('sourceGridCells')->flatten()->groupBy(function ($gridCell) {
            return $gridCell->id;
        });
        $masks = Mask::select(['id', 'options'])->whereIntegerInRaw('id', $predictionGrids->pluck('mask_id')->concat($predictionGrids->pluck('change_mask_id'))->unique())->get()->groupBy(function ($mask) {
            return $mask->id;
        });

        $httpResponse = Http::post(config('services.api.url').'/experiments/'.$this->experiment->id.'/predictions', [
            'id' => $predictionKey,
            'information' => [
                'experiment' => [
                    'id' => $this->experiment->id,
                    'type' => $this->experiment->team->detection_type->value,
                ],
                'label_classes' => $this->experiment->team->labelClasses->map(function ($labelClass) {
                    return $labelClass->title;
                }),
            ],
            'data' => $predictionGrids->map(function ($grid) use ($sourceDatasetsGrids, $masks) {
                $sourceGrid = $sourceDatasetsGrids[$grid['source_dataset_grid_cell_id']]->first();
                $sourceMask = (isset($grid['mask_id'])) ? $masks[$grid['mask_id']]->first() : null;
                $changeGrid = (isset($grid['change_source_dataset_grid_cell_id'])) ? $sourceDatasetsGrids[$grid['change_source_dataset_grid_cell_id']]->first() : null;
                $changeMask = (isset($grid['change_mask_id'])) ? $masks[$grid['change_mask_id']]->first() : null;

                return [
                    'grids' => array_filter([
                        array_filter([
                            'dataset' => [
                                'grid_cell_size' => $sourceGrid->gridCell->type->value,
                                'image_type' => $sourceGrid->dataset->image_type,
                                'year' => $sourceGrid->dataset->year,
                            ],
                            'grid_cell' => $sourceGrid->gridCell->title,
                            'mask_index' => (! is_null($sourceMask)) ? $sourceMask->options['index'] : null,
                        ]),
                        (isset($grid['change_source_dataset_grid_cell_id'])) ? array_filter([
                            'dataset' => [
                                'grid_cell_size' => $changeGrid->gridCell->type->value,
                                'image_type' => $changeGrid->dataset->image_type,
                                'year' => $changeGrid->dataset->year,
                            ],
                            'grid_cell' => $changeGrid->gridCell->title,
                            'mask_index' => (! is_null($changeMask)) ? $changeMask->options['index'] : null,
                        ]) : null,
                    ]),
                ];
            }),
        ]);

        if (! $httpResponse->successful()) {
            throw new Exception('Failed to send prediction data to API');
        }
    }

    public function failed(Throwable $exception)
    {
        DataCreationFailed::dispatch($this->user, 'Something went wrong trying to create your predictions, please try again later.');
    }
}

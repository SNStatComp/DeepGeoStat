<?php

namespace App\Jobs;

use App\Enums\LabelEvidenceTypeEnum;
use App\Events\DataCreationFailed;
use App\Models\Dataset;
use App\Models\Register;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateLabelEvidenceFromRegister implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60 * 5;

    public $user;

    public $team;

    public $input;

    public $csvPath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Team $team, $input, $csvPath = null)
    {
        $this->user = $user;
        $this->team = $team;
        $this->input = $input;
        $this->csvPath = $csvPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dataset = Dataset::findOrFail($this->input['dataset']);
        if ($this->csvPath) {
            foreach ($this->input['register_label_class_identifiers'] as $labelIdentifier) {
                $this->team->labelClasses()->where('id', $labelIdentifier['team_label_class'])->update([
                    'identifier' => $labelIdentifier['identifier'],
                ]);
            }

            // Label Evidence will be imported from CSV.
            if (($handle = fopen(Storage::path($this->csvPath), 'r')) !== false) {
                $header = array_map(fn ($value) => strtolower($value), fgetcsv($handle));

                // Check if CSV has correct columns for the dataset.
                $validCsv = false;
                if (! $dataset->grids()->first()->sourceMask()->exists()) {
                    $validCsv = in_array('grid', $header) && in_array('label', $header);
                } else {
                    $validCsv = in_array('grid', $header) && in_array('polygon_index', $header) && in_array('label', $header);
                }

                if (! $validCsv) {
                    throw new Exception('Failed to import your register: the given CSV file is not valid, are you sure it has the right header?');
                }

                $labels = [];
                $labelClasses = $this->team->labelClasses()->pluck('id', 'identifier');
                $grids = $dataset->grids()->with(['sourceGridCell.gridCell', 'sourceMask'])->get()->groupBy(function ($grid) {
                    return $grid->sourceGridCell->gridCell->title;
                });
                while (($row = fgetcsv($handle)) !== false) {
                    $row = array_combine($header, $row);

                    if (! $grids->has($row['grid'])) {
                        continue;
                    }
                    if (! $labelClasses->has($row['label'])) {
                        continue;
                    }

                    // Get Grid Id.
                    $rowGrids = $grids[$row['grid']];
                    if (! $dataset->grids()->first()->sourceMask()->exists()) {
                        $grid = $rowGrids->first();
                    } else {
                        $grid = $rowGrids->filter(function ($datasetGrid) use ($row) {
                            return $datasetGrid->sourceMask->options['index'] == $row['polygon_index'];
                        })->first();
                    }

                    // Check if Grid was found.
                    if (! isset($grid) || is_null($grid)) {
                        continue;
                    }

                    // Remove Grid from possible Grids to avoid duplicates.
                    $grids[$grid->sourceGridCell->gridCell->title] = $grids[$grid->sourceGridCell->gridCell->title]->filter(function ($datasetGrid) use ($grid) {
                        return $datasetGrid->id !== $grid->id;
                    })->values();

                    // Add Evidence to Label array.
                    $labels[] = [
                        'grid_id' => $grid->id,
                        'label_class_id' => $labelClasses[$row['label']],
                    ];
                }
            }

            // Transform Label array into collection to be used later.
            $labels = collect($labels);

            // Delete uploaded CSV file by the user.
            Storage::delete($this->csvPath);
        } else {
            // Label Evidence will be imported from register from database.
            $register = Register::findOrFail($this->input['register']);
            $labelClasses = [];
            collect($this->input['register_label_classes'])->each(function ($labelClass) use (&$labelClasses) {
                $labelClasses[$labelClass['register_label_class']] = $labelClass['team_label_class'];
            });

            $registerLabels = $register->labels()->orderBy('grid_cell_id')->get()->groupBy('grid_cell_id');
            $labels = $dataset->grids()->with(['sourceGridCell.gridCell'])->get()->map(function ($grid) use ($register, $registerLabels, $labelClasses) {
                if (! $registerLabels->has($grid->sourceGridCell->grid_cell_id)) {
                    return [];
                }

                // Dataset is masked
                if ($grid->mask_id) {
                    $label = $registerLabels[$grid->sourceGridCell->grid_cell_id]->where('mask_id', $grid->mask_id)->first();
                // Dataset is not masked
                } else {
                    $evidence = $registerLabels[$grid->sourceGridCell->grid_cell_id];
                    if ($evidence->count() > 1) {
                        $weightLabelClass = $register->weight_label_class_id;
                        if ($evidence->contains('label_class_id', $weightLabelClass)) {
                            $label = $evidence->firstWhere('label_class_id', $weightLabelClass);
                        } else {
                            $label = $evidence->firstWhere('label_class_id', $evidence->mode('label_class_id')[0]);
                        }
                    } elseif ($evidence->count() === 1) {
                        $label = $evidence[0];
                    }
                }

                if (! isset($label) || is_null($label)) {
                    return [];
                }

                return [
                    'grid_id' => $grid->id,
                    'label_class_id' => $labelClasses[$label->label_class_id],
                ];
            });

            // Get rid of empty arrays.
            $labels = $labels->filter();
        }

        $labelEvidence = DB::transaction(function () use ($dataset, &$labels) {
            $labelEvidence = $this->team->labelEvidence()->create([
                'user_id' => $this->user->id,
                'dataset_id' => $dataset->id,
                'title' => $this->input['title'],
                'description' => $this->input['description'],
                'type' => LabelEvidenceTypeEnum::Register,
            ]);

            $labels = $labels->map(function ($label) use ($labelEvidence) {
                return [
                    'user_id' => $this->user->id,
                    'label_evidence_id' => $labelEvidence->id,
                    ...$label,
                ];
            })->toArray();

            foreach (array_chunk($labels, 10000) as $chunk) {
                $labelEvidence->labels()->insert($chunk);
            }

            $labelEvidence->touch();

            return $labelEvidence;
        });
    }

    public function failed(Throwable $exception)
    {
        if ($this->csvPath) {
            Storage::delete($this->csvPath);
        }

        DataCreationFailed::dispatch($this->user, 'Something went wrong trying to create your label evidence, please try again later.');
    }
}

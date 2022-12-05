<?php

namespace App\Jobs;

use App\Actions\DeepGeoStat\GetDatasetGrids;
use App\Events\DataCreationFailed;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateDataset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60 * 5;

    public $user;

    public $team;

    public $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Team $team, $input)
    {
        $this->user = $user;
        $this->team = $team;
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $grids = GetDatasetGrids::execute($this->team, $this->input);

        DB::transaction(function () use ($grids) {
            $dataset = $this->team->datasets()->create([
                'user_id' => $this->user->id,
                'title' => $this->input['title'],
                'description' => $this->input['description'],
            ]);

            $grids = $grids->map(function ($grid) use ($dataset) {
                return [
                    'dataset_id' => $dataset->id,
                    ...$grid,
                ];
            })->toArray();

            foreach (array_chunk($grids, 5000) as $chunk) {
                $dataset->grids()->insert($chunk);
            }
        });
    }

    public function failed(Throwable $exception)
    {
        DataCreationFailed::dispatch($this->user, 'Something went wrong trying to create your dataset, please try again later.');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\InspectDataset;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanInspectDatasets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dgs:inspect:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the Inspect datasets that are older than 24 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Delete Inspect datasets that are older than 24 hours.
        $deletedRows = InspectDataset::where('created_at', '<=', Carbon::now()->subDay())->delete();

        if ($deletedRows !== 0) {
            $this->info('Successfully removed '.$deletedRows.' Inspect datasets.');
        } else {
            $this->info('Could not remove any Inspect datasets, there are no Inspect datasets older than 24 hours.');
        }
    }
}

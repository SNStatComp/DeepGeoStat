<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SurfaceUsageFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Extracting Surface Usage filter CSVs.');

        $temp_dir = './database/data/temp';
        $zip = new \ZipArchive();
        if ($zip->open('./database/data/surface-usage-filters.zip') === true) {
            // Create temp directory if it does not exist yet.
            @mkdir($temp_dir);

            // Extract file in zip to the temp directory.
            $zip->extractTo($temp_dir);
        }
        $zip->close();

        $this->command->info('Successfully extracted Surface Usage filter CSVs.');

        passthru("php artisan dgs:import:surface-usage-filter {$temp_dir}/surface_usage_filters.csv {$temp_dir}/surface_usage_classes.csv");

        exec("rm -rf {$temp_dir}");

        $this->command->info('Successfully imported Surface Usage filters.');
    }
}

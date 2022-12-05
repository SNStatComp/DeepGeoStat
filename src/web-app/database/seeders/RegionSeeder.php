<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('You can ignore the warning about progress being disabled for this seed.');

        GeoDataSeeder::importGeoDataIntoDB(
            $this->command,
            'region_geometries',
            'regions',
            'geometry, name, code, region_type, year, source_layer',
            '-where "region_type=\'provincie\' or region_type=\'gemeente\' or region_type=\'buurt\' or region_type=\'wijk\'"',
        );
    }
}

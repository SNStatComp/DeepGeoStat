<?php

namespace Database\Seeders;

use App\Enums\GridCellTypeEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GridCellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'string');

        // $this->setDefaultType(GridCellTypeEnum::Grid100x100);
        // GeoDataSeeder::importGeoDataIntoDB(
        //     $this->command,
        //     'grid_100m_selected',
        //     'grid_cells',
        //     'geometry, name'
        // );

        $this->setDefaultType(GridCellTypeEnum::Grid500x500);
        GeoDataSeeder::importGeoDataIntoDB(
            $this->command,
            'grid_500m_selected',
            'grid_cells',
            'geometry, title'
        );

        $this->setDefaultType();
    }

    private function setDefaultType(GridCellTypeEnum $type = null)
    {
        if ($type === null) {
            Schema::table('grid_cells', function (Blueprint $table) {
                $table->string('type')->default(null)->nullable(false)->change();
            });
        } else {
            Schema::table('grid_cells', function (Blueprint $table) use ($type) {
                $table->string('type')->default($type->value)->change();
            });
        }
    }
}

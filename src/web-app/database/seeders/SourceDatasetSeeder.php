<?php

namespace Database\Seeders;

use App\Enums\DatasetImageTypeEnum;
use App\Enums\GridCellTypeEnum;
use App\Models\GridCell;
use App\Models\SourceDataset;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SourceDatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'string');

        // 100x100m datasets
        // $this->importDatasetsRGB(GridCellTypeEnum::Grid100x100, '100x100m');
        // $this->importDatasetsIR(GridCellTypeEnum::Grid100x100, '100x100m');

        // 500x500m datasets
        $this->importDatasetsRGB(GridCellTypeEnum::Grid500x500, '500x500m');
        // $this->importDatasetsIR(GridCellTypeEnum::Grid500x500, '500x500m');

        $this->setDefaultType();
    }

    private function createDataset($gridCellType, $title, $description, $year, $url, $layer, $crs = 'EPSG:28992')
    {
        $this->command->info("Creating dataset {$title} in database.");

        $dataset = SourceDataset::create([
            'title' => $title,
            'description' => $description,
            'year' => $year,
            'url' => $url,
            'layer' => $layer,
            'crs' => $crs,
        ]);

        $this->command->info("Saving linked grid cells of dataset {$title} to database.");

        $this->command->getOutput()->progressStart(GridCell::where('type', $gridCellType->value)->count());
        GridCell::where('type', $gridCellType->value)->chunkById(25000, function ($gridCells) use ($dataset) {
            $dataset->gridCells()->attach($gridCells);
            $this->command->getOutput()->progressAdvance($gridCells->count());
        });

        $this->command->getOutput()->progressFinish();
    }

    private function importDatasetsRGB($gridCellType, $gridCellTypeName)
    {
        $this->setDefaultType(DatasetImageTypeEnum::RGB);

        $datasets = [
            [
                'title' => "2015 ${gridCellTypeName} RGB",
                'description' => '',
                'year' => 2015,
            ],
            // [
            //     'title' => "2016 ${gridCellTypeName} RGB",
            //     'description' => '',
            //     'year' => 2016,
            // ],
            [
                'title' => "2017 ${gridCellTypeName} RGB",
                'description' => '',
                'year' => 2017,
            ],
            // [
            //     'title' => "2018 ${gridCellTypeName} RGB",
            //     'description' => '',
            //     'year' => 2018,
            // ],
            // [
            //     'title' => "2019 ${gridCellTypeName} RGB",
            //     'description' => '',
            //     'year' => 2019,
            // ],
            [
                'title' => "2020 ${gridCellTypeName} RGB",
                'description' => '',
                'year' => 2020,
            ],
        ];

        foreach ($datasets as $dataset) {
            $this->createDataset(
                $gridCellType,
                $dataset['title'],
                $dataset['description'],
                $dataset['year'],
                'https://service.pdok.nl/hwh/luchtfotorgb/wms/v1_0?',
                "{$dataset['year']}_ortho25",
            );
        }
    }

    private function importDatasetsIR($gridCellType, $gridCellTypeName)
    {
        $this->setDefaultType(DatasetImageTypeEnum::IR);

        $datasets = [
            [
                'title' => "2015 ${gridCellTypeName} IR",
                'description' => '',
                'year' => 2015,
            ],
            // [
            //     'title' => "2016 ${gridCellTypeName} IR",
            //     'description' => '',
            //     'year' => 2016,
            // ],
            [
                'title' => "2017 ${gridCellTypeName} IR",
                'description' => '',
                'year' => 2017,
            ],
            // [
            //     'title' => "2018 ${gridCellTypeName} IR",
            //     'description' => '',
            //     'year' => 2018,
            // ],
            // [
            //     'title' => "${gridCellTypeName} IR 2019",
            //     'description' => '',
            //     'year' => 2019,
            // ],
            [
                'title' => "2020 ${gridCellTypeName} IR",
                'description' => '',
                'year' => 2020,
            ],
        ];

        foreach ($datasets as $dataset) {
            $this->createDataset(
                $gridCellType,
                $dataset['title'],
                $dataset['description'],
                $dataset['year'],
                'https://service.pdok.nl/hwh/luchtfotocir/wms/v1_0?',
                "{$dataset['year']}_ortho25IR",
            );
        }
    }

    private function setDefaultType(DatasetImageTypeEnum $type = null)
    {
        if ($type === null) {
            Schema::table('source_datasets', function (Blueprint $table) {
                $table->string('image_type')->default(null)->nullable(true)->change();
            });
        } else {
            Schema::table('source_datasets', function (Blueprint $table) use ($type) {
                $table->string('image_type')->default($type->value)->change();
            });
        }
    }
}

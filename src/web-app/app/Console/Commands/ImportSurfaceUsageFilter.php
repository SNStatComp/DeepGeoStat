<?php

namespace App\Console\Commands;

use App\Models\GridCell;
use App\Models\Mask;
use App\Models\Register;
use App\Models\SurfaceUsageFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class ImportSurfaceUsageFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dgs:import:surface-usage-filter
                            {csv : Path to the CSV file containing Surface Usage filters to be imported}
                            {classes_csv? :  Path to the CSV file containing the Surface Usage classes' titles (optional) }";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a Surface Usage filter from a csv file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $csv = $this->argument('csv');

        $file = new SplFileObject($csv, 'r');
        $file->seek(PHP_INT_MAX);
        $rows = $file->key() - 1;

        if (($handle = fopen($csv, 'r')) !== false) {
            DB::transaction(function () use ($handle, $rows) {
                $this->info('Preparing to load Surface Usage filter into database...');
                $header = fgetcsv($handle);

                $grids = GridCell::select(['id', 'title'])->pluck('id', 'title');
                $masks = Mask::select(['id', 'options->index as index'])->pluck('id', 'index');

                $this->info('Collecting data out of CSV file to insert into database.');

                $surfaceUsageFilters = [];
                $registers = [];

                $csvProgress = $this->output->createProgressBar($rows);
                $csvProgress->start();
                while (($row = fgetcsv($handle)) !== false) {
                    $row = array_combine($header, $row);

                    $grid = $row['grid'];
                    if (! $grids->has($grid)) {
                        continue;
                    }

                    // Mask does not exist in the database yet, create it.
                    if (! $masks->has($row['polygon_index'])) {
                        $mask = Mask::create([
                            'polygon' => json_decode($row['polygon']),
                            'geometry' => $row['wkt'],
                            'options' => [
                                'index' => intval($row['polygon_index']),
                                'size' => intval($row['polygon_size']),
                            ],
                        ]);

                        // Update the masks variable.
                        $masks[$mask->options['index']] = $mask->id;
                    }

                    //
                    // Create Surface Usage filters.
                    //

                    // Add Surface Usage filter for year 1.
                    $surfaceUsageFilters[$row['year_1_class'].'-'.$row['year_1']][] = [
                        'grid_cell_id' => $grids[$grid],
                        'mask_id' => $masks[$row['polygon_index']],
                    ];

                    // Add Surface Usage filter for year 2.
                    $surfaceUsageFilters[$row['year_2_class'].'-'.$row['year_2']][] = [
                        'grid_cell_id' => $grids[$grid],
                        'mask_id' => $masks[$row['polygon_index']],
                    ];

                    //
                    // Create Registers.
                    //

                    // Add Register for year 1.
                    $registers[$row['year_1_class'].'-'.$row['year_1']][] = [
                        'grid_cell_id' => $grids[$grid],
                        'mask_id' => $masks[$row['polygon_index']],
                        'label' => $row['year_1_class'],
                    ];

                    // Add Register for year 2.
                    $registers[$row['year_2_class'].'-'.$row['year_2']][] = [
                        'grid_cell_id' => $grids[$grid],
                        'mask_id' => $masks[$row['polygon_index']],
                        'label' => $row['year_2_class'],
                    ];

                    // Add Register for Change detection.
                    $registers[$row['year_1_class'].'-'.$row['year_1'].'|'.$row['year_1_class'].'-'.$row['year_2']][] = [
                        'grid_cell_id' => $grids[$grid],
                        'mask_id' => $masks[$row['polygon_index']],
                        'label' => ($row['year_1_class'] !== $row['year_2_class']) ? 'Change' : 'Unchanged',
                    ];

                    $csvProgress->advance();
                }
                $csvProgress->finish();

                $uniqueClasses = [];
                foreach (array_keys($surfaceUsageFilters) as $class) {
                    $class = explode('-', $class)[0];

                    if (! in_array($class, $uniqueClasses)) {
                        $uniqueClasses[] = $class;
                        sort($uniqueClasses);
                    }
                }

                $csvClasses = $this->argument('classes_csv');
                if ($csvClasses && ($handleClassCSV = fopen($csvClasses, 'r')) !== false) {
                    $headerClasses = fgetcsv($handleClassCSV);
                    $convertClassToTitle = [];
                    while (($row = fgetcsv($handleClassCSV)) !== false) {
                        $row = array_combine($headerClasses, $row);

                        $convertClassToTitle[$row['code']] = $row['title'];
                    }
                } else {
                    $this->info(PHP_EOL.'You have not specified the names of the different classes in your CSV file, answer the following questions to give a title to each class.');

                    $convertClassToTitle = [];
                    foreach ($uniqueClasses as $class) {
                        $convertClassToTitle[$class] = $this->ask('Title for class '.$class);
                    }
                }

                $this->info(PHP_EOL.'Inserting data into database.');

                $this->info('Inserting Surface Usage filters');
                $surfaceUsageProgress = $this->output->createProgressBar(count($surfaceUsageFilters));
                $surfaceUsageProgress->start();
                collect($surfaceUsageFilters)->each(function ($grids, $key) use ($convertClassToTitle, $surfaceUsageProgress) {
                    $key = explode('-', $key);
                    $class = $key[0];
                    $year = $key[1];

                    $surfaceUsageFilter = SurfaceUsageFilter::create([
                        'title' => $convertClassToTitle[$class].' '.$year,
                        'code' => $class,
                        'year' => $year,
                    ]);

                    $grids = collect($grids)->map(function ($grid) use ($surfaceUsageFilter) {
                        return [
                            'surface_usage_filter_id' => $surfaceUsageFilter->id,
                            ...$grid,
                        ];
                    })->toArray();

                    foreach (array_chunk($grids, 5000) as $chunk) {
                        $surfaceUsageFilter->grids()->insert($chunk);
                    }

                    $surfaceUsageProgress->advance();
                });
                $surfaceUsageProgress->finish();

                $this->info(PHP_EOL.'Inserting Registers linked to Surface Usage filters.');
                $registerProgress = $this->output->createProgressBar(count($registers));
                $registerProgress->start();
                collect($registers)->each(function ($labels, $key) use ($convertClassToTitle, $registerProgress) {
                    // Create Register.
                    $register = new Register();

                    $sourceData = explode('-', explode('|', $key)[0]);
                    $source = SurfaceUsageFilter::where('code', $sourceData[0])->where('year', $sourceData[1])->first();
                    $register->source()->associate($source);

                    $register->title = "{$convertClassToTitle[$sourceData[0]]} {$sourceData[1]}";

                    if (count(explode('|', $key)) > 1) {
                        $changeSourceData = explode('-', explode('|', $key)[1]);
                        $changeSource = SurfaceUsageFilter::where('code', $changeSourceData[0])->where('year', $changeSourceData[1])->first();
                        $register->changeSource()->associate($changeSource);

                        $register->title = "{$convertClassToTitle[$sourceData[0]]} {$sourceData[1]}-{$changeSourceData[1]}";
                    }

                    $register->save();

                    // Save Label Classes.
                    $labels = collect($labels);
                    $labelClasses = [];
                    $labels->unique('label')->pluck('label')->each(function ($label) use (&$labelClasses, $convertClassToTitle) {
                        if (is_numeric($label)) {
                            $labelClasses[$label] = $convertClassToTitle[$label];
                        } else {
                            $labelClasses[$label] = $label;
                        }
                    });

                    $dbLabelClasses = $register->labelClasses()->createMany(collect($labelClasses)->map(function ($labelClass) {
                        return [
                            'title' => $labelClass,
                        ];
                    }));

                    $labelClasses = collect($labelClasses)->map(function ($labelClass) use ($dbLabelClasses) {
                        return $dbLabelClasses->firstWhere('title', $labelClass)->id;
                    });

                    if ($labelClasses->count() === 1) {
                        $register->weight_label_class_id = $dbLabelClasses->first()->id;
                    } elseif ($register->changeSource()->exists()) {
                        $register->weight_label_class_id = $dbLabelClasses->firstWhere('title', 'Change')->id;
                    }

                    // Create Labels.
                    $labels = $labels->map(function ($label) use ($register, $labelClasses) {
                        return [
                            'register_id' => $register->id,
                            'grid_cell_id' => $label['grid_cell_id'],
                            'mask_id' => $label['mask_id'],
                            'label_class_id' => $labelClasses[$label['label']],
                        ];
                    })->toArray();

                    foreach (array_chunk($labels, 5000) as $chunk) {
                        $register->labels()->insert($chunk);
                    }

                    $register->save();

                    $registerProgress->advance();
                });
                $registerProgress->finish();

                $this->info(PHP_EOL.'Successfully imported the Surface Usage filter CSV.');
            });
        }
    }
}

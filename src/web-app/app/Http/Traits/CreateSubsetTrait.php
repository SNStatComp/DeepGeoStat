<?php

namespace App\Http\Traits;

use App\Enums\SubsetTypeEnum;
use App\Models\Dataset;
use App\Models\Region;
use Illuminate\Http\Request;

trait CreateSubsetTrait
{
    public function createSubset(SubsetTypeEnum $type, Dataset $dataset, Request $request)
    {
        if ($type === SubsetTypeEnum::RandomSample) {
            // Get the amount of Grid Cells in the selected Dataset.
            $gridCellsCount = $dataset->gridCells()->count();

            // Check if provided Sample Size is a number or percentage.
            // If it is a percentage convert the percentage into a number.
            $number = $request->sample_size;
            if (! is_numeric($number)) {
                $number = round($gridCellsCount * (round(str_replace('%', '', $number) / 1) / 100));
            }

            // Limit the number to the amount of Grid Cells.
            if ($number > $gridCellsCount) {
                $number = $gridCellsCount;
            }

            // Get random Grid Cell IDs.
            return $dataset->gridCells()->inRandomOrder()->take($number)->get();
        } elseif ($type === SubsetTypeEnum::Regions) {
            // Get the selected regions.
            $regions = Region::findOrFail($request->regions);

            // Get the selected regions with the selected dataset year.
            $regions = $regions->map(function ($region) use ($dataset) {
                return Region::where('year', $dataset->year)->where([
                    'name' => $region->name,
                    'region_type' => $region->region_type,
                ])->first() ?: $region;
            });

            // Get Grid Cells that are within the selected regions.
            $gridCells = [];
            $regions->each(function ($region) use ($dataset, &$gridCells) {
                $gridCells[] = $dataset->gridCells()->whereRaw('ST_Intersects(grid_cells.geometry, ?)', $region->geometry)->get();
            });

            // Turn the Grid Cell array into a single array with no duplicates.
            return $gridCells[0];
        } elseif ($type === SubsetTypeEnum::File) {
            // Get the Grid Cell names specified in the document.
            $gridCellsNames = [];
            if (($handle = fopen($request->file->getRealPath(), 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    $gridCellsNames[] = $data[0];
                }
            }

            // Search for the Grid Cell names in the available Grid Cells for the selected dataset.
            return $dataset->gridCells()->whereIn('name', $gridCellsNames)->get();
        }

        return null;
    }
}

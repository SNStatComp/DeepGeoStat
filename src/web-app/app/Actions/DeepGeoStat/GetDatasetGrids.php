<?php

namespace App\Actions\DeepGeoStat;

use App\Enums\TeamDetectionTypeEnum;
use App\Models\Dataset;
use App\Models\GridCell;
use App\Models\Region;
use App\Models\SourceDataset;
use App\Models\SurfaceUsageFilter;
use App\Models\Team;

class GetDatasetGrids
{
    public static function execute(Team $team, $input)
    {
        $filtersSelected = ((isset($input['surface_usage_filters']) && count($input['surface_usage_filters']) !== 0) || (isset($input['region_filters']) && count($input['region_filters']) !== 0));
        $sourceDatasets = SourceDataset::select(['id'])->with(['sourceGridCells' => function ($query) {
            $query->select(['source_dataset_grid_cell.id', 'source_dataset_grid_cell.grid_cell_id'])->orderBy('grid_cell_id', 'asc');
        }])->orderBy('year', 'asc')->findOrFail($input['datasets']);

        if ($filtersSelected) {
            // Filter Dataset if selected.
            if (isset($input['surface_usage_filters']) && count($input['surface_usage_filters']) > 0) {
                // Apply Surface usage filters.
                if (isset($input['surface_usage_filter_mask']) && $input['surface_usage_filter_mask']) {
                    // Apply Masks from Surface usage filters.
                    $datasetGrids = SurfaceUsageFilter::with(['grids.mask'])
                                    ->findOrFail($input['surface_usage_filters'])
                                    ->pluck('grids')
                                    ->flatten()
                                    ->filter(function ($grid) use ($input) {
                                        return $grid->mask->options['size'] >= ((500 * 500) * (floatval($input['surface_usage_filter_mask_size']) / 100.00));
                                    })
                                    ->map(function ($grid) {
                                        return [
                                            'grid_cell_id' => $grid->grid_cell_id,
                                            'mask_id' => $grid->mask_id,
                                        ];
                                    });
                } else {
                    $datasetGrids = SurfaceUsageFilter::with(['grids' => function ($query) {
                        $query->select('*')->distinct('grid_cell_id');
                    }])->findOrFail($input['surface_usage_filters'])->pluck('grids')->flatten()->map(function ($grid) {
                        return [
                            'grid_cell_id' => $grid->grid_cell_id,
                        ];
                    });
                }
            }

            // Region filters.
            if (isset($input['region_filters']) && count($input['region_filters']) > 0) {
                $regions = Region::findOrFail($input['region_filters']);

                $regionGridsQuery = GridCell::select('id')->where(function ($query) use ($regions) {
                    $regions->each(function ($region, $i) use ($query) {
                        $queryString = 'ST_Intersects(grid_cells.geometry, ?)';
                        if ($i === 0) {
                            $query->whereRaw($queryString, $region->geometry);
                        } else {
                            $query->orWhereRaw($queryString, $region->geometry);
                        }
                    });
                });

                if (isset($datasetGrids)) {
                    // Subset has been created of the selected sources.
                    // Check within this subset which are in the selected region(s).
                    $regionGrids = $regionGridsQuery->whereIntegerInRaw('id', $datasetGrids->pluck('grid_cell_id'))->pluck('id');

                    $datasetGrids = $datasetGrids->whereIn('grid_cell_id', $regionGrids)->values();
                } else {
                    // Full source datasets is being used get all grids within selected region(s).
                    $datasetGrids = $regionGridsQuery->pluck('id')->map(function ($gridId) {
                        return [
                            'grid_cell_id' => $gridId,
                        ];
                    });
                }
            }
        } else {
            $datasetGrids = $sourceDatasets->pluck('sourceGridCells')->flatten()->pluck('grid_cell_id')->unique()->map(function ($gridId) {
                return [
                    'grid_cell_id' => $gridId,
                ];
            });
        }

        // Format Dataset Grids to insert into the database.
        if ($team->detection_type === TeamDetectionTypeEnum::Classification) {
            $grids = $sourceDatasets->map(function ($sourceDataset) use ($datasetGrids) {
                $sourceGrids = $sourceDataset->gridCells->whereIn('grid_cell_id', $datasetGrids->pluck('grid_cell_id'))->pluck('id', 'grid_cell_id');

                return $datasetGrids->map(function ($grid) use ($sourceGrids) {
                    return [
                        'source_dataset_grid_cell_id' => $sourceGrids[$grid['grid_cell_id']],
                        'mask_id' => (array_key_exists('mask_id', $grid)) ? $grid['mask_id'] : null,
                    ];
                });
            })->flatten(1);
        } else {
            $sourceGridsFrom = $sourceDatasets[0]->gridCells->whereIn('grid_cell_id', $datasetGrids->pluck('grid_cell_id'))->pluck('id', 'grid_cell_id');
            $sourceGridsTo = $sourceDatasets[1]->gridCells->whereIn('grid_cell_id', $datasetGrids->pluck('grid_cell_id'))->pluck('id', 'grid_cell_id');

            $grids = $datasetGrids->map(function ($grid) use ($sourceGridsFrom, $sourceGridsTo) {
                return [
                    'source_dataset_grid_cell_id' => $sourceGridsFrom[$grid['grid_cell_id']],
                    'change_source_dataset_grid_cell_id' => $sourceGridsTo[$grid['grid_cell_id']],
                    'mask_id' => (array_key_exists('mask_id', $grid)) ? $grid['mask_id'] : null,
                    'change_mask_id' => (array_key_exists('mask_id', $grid)) ? $grid['mask_id'] : null,
                ];
            });
        }

        // Sort by Grid cell id.
        $grids = $grids->sortBy('source_dataset_grid_cell_id')->values();

        return $grids;
    }
}

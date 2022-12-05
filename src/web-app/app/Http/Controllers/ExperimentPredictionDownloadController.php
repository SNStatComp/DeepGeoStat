<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\Prediction;
use Illuminate\Support\Facades\DB;

class ExperimentPredictionDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Experiment $experiment, Prediction $prediction)
    {
        $callback = function () use ($prediction) {
            $columns = ['wkt', 'label'];

            $file = fopen('php://output', 'w');

            // Put column names to top of CSV file.
            fputcsv($file, $columns);

            // Put the Labels in the CSV file.
            $prediction->labels()->with(['sourceGridCell.gridCell' => function ($query) {
                $query->addSelect(DB::raw('ST_AsText(geometry) as geometry'));
            }, 'sourceMask' => function ($query) {
                $query->addSelect('id', DB::raw('ST_AsText(geometry) as geometry'));
            }, 'labelClass'])->get()->each(function ($label) use ($file) {
                fputcsv($file, [
                    ($label->sourceMask->exists()) ? $label->sourceMask->geometry : $label->sourceGridCell->gridCell->geometry,
                    $label->labelClass->title,
                ]);
            });

            fclose($file);
        };

        return response()->streamDownload($callback, $prediction->title.'.csv', [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$prediction->title.'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}

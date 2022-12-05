<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use Illuminate\Support\Facades\Http;

class ExperimentDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Experiment $experiment)
    {
        return response()->streamDownload(function () use ($experiment) {
            echo Http::get(config('services.api.url').'/experiments/'.$experiment->id.'/experiment');
        }, 'Experiment #'.$experiment->id.'.py');
    }
}

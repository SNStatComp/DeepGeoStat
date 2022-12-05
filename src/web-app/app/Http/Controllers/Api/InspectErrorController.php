<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreationFailed;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InspectErrorController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $inspectKey)
    {
        if (! Str::isUuid($inspectKey) || ! Cache::has("inspect.{$inspectKey}")) {
            abort(404);
        }

        $inspectData = Cache::get("inspect.{$inspectKey}");
        if ($user = User::find($inspectData['user_id'])) {
            DataCreationFailed::dispatch($user, 'Something went wrong trying to consolidate your dataset, please try again later.');
        }

        Cache::forget("inspect.{$inspectKey}");

        return response('Successfully informed user of fail.', 200);
    }
}

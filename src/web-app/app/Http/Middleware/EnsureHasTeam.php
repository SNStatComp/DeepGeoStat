<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->allTeams()->isEmpty()) {
            return redirect()->route('teams.index')->with('flash', [
                'type' => 'error',
                'message' => 'You need to be a part of a team before you can use other features.',
            ]);
        }

        $this->ensureUserHasCurrentTeamSet();

        return $next($request);
    }

    private function ensureUserHasCurrentTeamSet()
    {
        // If the user currently has no current team set, set the current team to their first team.
        if (is_null(auth()->user()->current_team_id)) {
            auth()->user()->switchTeam(auth()->user()->allTeams()->first());
        }
    }
}

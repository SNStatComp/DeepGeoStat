<?php

namespace App\Actions\Jetstream;

use App\Enums\TeamDetectionTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Events\AddingTeam;
use Laravel\Jetstream\Jetstream;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return mixed
     */
    public function create($user, array $input)
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'label_classes' => ['required', 'array', 'min:1'],
            'label_classes.*.name' => ['required', 'distinct:ignore_case', 'string', 'max:255'],
            'label_classes.*.color' => ['required', 'distinct', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'default_label_class' => ['nullable', 'numeric'],
            'detection_type' => ['required', new Enum(TeamDetectionTypeEnum::class)],
        ])->validateWithBag('createTeam');

        return DB::transaction(function () use ($user, $input) {
            AddingTeam::dispatch($user);

            $user->switchTeam($team = $user->ownedTeams()->create([
                'name' => $input['name'],
                'description' => $input['description'],
                'detection_type' => $input['detection_type'],
            ]));

            $label_classes = [];
            foreach ($input['label_classes'] as $label_class) {
                $label_classes[] = [
                    'user_id' => auth()->id(),
                    'title' => $label_class['name'],
                    'identifier' => Str::snake($label_class['name']),
                    'color' => $label_class['color'],
                ];
            }

            $label_classes = $team->labelClasses()->createMany($label_classes);

            if ($input['default_label_class'] !== null) {
                $team->default_label_class_id = $label_classes[$input['default_label_class']]->id;
                $team->save();
            }

            return $team;
        });
    }
}

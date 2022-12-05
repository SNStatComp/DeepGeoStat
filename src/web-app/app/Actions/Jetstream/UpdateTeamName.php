<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  array  $input
     * @return void
     */
    public function update($user, $team, array $input)
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'label_classes' => ['required', 'array', 'min:1'],
            'label_classes.*.id' => ['nullable', 'numeric'],
            'label_classes.*.title' => ['required', 'distinct:ignore_case', 'string', 'max:255'],
            'label_classes.*.color' => ['required', 'distinct', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'default_label_class' => ['nullable', 'numeric'],
        ])->validateWithBag('updateTeamName');

        DB::transaction(function () use ($user, $team, $input) {
            $team->forceFill([
                'name' => $input['name'],
                'description' => $input['description'],
            ])->save();

            $label_classes_ids = [];
            foreach ($input['label_classes'] as $label_class) {
                $label_class = $team->labelClasses()->updateOrCreate(
                    [
                        'id' => $label_class['id'],
                    ],
                    [
                        'user_id' => $user->id,
                        'title' => $label_class['title'],
                        'identifier' => Str::snake($label_class['title']),
                        'color' => $label_class['color'],
                    ]
                );

                $label_classes_ids[] = $label_class->id;
            }

            $team->labelClasses->whereNotIn('id', $label_classes_ids)->each->delete();

            $team->default_label_class_id = ($input['default_label_class'] !== null) ? $label_classes_ids[$input['default_label_class']] : null;
            $team->save();
        });
    }
}

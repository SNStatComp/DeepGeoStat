<?php

namespace App\Http\Controllers;

use App\Http\Resources\GridCellResource;
use App\Http\Resources\RegisterResource;
use App\Models\Register;
use App\Rules\DatasetsCompatible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function show(Request $request, Register $register)
    {
        $register = $register->load(['team', 'team.defaultLabelClass', 'team.labelClasses', 'datasets']);
        $pagination = ($register->datasets->count() === 1) ? 9 : 10;
        $grid_cells = $register->datasets[0]->gridCells()->paginate($pagination);

        return Inertia::render('Data/Registers/Show', [
            'register' => RegisterResource::make($register),
            'grid_cells' => GridCellResource::collection($grid_cells),
            'label_evidence' => $register->labelEvidence()->whereIn('grid_cell_id', collect($grid_cells->items())->pluck('id'))->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validateWithBag('importRegister', [
            'datasets' => ['required', 'array', 'min:1', 'max:2', new DatasetsCompatible],
            'datasets.*' => ['integer', 'exists:App\Models\Dataset,id'],
            'title' => ['required'],
            'description' => ['nullable'],
            'label_identifiers' => ['required', 'array'],
            'label_identifiers.*.label_class_id' => ['required', 'exists:App\Models\LabelClass,id'],
            'label_identifiers.*.identifier' => ['required'],
            'file' => ['required', 'file', 'mimes:csv'],
        ]);

        $datasets = auth()->user()->currentTeam->datasets()->find($request->datasets);
        $grid_cells = $datasets[0]->gridCells()->pluck('id', 'name');

        $label_classes = auth()->user()->currentTeam->labelClasses;
        foreach ($request->label_identifiers as $label_identifier) {
            $label_class = $label_classes->firstWhere('id', $label_identifier['label_class_id']);

            if ($label_class->identifier !== $label_identifier['identifier']) {
                $label_class->identifier = $label_identifier['identifier'];
                $label_class->save();
            }
        }

        $row_i = 0;
        $label_evidence = [];
        if (($handle = fopen($request->file->getRealPath(), 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if ($row_i > 0) {
                    $grid_cell = $data[0];
                    $label_class = $data[1];
                    $weight = (array_key_exists(2, $data)) ? $data[2] : null;

                    if ($grid_cells->has($grid_cell)) {
                        $grid_cell = $grid_cells[$grid_cell];
                        $label_class = $label_classes->firstWhere('identifier', $label_class);

                        $label_evidence[] = [
                            'user_id' => auth()->id(),
                            'grid_cell_id' => $grid_cell,
                            'label_class_id' => $label_class->id,
                            'weight' => $weight,
                        ];
                    }
                }

                $row_i++;
            }
        }

        $register = DB::transaction(function () use ($request, $label_evidence) {
            $register = auth()->user()->currentTeam->registers()->create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $register->datasets()->attach($request->datasets);

            $chunks = array_chunk($label_evidence, 25000);
            foreach ($chunks as $chunk) {
                $register->labelEvidence()->createMany($chunk);
            }

            return $register;
        });

        return redirect()->route('data.registers.show', $register);
    }

    public function destroy(Request $request, Register $register)
    {
        $register->delete();

        return back();
    }
}

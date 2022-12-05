<?php

namespace App\Http\Controllers;

use App\Models\LabelEvidence;
use Illuminate\Http\Request;

class LabelEvidenceGridController extends Controller
{
    public function store(Request $request, LabelEvidence $labelEvidence)
    {
        $request->validateWithBag('createLabels', [
            'labels' => ['required', 'array', 'min:1'],
            'labels.*.grid_id' => ['required', 'integer', 'exists:App\Models\DatasetGrid,id'],
            'labels.*.label_class_id' => ['required', 'integer', 'exists:App\Models\LabelClass,id'],
        ]);

        $labels = collect($request->labels)->map(function ($label) use ($labelEvidence) {
            return [
                'user_id' => auth()->id(),
                'label_evidence_id' => $labelEvidence->id,
                'grid_id' => $label['grid_id'],
                'label_class_id' => $label['label_class_id'],
            ];
        })->toArray();

        $labelEvidence->touch();
        $labelEvidence->labels()->upsert($labels, ['label_evidence_id', 'grid_id'], ['user_id', 'label_class_id']);

        return back();
    }
}

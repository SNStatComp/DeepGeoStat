<?php

namespace App\Http\Requests;

use App\Enums\TeamDetectionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatasetRequest extends FormRequest
{
    protected $errorBag = 'createDataset';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $team = auth()->user()->currentTeam;

        return [
            'datasets' => [
                'required', 'array',
                Rule::when($team->detection_type === TeamDetectionTypeEnum::Classification, [
                    'min:1',
                ]),
                Rule::when($team->detection_type === TeamDetectionTypeEnum::ChangeDetection, [
                    'size:2',
                ]),
            ],
            'datasets.*' => [
                'integer', 'exists:App\Models\SourceDataset,id',
            ],
            'title' => [
                'required',
            ],
            'description' => [
                'nullable',
            ],
            'surface_usage_filters' => [
                'nullable', 'array',
            ],
            'surface_usage_filters.*' => [
                'integer', 'exists:App\Models\SurfaceUsageFilter,id',
            ],
            'surface_usage_filter_mask' => [
                'nullable', 'boolean',
            ],
            'surface_usage_filter_mask_size' => [
                Rule::requiredIf(fn () => $this->get('surface_usage_filter_mask')),
                'nullable', 'regex:/\b(?<!\.)(?!0+(?:\.0+)?%)(?:\d|[1-9]\d|100)(?:(?<!100)\.\d+)?%/',
                // 'required_with:surface_usage_filter_mask',
            ],
            'region_filters' => [
                'nullable', 'array',
            ],
            'region_filters.*' => [
                'integer', 'exists:App\Models\Region,id',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'surface_usage_filter_mask_size.regex' => 'The surface usage filter mask size must be a valid percentage.',
        ];
    }
}

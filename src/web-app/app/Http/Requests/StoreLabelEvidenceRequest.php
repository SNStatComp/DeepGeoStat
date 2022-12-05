<?php

namespace App\Http\Requests;

use App\Enums\LabelEvidenceTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLabelEvidenceRequest extends FormRequest
{
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
        return [
            'dataset' => [
                'required', 'integer', 'exists:App\Models\Dataset,id',
            ],
            'title' => [
                'required',
            ],
            'description' => [
                'nullable',
            ],
            'type' => [
                'required', new Enum(LabelEvidenceTypeEnum::class),
            ],
            // Register
            'register' => [
                Rule::requiredIf(function () {
                    return $this->get('type') == LabelEvidenceTypeEnum::Register->value;
                }),
                'nullable', 'integer', 'exists:App\Models\Register,id',
            ],
            'register_label_classes' => [
                Rule::requiredIf(function () {
                    return $this->get('type') == LabelEvidenceTypeEnum::Register->value;
                }),
                'array', 'min:1',
            ],
            'register_label_classes.*.register_label_class' => [
                'required', 'integer', 'exists:App\Models\RegisterLabelClass,id',
            ],
            'register_label_classes.*.team_label_class' => [
                'required', 'integer', 'exists:App\Models\LabelClass,id',
            ],
            // File register
            'register_file' => [
                Rule::requiredIf(function () {
                    return $this->get('type') == LabelEvidenceTypeEnum::File->value;
                }),
                'nullable', 'file', 'mimes:txt,csv',
            ],
            'register_label_class_identifiers' => [
                Rule::requiredIf(function () {
                    return $this->get('type') == LabelEvidenceTypeEnum::File->value;
                }),
                'array', 'min:1',
            ],
            'register_label_class_identifiers.*.team_label_class' => [
                'required', 'integer', 'exists:App\Models\LabelClass,id',
            ],
            'register_label_class_identifiers.*.identifier' => [
                'required',
            ],
        ];
    }
}

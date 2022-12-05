<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'labelClasses' => $this->labelClasses->map(function ($labelClass) {
                return [
                    'id' => $labelClass->id,
                    'title' => $labelClass->title,
                ];
            }),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LabelEvidenceResource extends JsonResource
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
            'description' => $this->description,
            'user' => UserResource::make($this->whenLoaded('user')),
            'team' => TeamResource::make($this->whenLoaded('team')),
            'dataset' => DatasetResource::make($this->whenLoaded('dataset')),
        ];
    }
}

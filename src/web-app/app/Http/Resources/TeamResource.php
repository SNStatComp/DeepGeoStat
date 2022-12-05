<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'detectionType' => $this->detection_type,
            'owner' => UserResource::make($this->whenLoaded('user')),
            'labelClasses' => LabelClassResource::collection($this->whenLoaded('labelClasses')),
            'defaultLabelClass' => LabelClassResource::make($this->whenLoaded('defaultLabelClass')),
            'datasets' => DatasetResource::collection($this->whenLoaded('datasets')),
            'labelEvidence' => $this->whenLoaded('labelEvidence'),
        ];
    }
}

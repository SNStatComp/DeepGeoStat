<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GridCellResource extends JsonResource
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
            'name' => $this->name,
            'bbox' => "{$this->x_min},{$this->y_min},{$this->x_max},{$this->y_max}",
        ];
    }
}

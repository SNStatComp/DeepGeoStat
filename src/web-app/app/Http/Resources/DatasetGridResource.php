<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DatasetGridResource extends JsonResource
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
            'gridCells' => [
                DatasetGridCellResource::make($this->sourceGridCell, $this->sourceMask),
                $this->when($this->changeGridCell, function () {
                    return DatasetGridCellResource::make($this->changeGridCell, $this->changeMask);
                }),
            ],
        ];
    }
}

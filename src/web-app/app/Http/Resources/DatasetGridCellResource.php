<?php

namespace App\Http\Resources;

use App\Models\Mask;
use Illuminate\Http\Resources\Json\JsonResource;

class DatasetGridCellResource extends JsonResource
{
    public $mask;

    public function __construct($resource, Mask $mask = null)
    {
        parent::__construct($resource);

        $this->mask = $mask;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'title' => $this->gridCell->title,
            'url' => "{$this->dataset->url}&VERSION=1.3.0&REQUEST=GetMap&LAYERS={$this->dataset->layer}&CRS={$this->dataset->crs}&WIDTH=500&HEIGHT=500&FORMAT=image/png&styles&bbox={$this->gridCell->x_min},{$this->gridCell->y_min},{$this->gridCell->x_max},{$this->gridCell->y_max}",
            'polygon' => $this->whenNotNull($this->mask?->getCssMask()),
        ];
    }
}

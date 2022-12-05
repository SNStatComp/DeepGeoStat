<?php

namespace App\Http\Resources;

use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;

class ExperimentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->name,
            'options' => $this->options,
            'statistics' => $this->statistics,
            'learning_curve_simple' => $this->getLearningGraph(),
        ];
    }

    private function getLearningGraph()
    {
        try {
            $httpRequest = Http::get(config('services.api.url').'/experiments/'.$this->id.'/learning/simple');
        } catch (Exception $e) {
            $httpError = true;
        }

        if (! isset($httpError) && $httpRequest->successful()) {
            return $httpRequest->json();
        } else {
            return null;
        }
    }
}

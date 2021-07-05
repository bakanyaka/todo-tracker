<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Synchronization extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'completed_at' => $this->completed_at->toDateTimeString(),
            'completed_at_human' => $this->completed_at->diffForHumans()
        ];
    }
}

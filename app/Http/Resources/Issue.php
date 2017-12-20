<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property \App\BusinessDate closed_on
 */
class Issue extends Resource
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
            'id' => $this->id,
            'subject' => $this->subject,
            'assigned_to' => $this->assigned_to,
            'department' => $this->department,
            'priority' => $this->priority->name,
            'service' => $this->service->name,
            'estimated_hours' => $this->estimated_hours,
            'time_left' => $this->time_left,
            'created_on' => $this->created_on->toDateTimeString(),
            'closed_on' => optional($this->closed_on)->toDateTimeString()
        ];
    }
}

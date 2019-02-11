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
            'service' => optional($this->service)->name,
            'estimated_hours' => $this->estimated_hours,
            'time_left' => $this->time_left,
            'created_on' => $this->created_on->toDateTimeString(),
            'due_date' => optional($this->due_date)->toDateTimeString(),
            'closed_on' => optional($this->closed_on)->toDateTimeString(),
            'is_closed' => $this->status->is_closed,
            'is_paused' => $this->status->is_paused,
            'is_tracked_by_current_user' => $this->isTrackedByCurrentUser
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Assignee */
class AssigneeResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'name' => $this->name,
            'mail' => $this->mail,
        ];
    }
}

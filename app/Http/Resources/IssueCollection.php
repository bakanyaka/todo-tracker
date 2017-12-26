<?php

namespace App\Http\Resources;

use App\Http\Resources\Synchronization as SynchronizationResource;
use App\Models\Synchronization;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IssueCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        return [
            'data' => $this->collection,
            $this->mergeWhen($lastSync !== null,[
                'meta' => [
                    'last_sync' => new SynchronizationResource($lastSync)
                ]
            ]),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;


use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Facades\Redmine;
use App\Http\Controllers\Controller;
use App\Http\Resources\Service as ServiceResource;
use App\Models\Project;
use App\Models\Service;
use App\Models\Synchronization;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function sync()
    {
        $versionIds = [];
        foreach (Project::pluck('id') as $projectId) {
            try {
                $versions = Redmine::getVersions($projectId);
                $versions->each(function ($version) use ($projectId, &$versionIds) {
                    $hoursField = Arr::first($version['custom_fields'], function ($customField) {
                        return $customField['id'] === 82;
                    });
                    if ($hoursField && is_numeric($hoursField['value'])) {
                        $versionIds[] = $version['id'];
                        Service::updateOrCreate(
                            [
                                'id' => $version['id']
                            ],
                            [
                                'name' => $version['name'],
                                'project_id' => $projectId,
                                'hours' => (int) $hoursField['value'],
                            ]
                        );
                    }
                });
            } catch (FailedToRetrieveRedmineDataException $exception) {
                Log::notice("Failed to load versions for project id {$projectId}");
            }
        }

        // Delete services that have no redmine version with matching id
        $servicesWithNoMatchingVersion = Service::pluck('id')->diff($versionIds);
        Service::destroy($servicesWithNoMatchingVersion);
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'services',
        ]);
    }


}

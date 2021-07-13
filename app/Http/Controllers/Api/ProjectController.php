<?php

namespace App\Http\Controllers\Api;

use App\Facades\RedmineApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Project as ProjectResource;
use App\Models\Project;
use App\Models\Synchronization;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $projects = Project::orderBy('name')->get();
        return ProjectResource::collection($projects);
    }

    public function sync()
    {
        $redmineProjects = RedmineApi::getProjects();
        foreach ($redmineProjects as $redmineProject) {
            $project = Project::firstOrNew(['id' => $redmineProject['id']]);
            $project->name = $redmineProject['name'];
            $project->description = $redmineProject['description'];
            $project->identifier = $redmineProject['identifier'];
            $project->parent_id = $redmineProject['parent_id'];
            $project->save();
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'projects',
        ]);
    }
}

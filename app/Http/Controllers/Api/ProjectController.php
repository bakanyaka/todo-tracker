<?php

namespace App\Http\Controllers\Api;

use App\Facades\Redmine;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function sync()
    {
        $redmineProjects = Redmine::getProjects();
        foreach ($redmineProjects as $redmineProject) {
            $project = Project::firstOrNew(['id' => $redmineProject['id']]);
            $project->name = $redmineProject['name'];
            $project->description = $redmineProject['description'];
            $project->identifier = $redmineProject['identifier'];
            $project->save();
        }
    }
}
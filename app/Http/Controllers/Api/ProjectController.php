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
            Project::create([
                'id' => $redmineProject['id'],
                'name' => $redmineProject['name'],
                'description' => $redmineProject['description'],
                'identifier' => $redmineProject['identifier']
            ]);
        }

    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoriesController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        $categories = Category::orderBy('name')->get();
        return CategoryResource::collection($categories);
    }
}

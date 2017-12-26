<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JavaScript;

class FrontendController extends Controller
{
    public function index()
    {
        JavaScript::put([
            'redmineUri' => config('services.redmine.uri'),
            'user' => \Auth::user()
        ]);
        return view('index');
    }

}

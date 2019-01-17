<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//Auth::routes();
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login')->middleware('guest');
Route::post('/login',  'Auth\LoginController@login')->middleware('guest');;
Route::any('/logout', 'Auth\LoginController@logout')->name('logout')->middleware('auth');

Route::group(['prefix' => 'api', 'middleware' => ['auth']], function() {

    Route::group(['prefix' => 'issues'], function() {
        Route::get('/', 'Api\IssueController@index')->name('api.issues');
        Route::post('/track', 'Api\IssueController@store')->name('api.issues.track');
        Route::get('/sync', 'Api\IssueController@sync')->name('api.issues.sync');
        Route::get('/stats', 'Api\IssueStatsController@index')->name('api.issues.stats');
        Route::delete('/{issue}/track', 'Api\IssueController@destroy')->name('api.issues.untrack');
        Route::get('/reports/projects', 'Api\IssueReportController@byProject')->name('api.issues.reports.projects');
        Route::get('/reports', 'Api\IssueReportController@index')->name('api.issues.reports');
    });

    Route::group(['prefix' => 'projects'], function() {
        Route::get('/', 'Api\ProjectController@index')->name('api.projects');
        Route::get('/sync', 'Api\ProjectController@sync')->name('api.projects.sync');
    });

    Route::group(['prefix' => 'services', 'middleware' => ['can:touch,' . \App\Models\Service::class]], function() {
        Route::get('/', 'Api\ServiceController@index')->name('api.services');
        Route::post('/', 'Api\ServiceController@store')->name('api.services.store');
        Route::patch('/{service}', 'Api\ServiceController@update')->name('api.services.update');
        Route::delete('/{service}', 'Api\ServiceController@destroy')->name('api.services.destroy');
    });

    Route::group(['prefix' => 'assignees'], function() {
        Route::get('/', 'Api\AssigneeController@index')->name('api.assignees');
        Route::get('/sync', 'Api\AssigneeController@sync')->name('api.assignees.sync');
        Route::get('/report', 'Api\AssigneeReportController@index')->name('api.assignees.report.index');
        Route::get('/report/{assignee}', 'Api\AssigneeReportController@show')->name('api.assignees.report.show');
    });

    Route::group(['prefix' => 'time-entries'], function() {
        Route::get('/sync', 'Api\TimeEntryController@sync')->name('api.time-entries.sync');
    });


    Route::get('/synchronizations/last', 'Api\RedmineSyncController@index')->name('api.synchronizations.index');
});

//All unregistered routes should be handled by frontend
Route::any('{all}', 'FrontendController@index')->where(['all' => '.*'])->middleware(['auth']);





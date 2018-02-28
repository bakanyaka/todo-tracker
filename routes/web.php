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

Auth::routes();


Route::middleware(['auth'])->group(function() {

    Route::get('/services','ServiceController@index')->name('services');
    Route::post('/services','ServiceController@store');
    Route::get('/services/new','ServiceController@create')->name('services.new');
    Route::get('/services/{service}/edit','ServiceController@edit')->name('services.edit');
    Route::patch('/services/{service}','ServiceController@update')->name('services.update');
    Route::delete('/services/{service}','ServiceController@destroy')->name('services.delete');

});

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

    Route::group(['prefix' => 'services'], function() {
        Route::get('/', 'Api\ServiceController@index')->name('api.services');
        Route::post('/', 'Api\ServiceController@store')->name('api.services.store');
    });


    Route::get('/synchronizations/last', 'Api\RedmineSyncController@show')->name('api.synchronizations.last');
});

//All unregistered routes should be handled by frontend
Route::any('{all}', 'FrontendController@index')->where(['all' => '.*'])->middleware(['auth']);





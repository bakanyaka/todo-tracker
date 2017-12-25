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
    Route::get('/issues', 'Api\IssueController@index')->name('api.issues');
    Route::delete('/issues/{issue}/track', 'Api\IssueController@destroy')->name('api.issues.untrack');
    Route::post('/issues/track', 'Api\IssueController@store')->name('api.issues.track');
    Route::get('/issues/sync', 'Api\IssueController@sync')->name('api.issues.sync');

    Route::get('/synchronizations/last', 'Api\RedmineSyncController@show')->name('api.synchronizations.last');
});

//All unregistered routes should be handled by frontend
Route::any('{all}', 'FrontendController@index')->where(['all' => '.*'])->middleware(['auth']);





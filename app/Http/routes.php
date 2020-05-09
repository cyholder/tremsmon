<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/', 'MainController@index')->name('main.index');
Route::post('/', 'MainController@post')->name('main.post');
Route::get('api/db/tablespace', 'DatabaseController@tablespace');
Route::get('api/db/archivelog', 'DatabaseController@archivelog');
Route::get('api/app/list', 'DatabaseController@appList');
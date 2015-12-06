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

Route::get('/', ['middleware' => 'guest', 'uses' => 'AuthController@getLogin']);
Route::post('/', ['middleware' => 'guest', 'uses' => 'AuthController@postLogin']);

Route::get('register', ['middleware' => 'guest', 'uses' => 'AuthController@getRegister']);
Route::post('register', ['middleware' => 'guest', 'uses' => 'AuthController@postRegister']);

Route::get('logout', 'AuthController@logout');

Route::get('dashboard', ['middleware' => 'auth', 'uses' => 'DashboardController@getIndex']);
Route::get('admin', ['middleware' => 'auth', 'uses' => 'DashboardController@getAdmin']);

Route::get('confirm/{token}', ['middleware' => 'auth', 'uses' => 'DashboardController@getConfirm']);

Route::post('dashboard', ['middleware' => 'auth', 'uses' => 'DashboardController@postIndex']);

Route::get('test', 'DashboardController@test');

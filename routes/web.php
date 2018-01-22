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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify/{token}/{id}', 'Auth\RegisterController@verify');

Auth::routes();

Route::group(['middleware' => ['auth']], function(){
  Route::get('/home', 'HomeController@index')->name('home');
});

//login dengan Socialite
Route::get('social/login/redirect/{provider}', ['uses' => 'Auth\RegisterController@redirectToProvider', 'as' => 'social.login']);
Route::get('social/login/{provider}', 'Auth\RegisterController@handleProviderCallback');

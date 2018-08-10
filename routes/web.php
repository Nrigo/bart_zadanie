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

Route::group(['prefix' => 'gallery'],function(){
    Route::get('/', array('uses' => '\App\Http\Controllers\GalleryController@galleryList',   'as' => 'gallery.list'));
    Route::post('/', array('uses' => '\App\Http\Controllers\GalleryController@galleryCreate', 'as' => 'gallery.create'));
    
    Route::get('/{path}',    array('uses' => '\App\Http\Controllers\GalleryController@imagesList',   'as' => 'gallery.imageList'))->where('path', '.+');
    Route::delete('/{path}', array('uses' => '\App\Http\Controllers\GalleryController@delete',       'as' => 'gallery.delete'))->where('path', '.+');
    Route::post('/{path}',   array('uses' => '\App\Http\Controllers\GalleryController@imageUpload',  'as' => 'gallery.imageUpload'))->where('path', '.+');
});

Route::group(['prefix' => 'images'],function(){
	Route::get('/{w}x{h}/{path}', array('uses' => '\App\Http\Controllers\ImageController@getImageThumbnail',   'as' => 'images.getThumbnail'))->where('path', '.+');
});


Route::get('/facebook/login',   array('uses' => '\App\Http\Controllers\FacebookController@login',          'as' => 'facebook.login'));
Route::get('/facebook/logout',  array('uses' => '\App\Http\Controllers\FacebookController@logout',         'as' => 'facebook.logout'));
Route::get('/token',            array('uses' => '\App\Http\Controllers\FacebookController@callback',       'as' => 'facebook.callback'));
Route::get('/saveAccessToken',  array('uses' => '\App\Http\Controllers\FacebookController@saveAccessToken','as' => 'facebook.saveAccessToken'));







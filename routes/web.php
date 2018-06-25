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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware'=>'auth'],function(){

});



Route::group(['namespace'=>'Dropbox','middleware'=>'auth'],function(){

    Route::get('/dropbox', 'DropboxController@index');

    Route::post('/dropbox', 'DropboxController@postIndex');

    Route::get('/login-dropbox', 'DropboxController@loginDropbox');

    Route::get('/user-dropbox', 'DropboxController@userDropboxInfor');

    Route::get('/search-file-dropbox','DropboxController@getSearch');

    Route::post('/search-file-dropbox','DropboxController@postSearch')->name('search');

    Route::get('download','DropboxController@download');

    Route::get('upload-file-to-ebay', 'DropboxController@uploadFileEbay');

    Route::get('products/all', 'DropboxController@getAllProduct');

    // ----------------


    Route::get('begin', 'DropboxController@beginProcess');
});

Route::group(['namespace'=>'Dropbox'],function(){

    Route::get('add-item','DropboxController@createItemsEbay');
    Route::get('grant-code','DropboxController@createItemsEbay');
    Route::get('test',function(){
        dd(env('EBAY_APPID'));
    });

    Route::get('start','DropboxController@start');
    Route::get('step2','DropboxController@step2GetAccessTokenEbay');
    Route::get('step3','DropboxController@step3RefreshToken');

});



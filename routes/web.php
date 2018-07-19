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

Route::get('/home', 'Dropbox\DropboxController@products')->name('home');

Route::group(['middleware'=>'auth'],function(){

});




Route::group(['namespace'=>'Dropbox','middleware'=>'auth'],function(){

    Route::get('/dropbox', 'DropboxController@index')->name('dropbox');

    Route::post('/dropbox', 'DropboxController@postIndex');

    Route::get('/login-dropbox', 'DropboxController@loginDropbox');

    Route::get('/user-dropbox', 'DropboxController@userDropboxInfor');

    Route::get('/search-file-dropbox','DropboxController@getSearch');

    Route::post('/search-file-dropbox','DropboxController@postSearch')->name('search');

    Route::get('download','DropboxController@download');

    Route::get('upload-file-to-ebay', 'DropboxController@uploadFileEbay');

    Route::get('products/all', 'DropboxController@getAllProduct');
    
    // ----------------
    Route::get('grant','DropboxController@updateGrantCode')->name('grant');

    Route::get('begin', 'DropboxController@beginProcess')->name('begin');

    Route::get('test','DropboxController@testebay');

    Route::get('get-all','DropboxController@getAllItems')->name('getall');

    Route::get('get-item','DropboxController@getItem')->name('getItem');

    Route::get('get-detail-{id}','DropboxController@getDetail')->name('getDetail');

    Route::get('upload','DropboxController@upload');

    Route::get('refresh-app','DropboxController@refreshApp')->name('refresh');

    Route::get('test','TestController@index');

    Route::get('testjob','TestController@test');

    Route::get('products','DropboxController@products');

});

Route::group(['namespace'=>'Dropbox'],function(){

    Route::get('add-item','DropboxController@createItemsEbay');
    Route::get('grant-code','DropboxController@createItemsEbay');
    // Route::get('test',function(){
    //     dd(env('EBAY_APPID'));
    // });

    Route::get('start','DropboxController@start');
    Route::get('step2','DropboxController@step2GetAccessTokenEbay');
    Route::get('step3','DropboxController@step3RefreshToken');

});
Route::get('DownloadCSV',function(){

    dispatch(new \App\Jobs\dropbox\DownloadCSV);
    
});
Route::get('CheckCSVFile',function(){

    dispatch(new \App\Jobs\dropbox\CheckCSVFile);
    
});

Route::get('CreateInventoryEbay',function(){


    dispatch(new \App\Jobs\ebay\CreateInventoryEbay);
    
});
Route::get('CreateOfferEbay',function(){
    dispatch(new \App\Jobs\ebay\CreateOfferEbay);
    
});
Route::get('RefreshToken',function(){
    dispatch(new \App\Jobs\ebay\RefreshToken);
    
});
Route::get('PublicOfferEbay',function(){
    dispatch(new \App\Jobs\ebay\PublicOfferEbay);
    
});
Route::get('UpdateEbay',function(){
    $find = \App\Product::first();
    dispatch(new \App\Jobs\ebay\UpdateEbay($find));
});
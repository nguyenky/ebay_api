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

Route::get('testjob',function(){

     dispatch(new \App\Jobs\TestJob);
    
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

    Route::get('json',function(){
        $attributes=[];
        $data = [
                'availability'  => [
                    'shipToLocationAvailability'    => [
                        'quantity'  => '123',
                        // 'quantity'  => 12,
                    ]
                ],
                'condition'     => 'NEW',
                'product'       => [
                    'title'     => 'asdsad',
                    'imageUrls' =>[
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1000.jpg",
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1001.jpg",
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1002.jpg"
                    ],
                    'aspects'   => [
                        'size' => ['asdsad'],
                        'color' => ['asdsd'],
                        'length' => ['sfef'],
                        'width' => ['fsdf'],
                        'height' => ['assds'],
                    ],
                    'category' => ['asd'],
                    'asdsad' => [$attributes['name']],
                ]
            ];
        dd(json_encode($data));
    });


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



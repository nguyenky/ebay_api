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

Route::get('/ebay/preview', 'Ebay\EbayDescriptionController@index')->name('ebay_preview');
Route::get('/stock/download/csv', 'Downloads\DownloadAllStock@download')->name('download_all_stock_csv');
Route::get('/reports/missing-images/bullet-proof-fix', 'Reports\MissingImagesController@bulletProofFix')->name('report-missing-images-bullet-proof-fix');
Route::get('/reports/missing-images/relate_images', 'Reports\MissingImagesController@relateImages')->name('report-missing-images-relate');
Route::get('/tmp/', 'Reports\MissingImagesController@tmp')->name('tmp');

Route::group(['middleware'=>'auth'],function(){
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/products/{id}', 'Product\ProductDetailsController@index')->name('product-details');
    Route::get('/resync/{id}', 'HomeController@resync')->name('resync');
    Route::get('/resync-custom/', 'HomeController@resyncCustom')->name('custom-resync');
    Route::get('/get-inventory/{id}', 'HomeController@getInventory')->name('get-inventory');
    Route::get('/master-stock-update', 'HomeController@masterStockUpdate')->name('master-stock-update');

    Route::get('/ebay/inventory-sync', 'Ebay\EbayController@inventorySync')->name('ebay-inventory-sync');

    Route::get('/merchant-location', 'Ebay\MerchantLocationController@index')->name('merchant-location');

    //ManualProcessing
    Route::get('/manual-processing', 'ManualProcessingController@index')->name('manual-processing');
    Route::get('/manual-processing/step1', 'ManualProcessingController@step1')->name('manual-processing-step1');
    Route::get('/manual-processing/step2', 'ManualProcessingController@step2')->name('manual-processing-step2');
    Route::get('/manual-processing/step3', 'ManualProcessingController@step3')->name('manual-processing-step3');
    Route::get('/manual-processing/step4', 'ManualProcessingController@step4')->name('manual-processing-step4');
    Route::get('/manual-processing/step5', 'ManualProcessingController@step5')->name('manual-processing-step5');

    //Reports
    Route::get('/reports/missing-images', 'Reports\MissingImagesController@index')->name('report-missing-images');
    Route::get('/reports/missing-images/percents', 'Reports\MissingImagesController@generateImagesPercentages')->name('report-missing-images-percent');
    Route::get('/reports/missing-images/try-fix', 'Reports\MissingImagesController@tryFindImages')->name('report-missing-images-try-fix');

    //Unitex
    Route::get('/unitex/update-inventory-only', 'Unitex\UnitexEbayController@updateInventoryOnly')->name('unitex-update-inventory-only');
    Route::get('/unitex/update-inventory-ebay', 'Unitex\UnitexEbayController@updateInventoryAndPushToEbay')->name('unitex-update-inventory-and-ebay');

    Route::get('/unitex/shopify-product-refresh/test', 'Unitex\ShopifyRefreshController@index')->name('unitex-shopify-product-refresh-test');

    Route::get('/unitex/dropbox-product-refresh/', 'Unitex\DropboxRefreshController@index')->name('unitex-dropbox-product-refresh');
    Route::post('/unitex/dropbox-product-refresh/', 'Unitex\DropboxRefreshController@upload')->name('unitex-dropbox-product-refresh-upload');
    Route::get('/unitex/dropbox-product-refresh/review', 'Unitex\DropboxRefreshController@review')->name('unitex-dropbox-product-refresh-review');


    //Tools
    Route::get('/tools/generic-file-import', 'Tools\GenericImportController@index')->name('generic-file-import-tools');
    Route::post('/tools/generic-file-import/upload', 'Tools\GenericImportController@upload')->name('generic-file-import-tools-upload');
    Route::get('/tools/generic-file-import/options', 'Tools\GenericImportController@options')->name('generic-file-import-tools-options');

    Route::get('/tools/profit-calculator', 'Tools\ProfitCalculatorController@index')->name('tools-profit-calculator');


    //eBay
    Route::get('/ebay/import-orders', 'Ebay\ImportOrdersController@index')->name('ebay-import-orders');
    Route::post('/ebay/import-orders', 'Ebay\ImportOrdersController@process')->name('ebay-import-orders-process');

    Route::get('/ebay/api-playground', 'Ebay\ApiPlayController@index')->name('ebay-api-playground');
});

Route::group(['middleware'=>'auth','namespace'=>'ModeTest'],function(){

    Route::get('mode-test','ModeTestController@index')->name('mode-test');

    Route::post('update-mode-test','ModeTestController@update')->name('update-mode-test');

    Route::post('create-test-product','ModeTestController@create')->name('create-test-product');

    Route::get('del-product-test-{id}','ModeTestController@delete')->name('del-product-test');

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
\Log::info("Refresh Token at ". now());
    dispatch(new \App\Jobs\ebay\RefreshToken);
    
});
Route::get('PublicOfferEbay',function(){
    dispatch(new \App\Jobs\ebay\PublicOfferEbay);
    
});
Route::get('UpdateEbay',function(){
     // $find = \App\Product::where('product_mode_test',1)->first();
     $products = \App\Product::where('product_mode_test',1)->get();
     foreach ($products as $key => $value) {
         dispatch(new \App\Jobs\ebay\UpdateEbay($value));
     }
     // dispatch(new \App\Jobs\ebay\UpdateEbay($find));
});
Route::get('test-update-ebay',function(){
    $find = \App\Product::where('product_mode_test',0)->first();
    $x=new \App\Jobs\ebay\UpdateEbay($find);
    $x->handle();
});

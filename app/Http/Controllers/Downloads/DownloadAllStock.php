<?php

namespace App\Http\Controllers\Downloads;

use App\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DownloadAllStock extends Controller
{
    public function __construct()
    {
        env("APP_DEBUG", true);
    }

    public function download(Request $request){
        $result=false;
        infolog('[DownloadAllStock.download] START at '. now());

        $filename_and_path=public_path('files/all_available_stock.csv');

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        if(file_exists($filename_and_path) && filemtime($filename_and_path)>time()-(86400*1) && request("debug",true)===FALSE){
            infolog('[DownloadAllStock.download] DOWNLOADING CACHED at '. now());
            return(Response::download($filename_and_path, basename($filename_and_path), $headers));
        }

        $all=Product::whereNotNull("listingID")->whereRaw("QTY>2")->get();

        $counter=0;
        if($all){
            if($fp = fopen($filename_and_path, 'w')){
                fputcsv($fp, ["SKU","Name","Description","Category","Channel ID","QTY","RRP","List Price","Image1","Image2","Image3","Image4","Image5","created_at","updated_at"]);
                foreach($all as $product){
                    fputcsv($fp, [
                        $product->SKU,
                        $product->Name,
                        $product->Description,
                        $product->Category,
                        "EBAY-AU",
                        $product->QTY,
                        $product->RRP,
                        $product->listing_price,
                        $product->Image1,
                        $product->Image2,
                        $product->Image3,
                        $product->Image4,
                        $product->Image5,
                        $product->created_at,
                        $product->updated_at
                    ]);
                    $counter++;
                }
                fclose($fp);
            }
        }
        infolog('[DownloadAllStock.download] SUCCESSFULLY WRITTEN '.$counter.' ITEMS at '. now());

        if($counter>0){
            if(file_exists($filename_and_path)){
                infolog('[DownloadAllStock.download] DOWNLOADING at '. now());
                return(Response::download($filename_and_path, basename($filename_and_path), $headers));
            }else{
                infolog('[DownloadAllStock.download] ERROR NO FILE at '. now());
                die("0");
            }
        }else{
            infolog('[DownloadAllStock.download] ERROR NO ITEMS at '. now());
            die("0");
        }
    }

    public function index(){
    }
}

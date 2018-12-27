<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Product;
use App\Source;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function index($id=0){
        $source=false;
        $images=false;
        $specifics=false;
        if($product=Product::find($id)){
            $source=Source::find($product->source_id);
            $images=$product->getImagesArray();
            $specifics=$product->getSpecifics();
            $ebay_details=$product->getEbayDetails();
        }
        return view('products.details',[
            "product"=>$product,
            "source"=>$source,
            "images"=>$images,
            "specifics"=>$specifics,
            "ebay_details"=>$ebay_details,
        ]);
    }
}

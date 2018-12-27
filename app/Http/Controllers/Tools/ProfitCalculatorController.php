<?php

namespace App\Http\Controllers\Tools;

use App\EbayDetail;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfitCalculatorController extends Controller
{
    public function index(Request $request){
        $cost=100;
        $price=100;
        $id=$request->id;
        $listingid=$request->listingid;
        $product=false;
        if((int)$id>0){
            if($product=Product::find($id)){
                $cost=$product->cost;
                $price=$product->listing_price;
            }
        }
        if(strlen($listingid)>0){
            if($ed=EbayDetail::where("listingid",$listingid)->first()){
                if($product=Product::find($ed->product_id)){
                    $cost=$product->cost;
                    $price=$product->listing_price;
                }
            }
        }
        return view('tools.profit-calculator',[
            "cost"=>$cost,
            "price"=>$price,
            "product"=>$product
        ]);
    }
}

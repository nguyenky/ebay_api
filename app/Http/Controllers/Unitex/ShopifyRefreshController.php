<?php

namespace App\Http\Controllers\Unitex;

use App\Http\Controllers\Controller;
use App\Jobs\unitex\UnitexShopifyRefresh;

class ShopifyRefreshController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        infolog('[ShopifyRefreshController.index] START at '. now());
        $page=request("page",1);
        $page=($page<1)?1:$page;
        print("<p><a href='?page=".($page+1)."'>NEXT PAGE &gt;</a></p>");

        if($shopifyProducts=UnitexShopifyRefresh::pullAndParseFeed($page)){
            foreach($shopifyProducts as $product){
                if(array_key_exists("variants",$product) && is_array($product["variants"]) && count($product["variants"])>0){
                    foreach($product["variants"] as $variant){
                        dispatch_now(new UnitexShopifyRefresh($variant, $product));
                    }
                }else{
                    infolog('[ShopifyRefreshController.index] ERROR: Product id='.$product["id"].' does not have any variants! at '. now(),$shopifyProducts);
                }
            }
        }else{
            infolog('[ShopifyRefreshController.index] ERROR at '. now(),$shopifyProducts);
        }
        infolog('[ShopifyRefreshController.index] END at '. now());
    }
}

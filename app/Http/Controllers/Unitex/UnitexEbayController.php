<?php

namespace App\Http\Controllers\Unitex;

use App\Http\Controllers\Controller;

use App\Jobs\unitex\UnitexDailyInventoryUpdate;
use App\Jobs\ebay\BulkInventory;

class UnitexEbayController extends Controller
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
        /*
        $search=request("s");
        $products=Product::where('product_mode_test',0);
        if(strlen($search)>0){
            $products=$products->whereRaw("(SKU LIKE '%".DB::raw($search)."%' OR Name LIKE '%".DB::raw($search)."%' OR Description LIKE '%".DB::raw($search)."%' OR offerID LIKE '%".DB::raw($search)."%' OR listingID LIKE '%".DB::raw($search)."%')");
        }
        $products=$products->paginate(100);
        return view('products',['items'=>$products]);
        */
    }

    /**
     * Update Inventory Only
     *
     * @return \Illuminate\Http\Response
     */
    public function updateInventoryOnly()
    {
        infolog('[UpdateInventoryOnly] START at '. now());
        dispatch_now(new UnitexDailyInventoryUpdate());
        infolog('[UpdateInventoryOnly] END at '. now());
    }

    /**
     * Update Inventory and Push to eBay
     *
     * @return \Illuminate\Http\Response
     */
    public function updateInventoryAndPushToEbay()
    {
        infolog('[UpdateInventoryAndPushToEbay] START at '. now());
        dispatch_now(new UnitexDailyInventoryUpdate());
        dispatch_now(new BulkInventory());
        infolog('[UpdateInventoryAndPushToEbay] END at '. now());
    }
}

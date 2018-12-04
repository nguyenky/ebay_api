<?php

namespace App\Http\Controllers;

use App\Jobs\ebay\BulkInventory;
use App\Jobs\ebay\FullProductDataResync;
use App\Jobs\ebay\RefreshToken;

use App\Jobs\unitex\UnitexDailyInventoryUpdate;
use App\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public $token;
    public $api='https://api.ebay.com/';

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
        $search=request("s");
        //$products=Product::whereNotNull('sku');
        $products=DB::table("products AS p")
            ->select(DB::raw("p.id, s.source, p.sku, p.name, p.qty, p.cost, p.sell, p.listing_price, ed.offerid, ed.listingid, p.created_at, p.updated_at"))
            ->leftJoin("sources AS s","s.id","=","p.source_id")
            ->leftJoin("ebay_details AS ed","p.id","=","ed.product_id")
            ->whereNotNull('sku');
        if(strlen($search)>0){
            $products=$products->whereRaw("(sku LIKE '%".DB::raw($search)."%' OR name LIKE '%".DB::raw($search)."%' OR description LIKE '%".DB::raw($search)."%' OR offerid LIKE '%".DB::raw($search)."%' OR listingid LIKE '%".DB::raw($search)."%')");
        }
        $products=$products->paginate(100);
        return view('products',['items'=>$products]);
    }

    public function refreshToken(){
        $this->token=RefreshToken::dispatchNow();
    }

    /**
     * Performs an eBay Get Inventory API call.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInventory($id=0)
    {
        infolog("[GetEbayInventory] START at ".now());
        $this->token=RefreshToken::dispatchNow();
        $product=Product::find($id);
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', $this->api.'sell/inventory/v1/inventory_item/'.$product->SKU,[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);

            infolog('[GetEbayInventory] SUCCESS at '. now(), $search_results);
            return $search_results;

        } catch (\Exception $e) {
            infolog('[GetEbayInventory] FAIL at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
        infolog("[GetEbayInventory] END at ".now());
    }

    /**
     * Resync a Product across Marketplaces
     *
     * @return \Illuminate\Http\Response
     */
    public function resync($sku)
    {
        $product=Product::where("sku",$sku)->first();

        infolog("[HomeController] Product[SKU=$sku]->id=".$product->id." at ".now());

        dispatch_now(new FullProductDataResync($product));
    }

    /**
     * Resync a Custom Query across Marketplaces
     *
     * @return \Illuminate\Http\Response
     */
    public function resyncCustom()
    {
        $products=Product::where("images_percent",100)->whereNull("listingID")->whereNotNull("Image1")->where("product_mode_test",0)->where("QTY",'>',0)->get();
        infolog("[resyncCustom] COUNT ".count($products)." at ".now());
        foreach($products as $product){
            dispatch(new FullProductDataResync($product));
            infolog("[resyncCustom] dispatched JOB FullProductDataResync at ".now());
        }
        dd("Nope");
    }

    /**
     * Perform a Master Stock File Update
     *
     * @return \Illuminate\Http\Response
     */
    public function masterStockUpdate()
    {
        infolog('[MasterStockUpdate] START at '. now());
        dispatch_now(new UnitexDailyInventoryUpdate());
        dispatch_now(new BulkInventory());
        infolog('[MasterStockUpdate] END at '. now());
    }
}

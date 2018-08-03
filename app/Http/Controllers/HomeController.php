<?php

namespace App\Http\Controllers;

use App\Jobs\dropbox\BulkInventory;
use App\Jobs\ebay\FullProductDataResync;
use App\Jobs\ebay\PublicOfferEbay;
use App\Jobs\ebay\RefreshToken;
use App\Jobs\ebay\UpdateEbay;
use Illuminate\Http\Request;

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
        $products=Product::where('product_mode_test',0);
        if(strlen($search)>0){
            $products=$products->whereRaw("(SKU LIKE '%".DB::raw($search)."%' OR Name LIKE '%".DB::raw($search)."%' OR Description LIKE '%".DB::raw($search)."%' OR offerID LIKE '%".DB::raw($search)."%' OR listingID LIKE '%".DB::raw($search)."%')");
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
    public function resync($id=0)
    {
        $product=Product::find($id);

        dispatch_now(new FullProductDataResync($product));
    }

    /**
     * Perform a Master Stock File Update
     *
     * @return \Illuminate\Http\Response
     */
    public function masterStockUpdate()
    {
        infolog('[MasterStockUpdate] START at '. now());
        dispatch_now(new BulkInventory());
        infolog('[MasterStockUpdate] END at '. now());
    }
}

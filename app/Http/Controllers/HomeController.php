<?php

namespace App\Http\Controllers;

use App\Jobs\ebay\RefreshToken;
use App\Jobs\ebay\UpdateEbay;
use Illuminate\Http\Request;

use App\Product;

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
        $products = Product::where('product_mode_test',0)->paginate(100);
        return view('products',['items'=>$products]);
    }

    public function refreshToken(){
        $this->token=RefreshToken::dispatchNow();
    }

    /**
     * Resync a Product across Marketplaces
     *
     * @return \Illuminate\Http\Response
     */
    public function updateOfferEbay(Product $product)
    {
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$product->id);

        try {
            $client = new \GuzzleHttp\Client();
            $data = [
                'availability'  => [
                    'shipToLocationAvailability'    => [
                        'quantity'  => $product->QTY,
                    ]
                ],
                'condition' => 'NEW',
                "merchantLocationKey" => env('MERCHANTLOCATIONKEY'),
                "listingPolicies" => [
                    "fulfillmentPolicyId" => env('FULFILLMENTPOLICYID'),
                    "paymentPolicyId"     => env('PAYMENTPOLICYID') ,
                    "returnPolicyId"      => env('RETURNPOLICYID')
                ],
                "categoryId" => env('CATEGORYID') ,
                "format"=>"FIXED_PRICE",
                "listingDescription" => $description,
                "pricingSummary"=>[
                    "price"  => [
                        "currency" => "AUD",
                        "value" => $product->listing_price
                    ]
                ]
            ];
            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_AU',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            infolog('[UpdateOfferEbay] PUT '.$this->api.'sell/inventory/v1/offer/'.$product->offerID);
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/offer/'.$product->offerID,[
                'headers'=> $header,
                'body'  => $json
            ]);
            $search_results = json_decode($res->getBody(), true);
            infolog('[UpdateOfferEbay] SUCCESS! at '. now(), $search_results);
        }catch(\Exception $e) {
            infolog('[UpdateOfferEbay] FAIL at '. now(), $e);
            //infolog("Details",$e->getResponse()->getBody()->getContents());
        }
        infolog('[UpdateOfferEbay] END at '. now());
    }

    /**
     * Resync a Product across Marketplaces
     *
     * @return \Illuminate\Http\Response
     */
    public function updateInventoryEbay(Product $product)
    {
        $imageUrls =[];
        for($c=1;$c<=5;$c++) {
            $key="Image".$c;
            $img=$product->$key;
            if(strlen($img)>0){
                $imageUrls[]=env("PROD_APP_URL").'/images/'.$img;
            }
        }

        try {
            $client = new \GuzzleHttp\Client();
            $data = [
                'product'       => [
                    'title'     => $product->Name,
                    'imageUrls' => $imageUrls,
                    'aspects'   => [
                        'size' => [$product->Size],
                        'color' => [$product->Color],
                        'length' => [$product->Length],
                        'width' => [$product->Width],
                        'height' => [$product->Height],
                        'unitweight' => [$product->UnitWeight],
                        'construction' => [$product->Construction ? $product->Construction : 'NEW'],
                        'material' => [$product->Material],
                        'pileheight' => [$product->Pileheight]
                    ],
                    'category' => $product->Category,
                    'cost' => $product->Cost,
                    'sell' => $product->Sell,
                    'rrp' => $product->RRP,
                    'origin' => $product->Origin,
                ]
            ];
            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_AU',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            infolog('[UpdateInventoryEbay] PUT '.$this->api.'sell/inventory/v1/inventory_item/'.$product->SKU);
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/inventory_item/'.$product->SKU,[
                'headers'=> $header,
                'body'  => $json
            ]);
            $search_results = json_decode($res->getBody(), true);
            infolog('[UpdateInventoryEbay] SUCCESS! at '. now(), $search_results);
        }catch(\Exception $e) {
            infolog('[UpdateInventoryEbay] FAIL at '. now(), $e);
            //infolog("Details",$e->getResponse()->getBody()->getContents());
        }
        infolog('[UpdateInventoryEbay] END at '. now());
    }

    public function getOffer($sku){

        infolog('[GetOfferEbay] START ----Get Offer---- at '. now());
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', $this->api.'sell/inventory/v1/offer?sku='.$sku,[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);

            infolog('[GetOfferEbay] SUCCESS at '. now(), $search_results);
            return $search_results;

        } catch (\Exception $e) {
            infolog('[GetOfferEbay] FAIL at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
    }

    public function checkForEbayListingID($product){
        $result=false;
        if($results=$this->getOffer($product->SKU)){
            #todo: check if item has a listingID
            if(is_array($results) && array_key_exists("offers",$results) && is_array($results["offers"])){
                foreach($results["offers"] as $offer){
                    if($offer["sku"]==$product->SKU){
                        if(array_key_exists("listing",$results)){
                            $result=$offer["listing"]["listingId"];
                            infolog('[checkForEbayListingID] RESULT='.($result).' at '. now());
                        }else{
                            infolog('[checkForEbayListingID] FAIL: No Listing Found at '. now());
                        }
                        break;
                    }
                }
            }else{
                infolog('[checkForEbayListingID] FAIL: No Offers Found at '. now());
            }
        }
        return($result);
    }

    /**
     * Resync a Product across Marketplaces
     *
     * @return \Illuminate\Http\Response
     */
    public function resync($id=0)
    {
        $product=Product::find($id);

        $this->refreshToken();

        if($product->offerID && !$product->listingID){
            infolog('[Resync] ISSUE product has an OfferID but not a ListingID. Checking eBay...'. now());
            if($listingID=$this->checkForEbayListingID($product)){
            }
        }

        $this->updateOfferEbay($product);

        $this->updateInventoryEbay($product);

        //dispatch_now(new UpdateEbay($product));
    }
}

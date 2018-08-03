<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Product;

class FullProductDataResync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;
    public $token;
    public $api='https://api.ebay.com/';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        infolog('[FullProductDataResync] __construct at '. now());
        $this->product=$product;
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __destruct()
    {
        infolog('[FullProductDataResync] __destruct at '. now());
    }

    /**
     * Refresh the eBay Token
     *
     * @return void
     */
    public function refreshToken(){
        $this->token=RefreshToken::dispatchNow();
    }

    /**
     * Create an offer on eBay
     *
     * @param $product (Product) - the Product model to create an offer for
     *
     * @return void
     */
    public function createOfferEbay($product){
        infolog('[CreateOfferEbay] START at '. now());
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$product->id);
        try {
            $client = new \GuzzleHttp\Client();
            $data = [
                "sku"  =>$product->SKU,
                "marketplaceId" => "EBAY_AU",
                "format" => "FIXED_PRICE",
                "listingDescription" => $description,
                "availableQuantity" => $product->QTY,
                "quantityLimitPerBuyer" => $product->QTY,
                "pricingSummary" => [
                    "price"  => [
                        "value" => $product->listing_price,
                        "currency" => "AUD"
                    ]
                ],
                "listingPolicies" => [
                    "fulfillmentPolicyId" => env('FULFILLMENTPOLICYID'),
                    "paymentPolicyId"     => env('PAYMENTPOLICYID') ,
                    "returnPolicyId"      => env('RETURNPOLICYID')
                ],
                "categoryId" => env('CATEGORYID') ,
                "merchantLocationKey" => env('MERCHANTLOCATIONKEY')
            ];

            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Accept'=>'application/json',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('POST', $this->api.'sell/inventory/v1/offer',[
                'headers'=> $header,
                'body'  => $json
            ]);
            $search_results = json_decode($res->getBody(), true);

            infolog('Job Create Offer SUCCESS at '. now());
            return $search_results;

        } catch(\Exception $e) {
            infolog('Job Create Offer FAIL at '. now());
            infolog("Details",$e->getResponse()->getBody()->getContents());
            dd($e);
        }
        infolog('Job Create Offer END at '. now());
    }

    /**
     * Get an Offer from eBay based on a SKU
     *
     * @param $sku (string) the SKU to search on eBay
     *
     * @return void
     */
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

    /**
     * Check if there is a ListingID on eBay for a given Product
     *
     * @param $product (Product) the Product to search on eBay
     *
     * @return boolean - true if found to have a ListingID, false if not
     */
    public function checkForEbayListingID(Product $product){
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
     * @param $product (Product) the Product to resync on eBay
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
        try {
            $client = new \GuzzleHttp\Client();
            $data = [
                'product'       => [
                    'title'     => $product->Name,
                    'imageUrls' => $product->getImagesArray(),
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->refreshToken();

        $createdOffer=false;
        if(!$this->product->offerID){
            infolog('[Resync] ISSUE product does NOT have an OfferID . Checking eBay... at '. now());
            if($result=$this->createOfferEbay($this->product)){
                $this->product->offerID=$result['offerId'];
                $this->product->save();
                infolog('[Resync] SAVED product now has a OfferID: '.$this->product->offerID.' at '. now());
                $createdOffer=true;
            }else{
                infolog('[Resync] ERROR product could NOT GET a OfferID at '. now());
            }
        }

        if($this->product->offerID && !$this->product->listingID){
            infolog('[Resync] ISSUE product has an OfferID but not a ListingID. Checking eBay... at'. now());
            if($listingID=$this->checkForEbayListingID($this->product)){
                $this->product->listingID=$listingID;
                $this->product->save();
                infolog('[Resync] SAVED product now has a ListingID: '.$this->product->listingID.' at '. now());
            }else{
                infolog('[Resync] ERROR product could NOT GET a ListingID at '. now());
            }
        }

        if(!$createdOffer){
            $this->updateOfferEbay($this->product);
        }

        $this->updateInventoryEbay($this->product);

        if($this->product->offerID && !$this->product->listingID){
            infolog('[Resync] ISSUE product has an OfferID but not a ListingID. attempting to publish at '. now());
            $po=new PublicOfferEbay();
            if($listingID=$po->publicOffer($this->product)){
                $this->product->listingID=$listingID;
                $this->product->save();
                infolog('[Resync] SAVED product now has a ListingID: '.$this->product->listingID.' at '. now());
            }
        }

        //dispatch_now(new UpdateEbay($product));
        infolog("[GetEbayInventory] END at ".now());
    }
}

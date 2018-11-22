<?php

namespace App\Jobs\ebay;

use App\EbayDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Product;

class FullProductDataResync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product=false;
    public $ebay_details=false;
    public $images=false;
    public $specifics=false;
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
     * getEbayDetails
     *
     * @return void
     */
    public function getEbayDetails($product=NULL){
        $product=($product===NULL)?$this->product:$product;
        if(!$this->ebay_details){
            $this->ebay_details=EbayDetail::where("product_id",$product->id)->first();
        }
        if(!$this->ebay_details){
            throw new \Error("eBay Details Not Found: product_id=".$product->id);
        }
        return($this->ebay_details);
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
            $this->getEbayDetails($product);
            $data = [
                "sku"  =>$product->sku,
                "marketplaceId" => "EBAY_AU",
                "format" => "FIXED_PRICE",
                "listingDescription" => $description,
                "availableQuantity" => $product->qty,
                "quantityLimitPerBuyer" => $product->qty,
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
                "categoryId" => $this->ebay_details->categoryid,
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
        if($results=$this->getOffer($product->sku)){
            #todo: check if item has a listingID
            if(is_array($results) && array_key_exists("offers",$results) && is_array($results["offers"])){
                foreach($results["offers"] as $offer){
                    if($offer["sku"]==$product->sku){
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
            $this->getEbayDetails($product);
            $data = [
                'availability'  => [
                    'shipToLocationAvailability'    => [
                        'quantity'  => $product->qty,
                    ]
                ],
                'condition' => 'NEW',
                "merchantLocationKey" => env('MERCHANTLOCATIONKEY'),
                "listingPolicies" => [
                    "fulfillmentPolicyId" => env('FULFILLMENTPOLICYID'),
                    "paymentPolicyId"     => env('PAYMENTPOLICYID') ,
                    "returnPolicyId"      => env('RETURNPOLICYID')
                ],
                "categoryId" =>$this->ebay_details->categoryid,
                "format"=>"FIXED_PRICE",
                "listingDescription" => $description,
                "pricingSummary"=>[
                    "price"  => [
                        "currency" => "AUD",
                        "value" => $product->listing_price
                    ]
                ],
                "brand" => "Unbranded",
                "mpn" => "Does Not Apply",
                "upc" => ["Does Not Apply"]
            ];
            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_AU',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            infolog('[UpdateOfferEbay] PUT '.$this->api.'sell/inventory/v1/offer/'.$this->ebay_details->offerid);
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/offer/'.$this->ebay_details->offerid,[
                'headers'=> $header,
                'body'  => $json
            ]);
            $search_results = json_decode($res->getBody(), true);
            infolog('[UpdateOfferEbay] SUCCESS! at '. now(), $search_results);
        }catch(\Exception $e) {
            infolog('[UpdateOfferEbay] FAIL at '. now(), $e);
            infolog("Details",$e->getResponse()->getBody()->getContents());
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
        $result=false;
        try {
            $client = new \GuzzleHttp\Client();
            if($aspects=$product->getSpecifics(true)){
                $aspects["Brand"]=["Unbranded"];
                infolog('[UpdateInventoryEbay.getSpecifics] DATA at '. now(), $aspects);
            }else{
                $aspects=[
                    "Brand" => [
                        "Unbranded"
                    ]
                ];
            }
            $images=$product->getImagesArray();
            if(!$images){
                $err='[UpdateInventoryEbay.updateInventoryEbay] ERROR: No Images Found for product_id='.$product->id.' at '. now();
                infolog($err, $images);
                throw new \Error($err);
            }
            $data = [
                'product'       => [
                    'title'     => $product->name,
                    'imageUrls' => $images,
                    'aspects'   => $aspects,
                    "brand" => "Unbranded",
                    "mpn" => "Does Not Apply",
                    "upc" => ["Does Not Apply"],
                ],
                'condition' => 'NEW'
            ];
            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_AU',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            infolog('[UpdateInventoryEbay] PUT '.$this->api.'sell/inventory/v1/inventory_item/'.$product->sku);
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/inventory_item/'.$product->sku,[
                'headers'=> $header,
                'body'  => $json
            ]);
            $search_results = json_decode($res->getBody(), true);
            infolog('[UpdateInventoryEbay] SUCCESS! at '. now(), $search_results);
            $result=true;
        }catch(\Exception $e) {
            infolog('[UpdateInventoryEbay] FAIL at '. now(), $e);
            infolog("Details",$e->getResponse()->getBody()->getContents());
        }
        infolog('[UpdateInventoryEbay] END at '. now());

        return($result);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->refreshToken();

        $this->getEbayDetails();

        $createdOffer=false;
        if(!optional($this->ebay_details)->offerid){
            infolog('[Resync] ISSUE product does NOT have an OfferID . Checking eBay... at '. now());
            if($result=$this->createOfferEbay($this->product)){
                if(!$this->ebay_details){
                    $this->ebay_details=new EbayDetail();
                    $this->ebay_details->product_id=$this->product->id;
                }
                $this->ebay_details->offerid=$result['offerId'];
                $this->ebay_details->save();
                infolog('[Resync] SAVED product now has a OfferID: '.$this->ebay_details->offerid.' at '. now());
                $createdOffer=true;
            }else{
                infolog('[Resync] ERROR product could NOT GET a OfferID at '. now());
            }
        }

        if($this->ebay_details->offerid && !$this->ebay_details->listingid){
            infolog('[Resync] ISSUE product has an OfferID but not a ListingID. Checking eBay... at'. now());
            if($listingID=$this->checkForEbayListingID($this->product)){
                $this->ebay_details->listingid=$listingID;
                $this->ebay_details->save();
                infolog('[Resync] SAVED product now has a ListingID: '.$this->product->listingid.' at '. now());
            }else{
                $this->ebay_details->error='[Resync] ERROR product could NOT GET a ListingID at '. now();
                $this->ebay_details->save();
                infolog($this->ebay_details->error);
            }
        }

        if(!$createdOffer){
            $this->updateOfferEbay($this->product);
        }

        if($this->updateInventoryEbay($this->product)){
            $this->ebay_details->sync=1;
            $this->ebay_details->synced_at=date("Y-m-d H:i:s");
            $this->ebay_details->save();
        }

        if($this->ebay_details->offerid && !$this->ebay_details->listingid){
            infolog('[Resync] ISSUE product has an OfferID but not a ListingID. attempting to publish at '. now());
            $po=new PublicOfferEbay();
            if($listingID=$po->publicOffer($this->product)){
                $this->ebay_details->sync=1;
                $this->ebay_details->synced_at=date("Y-m-d H:i:s");
                $this->ebay_details->listingid=$listingID;
                $this->ebay_details->save();
                infolog('[Resync] SAVED product now has a ListingID: '.$this->ebay_details->listingid.' at '. now());
            }
        }

        //dispatch_now(new UpdateEbay($product));
        infolog("[GetEbayInventory] END at ".now());
    }
}

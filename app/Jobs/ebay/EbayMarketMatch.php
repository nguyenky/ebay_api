<?php

namespace App\Jobs\ebay;

use App\CompetitorItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product;
use Illuminate\Support\Facades\DB;

class EbayMarketMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $token;
    protected $api;
    protected $product;

    public function __construct(Product $product)
    {
        infolog('[EbayMarketMatch] __construct at '. now());
        $this->api = 'https://api.ebay.com/';
        $this->token = \App\Token::find(1);

        $this->product=$product;
    }
    public function __destruct()
    {
        infolog('[EbayMarketMatch] __destruct at '. now());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->product->sku){
            infolog('[EbayMarketMatch] product has a SKU at '. now(),$this->product->sku);
            $this->matchMarket();
        }
    }

    /**
     * getCompetitors
     *
     * @return void
     */
    public function getApiUrl()
    {
        $url="http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&outputSelector=SellerInfo&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=LarsSorh-ixplores-PRD-42ccbdebc-9ad47cf8&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD=true&paginationInput.entriesPerPage=100&keywords=".$this->product->sku."&GLOBAL-ID=EBAY-AU";
        infolog('[EbayMarketMatch.getApiUrl] eBay API URL: '.$url);
        return($url);
    }

    /**
     * getCompetitors
     *
     * @return void
     */
    public function getCompetitors()
    {
        $result=[];
        if($data=@file_get_contents($this->getApiUrl())){
            if($json=json_decode($data, true)){
                if($json["findItemsAdvancedResponse"]){
                    if(is_array($json["findItemsAdvancedResponse"]) && $json["findItemsAdvancedResponse"][0]){
                        if($json["findItemsAdvancedResponse"][0]["ack"][0]=="Success"){
                            if($json["findItemsAdvancedResponse"][0]["searchResult"] && is_array($json["findItemsAdvancedResponse"][0]["searchResult"]) && array_key_exists("item",$json["findItemsAdvancedResponse"][0]["searchResult"][0]) && is_array($json["findItemsAdvancedResponse"][0]["searchResult"][0]["item"])){
                                $items=$json["findItemsAdvancedResponse"][0]["searchResult"][0]["item"];
                                infolog('[EbayMarketMatch.getCompetitors] Found '.count($items).' items '. now());
                                $affected=DB::table("competitor_items")->where('sku', $this->product->sku)->update(array('latest' => 0));
                                if($affected){
                                    infolog('[EbayMarketMatch.getCompetitors] Updated '.$affected.' existing SKUs at '. now());
                                }else{
                                    infolog('[EbayMarketMatch.getCompetitors] NO existing SKUs at '. now());
                                }
                                foreach($items as $item){
                                    $competitor=new CompetitorItem();
                                    $competitor->sku=$this->product->sku;
                                    $competitor->uniqueid=$item["itemId"][0];
                                    $competitor->title=$item["title"][0];
                                    $competitor->market=$item["globalId"][0];
                                    $competitor->categoryid=$item["primaryCategory"][0]["categoryId"][0];
                                    $competitor->category=$item["primaryCategory"][0]["categoryName"][0];
                                    $competitor->image=$item["galleryURL"][0];
                                    $competitor->url=$item["viewItemURL"][0];
                                    $competitor->country=$item["country"][0];
                                    $competitor->seller=$item["sellerInfo"][0]["sellerUserName"][0];
                                    $competitor->feedback_score=$item["sellerInfo"][0]["feedbackScore"][0];
                                    $competitor->feedback_percent=$item["sellerInfo"][0]["positiveFeedbackPercent"][0];
                                    $competitor->feedback_rating=$item["sellerInfo"][0]["feedbackRatingStar"][0];
                                    $competitor->top_rated_seller=$item["sellerInfo"][0]["topRatedSeller"][0];
                                    //todo isue here
                                    $competitor->shipping_currency=@$item["shippingInfo"][0]["shippingServiceCost"][0]["@currencyId"];
                                    $competitor->shipping=@$item["shippingInfo"][0]["shippingServiceCost"][0]["__value__"];
                                    $competitor->shipping_type=$item["shippingInfo"][0]["shippingType"][0];
                                    $competitor->shipping_location=$item["shippingInfo"][0]["shipToLocations"][0];
                                    $competitor->currency=$item["sellingStatus"][0]["convertedCurrentPrice"][0]["@currencyId"];
                                    $competitor->price=$item["sellingStatus"][0]["convertedCurrentPrice"][0]["__value__"];
                                    $competitor->latest=1;
                                    $competitor->save();
                                    $result[]=$competitor;
                                }
                            }else{
                                infolog('[EbayMarketMatch.getCompetitors] ERROR: findItemsAdvancedResponse unexplained error at '. now(),$json["findItemsAdvancedResponse"][0]);
                            }
                        }else{
                            infolog('[EbayMarketMatch.getCompetitors] ERROR: findItemsAdvancedResponse ACK was NOT a success at '. now(),$json);
                        }
                    }else{
                        infolog('[EbayMarketMatch.getCompetitors] ERROR: findItemsAdvancedResponse is an empty array at '. now(),$json);
                    }
                }else{
                    infolog('[EbayMarketMatch.getCompetitors] ERROR: findItemsAdvancedResponse is false at '. now(),$json);
                }
            }else{
                infolog('[EbayMarketMatch.getCompetitors] ERROR: json_decode failed at '. now(),$data);
            }
        }else{
            infolog('[EbayMarketMatch.getCompetitors] ERROR: Failed to connect to the API at '. now());
        }
    }

    public function matchMarket(){
        $result=false;
        if($competitors=$this->getCompetitors()){
            foreach($competitors as $competitor){
                /*
                if($this->hasPriceDifferential($competitor)){
                    if($this->fixPriceDifferential($competitor)){

                    }else{

                    }
                }else{

                }
                */
            }
        }
        // - Do business logic
        //Update

        return($result);

        $attribute->setListingPrice();
        infolog('Job Update Offer START at '. now());
        print("Trying to load: ".env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);
        // $token=$this->getToken();
        //Here you do not need to get token again. Because the token will be update every 30 minutes !

        try {
            $client = new \GuzzleHttp\Client();

            $data = [
                "title"  =>$attribute->Name,
                "listingDescription"  =>$description,
                "SKU"=>$attribute->SKU,
                "marketplaceId"=>"EBAY_AU",
                "format"=>"FIXED_PRICE",
		        "pricingSummary"=>[
                    "price"  => [
                        "currency" => "AUD",
                        "value" => $attribute->listing_price
                    ]
                ],
                "availableQuantity"=> $attribute->QTY,
                "listingPolicies" => [
                    "fulfillmentPolicyId" => env('FULFILLMENTPOLICYID'),
                    "paymentPolicyId"     => env('PAYMENTPOLICYID') ,
                    "returnPolicyId"      => env('RETURNPOLICYID')
                ],
                "categoryId" => env('CATEGORYID'),
                "quantityLimitPerBuyer"=>$attribute->QTY
            ];

            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Accept'=>'application/json',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/offer/'.$attribute->offerID,[
                            'headers'=> $header,
                            'body'  => $json
                        ]);

            $search_results = json_decode($res->getBody(), true);
            // dd('ok',$search_results);
            infolog('Job Update Offer SUCCESS at '. now(),$search_results);
        } catch(\Exception $e) {
            // dd('error',$e->getMessage());
            infolog('Job Update Offer FAIL at '. now());
            infolog("Details",$e->getResponse()->getBody()->getContents());
        }
        infolog('Job Update Offer END at '. now());

        $this->product->ebayupdated_at=date("Y-m-d H:i:s");
        $this->product->save();
    }
}

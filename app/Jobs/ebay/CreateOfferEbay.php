<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateOfferEbay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $token;
    protected $api;
    public function __construct()
    {
        if(env('EBAY_SERVER') == 'sandbox'){

            $this->api = 'https://api.sandbox.ebay.com/';

        }else{

            $this->api = 'https://api.ebay.com/';
        }
        $this->token = \App\Token::find(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        infolog('Job [CreateOfferEbay] START at '. now());
        $system = \App\System::first();
        if($system->mode_test){
            $products = \App\Product::where('product_mode_test',1)->get();
        }else{
            $products = \App\Product::where('product_mode_test',0)->get();
        }
        $not_created=0;
        $created=0;
        $published=0;
        $unpublished=0;
        foreach ($products as $key => $value) {
            $offer = $this->getOffer($value->SKU);
            if(!$offer){
                $createOffer = $this->createOffer($value);
                if($createOffer){
                    $product = \App\Product::where('SKU',$value->SKU)->first();
                    $product->offerID = $createOffer['offerId'];
                    $product->save();
                    $created++;
                }else{
                    $not_created++;
                }
            }else{
                $product = \App\Product::where('SKU',$value->SKU)->first();
                $product->offerID = $offer['offers'][0]['offerId'];
                if($offer['offers'][0]['status']=='PUBLISHED'){
                    $product->listingID = $offer['offers'][0]['listing']['listingId'];
                    $published++;
                }

                if($offer['offers'][0]['status']=='UNPUBLISHED'){
                    $product->listingID = null;
                    $unpublished++;
                }
                $product->save();
            }
        }
        infolog('Job [CreateOfferEbay] RESULT $not_created='.$not_created.' at '. now());
        infolog('Job [CreateOfferEbay] RESULT $created='.$created.' at '. now());
        infolog('Job [CreateOfferEbay] RESULT $published='.$published.' at '. now());
        infolog('Job [CreateOfferEbay] RESULT $unpublished='.$unpublished.' at '. now());
        infolog('Job [CreateOfferEbay] END at '. now());
    }

    public function getOffer($attribute){

        infolog('Job [Ebay] START ----Get Offer---- at '. now());

        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', $this->api.'sell/inventory/v1/offer?sku='.$attribute,[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);

            infolog('Job [Ebay] END ----Get Offer---- at '. now(), $search_results);

            return $search_results;

        } catch (\Exception $e) {
             infolog('Job [Get Ebay] FAIL ----Get Offer:  at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
    }

    public function createOffer($attribute){
        infolog('Job Update Offer START at '. now());
        print("Trying to load: ".env("APP_URL")."/ebay/preview/?id=".$attribute->id);
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);
        try {
            $client = new \GuzzleHttp\Client();
            $data = [
                "sku"  =>$attribute->SKU,
                "marketplaceId" => "EBAY_AU",
                "format" => "FIXED_PRICE",
                "listingDescription" => $description,
                "availableQuantity" => $attribute->QTY,
                "quantityLimitPerBuyer" => ceil($attribute->QTY/2),
                "pricingSummary" => [
                        "price"  => [
                            "value" => $attribute->listing_price,
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
}

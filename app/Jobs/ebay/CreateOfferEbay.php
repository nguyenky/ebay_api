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
        $system = \App\System::first();
        if($system->mode_test){
            $products = \App\Product::where('product_mode_test',1)->get();
        }else{
            $products = \App\Product::where('product_mode_test',0)->get();
        }
        foreach ($products as $key => $value) {
            $offer = $this->getOffer($value->SKU);
            if(!$offer){
                $createOffer = $this->createOffer($value);
                if($createOffer){
                    $product = \App\Product::where('SKU',$value->SKU)->first();
                    $product->offerID = $createOffer['offerId'];
                    $product->save();
                }
            }else{
                $product = \App\Product::where('SKU',$value->SKU)->first();
                $product->offerID = $offer['offers'][0]['offerId'];
                if($offer['offers'][0]['status']=='PUBLISHED'){
                    $product->listingID = $offer['offers'][0]['listing']['listingId'];
                }

                if($offer['offers'][0]['status']=='UNPUBLISHED'){
                    $product->listingID = null;
                }
                $product->save();
            }
        }
    }

    public function getOffer($attribute){

        \Log::info('Job [Ebay] START ----Get Offer---- at '. now());

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

            return $search_results;

        } catch (\Exception $e) {
             \Log::info('Job [Ebay] FAIL ----Get Offer---- at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
        \Log::info('Job [Ebay] END ----Get Offer---- at '. now());
        
    }

    public function createOffer($attribute){
        \Log::info('Job Update Offer START at '. now());
        print("Trying to load: ".env("APP_URL")."/ebay/preview/?id=".$attribute->id);
        $description=file_get_contents(env("APP_URL")."/ebay/preview/?id=".$attribute->id);
        try {
            $client = new \GuzzleHttp\Client();
            $data = [];
            $data = [
                "sku"  =>$attribute->SKU,
                "marketplaceId" => "EBAY_AU",
                "format" => "FIXED_PRICE",
                "listingDescription" => $description,
                "availableQuantity" => 10,
                "quantityLimitPerBuyer" => 2,
                "pricingSummary" => [
                        "price"  => [
                            "value" => $attribute->RRP,
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

        \Log::info('Job Create Offer SUCCESS at '. now());
        return $search_results;
        
        } catch(\Exception $e) {
             \Log::info('Job Create Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Create Offer END at '. now());
    }


}

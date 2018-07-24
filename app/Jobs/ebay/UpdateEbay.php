<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product;
class UpdateEbay implements ShouldQueue
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
        if(env('EBAY_SERVER') == 'sandbox'){

            $this->api = 'https://api.sandbox.ebay.com/';

        }else{

            $this->api = 'https://api.ebay.com/';
        }
        $this->token = \App\Token::find(1);
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->product->offerID){
            $this->updateOffer($this->product);
        }
    }

    public function getToken(){
        $api = 'https://api.ebay.com/';
        $token = \App\Token::find(1);
        $client = new \GuzzleHttp\Client();
        $appID = env('EBAY_APPID');
        $clientID = env('CERT_ID');

        $code = $appID .':'.$clientID;

        $header = [
            'Content-Type'=>'application/x-www-form-urlencoded',
            'Authorization'=> 'Basic '.base64_encode($code)
        ];
        $body = [
            'grant_type'=>'refresh_token',
            'refresh_token'=>$token->refresh_token_ebay,
            'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
        ];
        $res = $client->request('POST', $api.'identity/v1/oauth2/token',[
            'headers'=> $header,
            'form_params'  => $body
        ]);

        $search_results = json_decode($res->getBody(), true);
        $token->accesstoken_ebay = $search_results['access_token'];
        $token->save();
        return($token->accesstoken_ebay);
    }

    public function updateOffer($attribute){
        $attribute->setListingPrice();

        info('Job Update Offer START at '. now());
        print("Trying to load: ".env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);

        $token=$this->getToken();

        try {
            $client = new \GuzzleHttp\Client();

            $data = [
                "title"  =>$attribute->Name,
                "listingDescription"  =>$description,
                "pricingSummary" => [
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
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];

            dump("URL:",$this->api.'sell/inventory/v1/offer/'.$attribute->offerID);
            dump("Header:",$header);
            dump("Body:",$json);

            $res = $client->request('PUT', $this->api.'sell/inventory/v1/offer/'.$attribute->offerID,[
                            'headers'=> $header,
                            'body'  => $json
                        ]);

            $search_results = json_decode($res->getBody(), true);
            info('Job Update Offer SUCCESS at '. now(),$search_results);
        } catch(\Exception $e) {
             info('Job Update Offer FAIL at '. now());
             dd($e);
        }
        info('Job Update Offer END at '. now());

        $this->product->ebayupdated_at=date("Y-m-d H:i:s");
        $this->product->save();
    }
}

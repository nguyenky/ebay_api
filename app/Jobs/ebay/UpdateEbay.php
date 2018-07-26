<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use \Log;
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
    public function updateOffer($attribute){
        \Log::info('Job Update Offer START at '. now());
        print("Trying to load: ".env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);
        $description=file_get_contents(env("PROD_APP_URL")."/ebay/preview/?id=".$attribute->id);

        try {
            $client = new \GuzzleHttp\Client();

            $data = [];
            $data = [
                "listingDescription"  =>$description,
                "pricingSummary" => [
                        "price"  => [
                            "currency" => "AUD",
                            "value" => $attribute->RRP
                        ]
                ],
                "SKU"=>$attribute->SKU,
                "marketplaceId"=>"EBAY_AU",
                "format"=>"FIXED_PRICE"
            ];

            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Accept'=>'application/json',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];

            dump("URL:",$this->api.'sell/inventory/v1/offer/'.$attribute->offerID);
            dump("Header:",$header);
            dump("Body:",$json);

            $res = $client->request('POST', $this->api.'sell/inventory/v1/offer/'.$attribute->offerID,[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        \Log::info('Job Update Offer SUCCESS at '. now());
        return null;
        } catch(\Exception $e) {
             \Log::info('Job Update Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Update Offer END at '. now());

        $this->product->updateebay_at=date("Y-m-d H:i:s");
    }
}

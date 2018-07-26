<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublicOfferEbay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected  $token;
    protected  $api;
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
            if(!$value->listingID && $value->offerID){
                $listingID = $this->publicOffer($value);
                $product = \App\Product::where('SKU',$value->SKU)->first();
                $product->listingID = $listingID;
                $product->save();
            }
        }

    }

    public function publicOffer($attribute){
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('POST', $this->api.'sell/inventory/v1/offer/'.$attribute->offerID.'/publish',[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);
            return $search_results['listingId'];
        } catch (\Exception $e) {
             \Log::info('Job [Ebay] FAIL ----Get Offer---- at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
    }
}

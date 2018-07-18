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
        $products = \App\Product::all();
        foreach ($products as $key => $value) {
            if(!$value->listingID){
                $this->publicOffer($value);
            }
        }
    }

    public function publicOffer($attribute){
        try {
            // dd($attribute);
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            // dd($header);
            $res = $client->request('GET', $this->api.'sell/inventory/v1/offer/'.$attribute->SKU.'/publish',[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);
            // dd($search_results);
            // return $search_results['offers'];
            return true;
        } catch (\Exception $e) {
             \Log::info('Job [Ebay] FAIL ----Get Offer---- at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
    }
}

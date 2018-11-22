<?php

namespace App\Jobs\ebay;

use App\EbayDetail;
use App\Product;
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
        infolog('Job [PublishOfferEbay] START at '. now());

        $products = \App\Product::where('product_mode_test',0)->get();

        infolog('Job [PublishOfferEbay] FOUND '.count($products).' products in LIVE MODE at '. now());

        $published=0;
        $already_published=0;
        $no_offer=0;
        $skipped_test_mode=0;
        foreach ($products as $key => $value) {
            if($value->product_mode_test){
                $skipped_test_mode++;
            }elseif(!$value->listingID && $value->offerID){
                if($listingID=$this->publicOffer($value)){
                    $product = \App\Product::where('sku',$value->SKU)->first();
                    $product->listingID = $listingID;
                    $product->save();
                    $published++;
                }
            }elseif($value->listingID && $value->offerID){
                $already_published++;
            }else{
                $no_offer++;
            }
        }
        infolog('Job [PublishOfferEbay] RESULT $skipped_test_mode='.$skipped_test_mode.' at '. now());
        infolog('Job [PublishOfferEbay] RESULT $published='.$published.' at '. now());
        infolog('Job [PublishOfferEbay] RESULT $already_published='.$already_published.' at '. now());
        infolog('Job [PublishOfferEbay] RESULT $no_offer='.$no_offer.' at '. now());
        infolog('Job [PublishOfferEbay] END at '. now());
    }

    public function publicOffer(Product $product){
        $details=EbayDetail::where("product_id",$product->id)->first();
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            infolog('Job [PublishOfferEbay] CALLING '.$this->api.'sell/inventory/v1/offer/'.$details->offerid.'/publish at '. now());
            $res = $client->request('POST', $this->api.'sell/inventory/v1/offer/'.$details->offerid.'/publish',[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);
            return $search_results['listingId'];
        } catch (\Exception $e) {
             infolog('Job [PublishOfferEbay] FAIL ----Publish Offer---- at '. now());
             $err=$e->getResponse()->getBody()->getContents();
             infolog("Details",$err);
             $details->error=$err;
             $details->save();
             if($e->getCode()==404){
                 return false;
             }
        }
    }
}

<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Product;

class DescriptionUpdate implements ShouldQueue
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
        infolog('[DescriptionUpdate] __construct(product_id='.$product->id.') at '. now());
        $this->product=$product;
        $this->token = \App\Token::find(1);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __destruct()
    {
        infolog('[DescriptionUpdate] __destruct at '. now());
    }

    /**
     * Queue Updates where date updated is newer than the date eBay was updated.
     *
     * @return void
     */
    static public function queueAllEbay()
    {
        infolog('[DescriptionUpdate] queue STARTED at '. now());
        $products=Product::whereNotNull("listingID")->where("ebayupdated_at","<","updated_at")->get();
        if($products){
            foreach($products as $product){
                dispatch(new self($product));
            }
        }
        infolog('[DescriptionUpdate] queue ENDED at '. now());
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

            $product->ebayupdated_at=time()+10;
            $product->save();

            infolog('[UpdateOfferEbay] SUCCESS! at '. now(), $search_results);
        }catch(\Exception $e) {
            infolog('[UpdateOfferEbay] FAIL at '. now(), $e);
            //infolog("Details",$e->getResponse()->getBody()->getContents());
        }
        infolog('[UpdateOfferEbay] END at '. now());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->product->offerID){
            $this->updateOfferEbay($this->product);
        }
    }
}

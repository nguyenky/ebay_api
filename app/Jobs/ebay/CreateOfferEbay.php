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
        $product = \App\Product::all();
        foreach ($product as $key => $value) {
            $offer = $this->getOffer($value->SKU);
            if(!$offer){
                $createOffer = $this->createOffer($value);
                if($createOffer){
                    $product = \App\Product::where('SKU',$value->SKU)->first();
                    $product->offerID = $createOffer['offerId'];
                    $product->save();
                }
            }    
        }
        
    }

    public function getOffer($attribute){
        \Log::info('Job [Ebay] START ----Get Offer---- at '. now());
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
            $res = $client->request('GET', $this->api.'sell/inventory/v1/offer?sku='.$attribute,[
                'headers'=> $header,
            ]);
            // dd($res);
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
        \Log::info('Job [Ebay] END ----Get Offer---- at '. now());
        
    }

    public function createOffer($attribute){
        $imageUrls =[];
        if($attribute->Image1){
            $url = url('/images/'.$attribute->Image1); 
            array_push($imageUrls,$url);
        }
        if($attribute->Image2){
            $url = url('/images/'.$attribute->Image2); 
            array_push($imageUrls,$url);
        }
        if($attribute->Image3){
            $url = url('/images/'.$attribute->Image3); 
            array_push($imageUrls,$url);
        }
        if($attribute->Image4){
            $url = url('/images/'.$attribute->Image4); 
            array_push($imageUrls,$url);
        }
        if($attribute->Image5){
            $url = url('/images/'.$attribute->Image5); 
            array_push($imageUrls,$url);
        }


        $description = "<!DOCTYPE html><html lang='en'><head><meta charset='utf-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Basic Listing</title><link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Anton'><style>html{box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box}body{font-size:100%;font-family:'Helvetica',Arial,sans-serif;-webkit-font-smoothing:antialiased;margin:0px}*{margin:0;padding:0;outline:none;border:none;text-decoration:none}.product{margin-bottom:50px}.wrap-title{padding:50px}.product-detail{margin-top:50px}p{margin-top:20px;margin-bottom:0;padding:0;font-size:14px;line-height:1.5}p strong{font-size:14px;font-weight:600}ul{font-size:14px}.tabs{display:flex;flex-wrap:wrap}.input{position:absolute;opacity:0}.label{background-color:#fff;float:left;border:none;outline:none;cursor:pointer;display:flex;align-items:center;justify-content:center;text-align:center;transition:0.3s;font-size:13px;color:#00005e;text-align:center;text-transform:uppercase;font-weight:bold;box-sizing:border-box;border-top:1px solid #d9d9d9;border-right:1px solid #d9d9d9;border-bottom:1px solid #d9d9d9;position:relative;top:1px;height:50px}.label.tab1{width:20%;border-left:1px solid #d9d9d9}.label.tab2{width:20%}.input:checked+.label{color:#92bcba;position:relative;z-index:1;top:1px;border-bottom:1px solid #fff}@media (min-width: 600px){.label{width:auto}}.panel{display:none;background:#fff}@media (min-width: 600px){.panel{order:99}}.input:checked+.label+.panel{display:block}.tab{overflow:hidden;background-color:#fff;position:relative;bottom:-5px;display:inline-block}.tabcontent{border:1px solid #d9d9d9;padding:35px}.tabcontent *{-webkit-animation:scale 0.7s ease-in-out;-moz-animation:scale 0.7s ease-in-out;animation:scale 0.7s ease-in-out}@keyframes scale{0%{transform:scale(0.9);opacity:0}50%{transform:scale(1.01);opacity:0.5}100%{transform:scale(1);opacity:1}}.tabcontent ul{list-style-type:disc;letter-spacing:0.3px;line-height:26px;font-size:14px;padding-left:30px;margin-top:15px}.clear{clear:both}img{max-width:100%;display:block}.container{max-width:1150px;width:100%;margin:0 auto;padding-left:15px;padding-right:15px;background:#fff;color:#5f5f5f;font-size:14px;box-sizing:border-box}.gallery{width:60%;float:left;margin-top:24px;position:relative}.images-box{width:100%;max-width:482px;max-height:392px;height:392px;float:right}.defaultimg{position:absolute;top:15%;right:0;bottom:0;left:84px;width:100%;max-width:504px;height:392px;-webkit-animation:cssAnimation 0.7661s 1 ease-in-out;-moz-animation:cssAnimation 0.7661s 1 ease-in-out;-o-animation:cssAnimation 0.7661s 1 ease-in-out;padding-left:46px;box-sizing:border-box}.defaultimg img{margin:0 auto;max-width:244px;width:100%}.small-images{list-style:none;float:left;margin-top:-19px}.small-images li{display:block;width:84px;height:90px;cursor:pointer}.small-images .item-content{position:relative;float:left;width:84px;height:105px;border-bottom:1px solid #d9d9d9}.small-images .small-image{position:absolute;top:0;right:0;bottom:0;left:0;margin:auto;max-width:100%;max-height:100%}.small-images .gallery-content{position:absolute;top:0;left:84px;width:100%;max-width:504px;height:392px;display:none;padding-left:46px;box-sizing:border-box}.item-wrapper{width:100%;height:100%;position:relative}.small-images .gallery-content img{margin:0 auto;width:auto;max-height:100%}.small-images li.image:hover .gallery-content#image{display:block;-webkit-animation:cssAnimation 0.7661s 1 ease-in-out;-moz-animation:cssAnimation 0.7661s 1 ease-in-out;-o-animation:cssAnimation 0.7661s 1 ease-in-out;padding-top:5%}.small-images li:hover~.defaultimg{display:none}@-webkit-keyframes cssAnimation{from{-webkit-transform:rotate(0deg) scale(0.48) skew(-180deg) translate(0px)}to{-webkit-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)}}@-moz-keyframes cssAnimation{from{-moz-transform:rotate(0deg) scale(0.48) skew(-180deg) translate(0px)}to{-moz-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)}}@-o-keyframes cssAnimation{from{-o-transform:rotate(0deg) scale(0.48) skew(-180deg) translate(0px)}to{-o-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)}}.gallery-detail{width:40%;float:right;padding-top:80px;box-sizing:border-box}.gallery-detail h1{font-size:33px;font-weight:400;line-height:33px;color:#5f5f5f;letter-spacing:0.9px}.gallery-detail .price{font-size:22px;font-weight:600;color:#5f5f5f;margin-top:17px;letter-spacing:0.6px}</style></head><body><div class='container'><div class='content'> <section class='product'><div class='gallery-content'><div class='gallery'><div class='images-box'></div><ul class='small-images' id='list-thumnail'><li class='image'><div class='item-content'> <img class='small-image' src='".url('/images/'.$attribute->Image1)."'></div><div class='gallery-content' id='image'><div class='item-wrapper'><img src='".url('/images/'.$attribute->Image1)."'></div></div></li><li class='image'><div class='item-content'><img class='small-image' src='".url('/images/'.$attribute->Image2)."'></div><div class='gallery-content' id='image'><div class='item-wrapper'> <img src='".url('/images/'.$attribute->Image2)."'></div></div></li><li class='image'><div class='item-content'> <img class='small-image' src='".url('/images/'.$attribute->Image3)."'></div><div class='gallery-content' id='image'><div class='item-wrapper'> <img src='".url('/images/'.$attribute->Image3)."'></div></div></li><li class='image'><div class='item-content'> <img class='small-image' src='".url('/images/'.$attribute->Image4)."'></div><div class='gallery-content' id='image'><div class='item-wrapper'> <img src='".url('/images/'.$attribute->Image4)."'></div></div></li><li class='image'><div class='item-content'> <img class='small-image' src='".url('/images/'.$attribute->Image5)."'></div><div class='gallery-content' id='image'><div class='item-wrapper'> <img src='".url('/images/'.$attribute->Image5)."'></div></div></li><div class='defaultimg'><div class='inner'> <img src='".url('/images/'.$attribute->Image1)."'></div></div></ul><div class='clear'></div></div><div class='gallery-detail'><h1>NEW Sana Grey Hand Woven Flatweave Wool &amp; Viscose Rug</h1><p class='price'>$ 389.00</p></div><div class='clear'></div></div> </section> <section class='product-details'><div class='tabs'> <input class='input' name='tabs' type='radio' id='tab-1' checked='checked'/> <label class='label tab1' for='tab-1'>Product details</label><div class='panel'><div id='Product-details' class='tabcontent'><p><strong>Features:</strong></p><ul><li> Hand woven by skilled artisans for a flatweave look that can take three to four months to create, with a soft feel and rugged construction</li><li> Materials: wool, viscose</li><li> A rug pad is recommended to extend the life of your rug, protect your floors and avoid sliding</li><li> Wool is a natural fibre that will stay looking fresh and new for longer. It is a naturally beautiful, eco-friendly and sustainable choice to create a warm and comfortable environment for your home. Viscose adds luminosity and extra softness.</li><li> Flatwoven rug - no pile</li><li> Construction: hand woven flat weave</li><li> As products are hand made, please allow for slight variations in sizing and appearance</li><li> Please note, rug images are taken in a brightly lit studio environment; please allow for colour variations depending on your monitor settings and the lighting environment in your home.</li><li> Product Type: Area Rugs</li><li> Style: Contemporary; Scandinavian</li><li>Size (Size: 155 x 225cm): 160cm x 230cm</li><li> Size (Size: 190 x 280cm): 200cm x 300cm</li><li>Size (Size: 230 x 320cm): 240cm x 330cm</li><li>Shape: Rectangular</li><li>Primary Colour: Silver &amp; Grey</li><li>Material: Wool; Synthetics<ul><li>Material Details: Wool &amp; Viscose</li></ul></li><li>Primary Pattern: Striped</li><li>Construction: Hand Woven; Flat Woven</li><li>Eco-Friendly: No</li><li>Reversible: No</li><li>Non-Slip Backing: No</li><li>Handmade: Yes</li><li>Fringes: No</li><li>Rug Pad Needed: Yes</li><li>Commercial Use: No</li><li>Region of Origin: South Asia</li><li>Outdoor Use: No</li><li>Recycled Content: No<ul><li>Recycled Content Details:</li></ul></li><li>Product Care: Spot Clean, Gentle Vacuum, Professional Rug Cleaning Only</li></ul> <strong>Dimensions:</strong><ul><li>Overall Length - End to End (Size: 155 x 225cm): 225</li><li>Overall Length - End to End (Size: 190 x 280cm): 280</li><li>Overall Length - End to End (Size: 230 x 320cm): 320</li><li>Overall Width - Side to Side (Size: 155 x 225cm): 155</li><li>Overall Width - Side to Side (Size: 190 x 280cm): 190</li><li>Overall Width - Side to Side (Size: 230 x 320cm): 230</li><li>Pile Height: 0.25</li><li>Overall Product Weight (Size: 155 x 225cm): 10</li><li>Overall Product Weight (Size: 190 x 280cm): 16</li><li>Overall Product Weight (Size: 230 x 320cm): 24</li></ul> <strong>Warranty:</strong><ul><li>Product Warranty: Statutory Warranty</li></ul><p></p></div></div> <input class='input' name='tabs' type='radio' id='tab-2'/> <label class='label tab2' for='tab-2'>Shipping & Returns</label><div class='panel'><div id='Shipping-returns' class='tabcontent'><p> 30 day returns<br><br> We want you to love the products you buy from us. If you change your mind, you may return it to us within 30 days of the date you received it, no questions asked. You will be responsible for all shipping charges to facilitate a change of mind return. If you change your mind, we will provide you with a refund in an amount equal to the price you paid for the product, less all shipping costs. Items returned must be in 'as-new' condition. This means you have not used, assembled, damaged, washed or laundered any of the items. Please return items secured in their original packaging. If you cannot return an item 'as new' in its original packaging, a handling and restocking fee may apply up to 20% of the value of the item. <br><br> Non-returnable items excluded from all change of mind returns include:</p><ul><li>Products described as 'made to order'</li><li>Mattresses, bedding and pillows</li><li>Clearance items</li><li>Personalised items</li></ul> <br><p>Within 5 business days of receiving your return, and subject to confirming it is in 'as-new' condition, we will issue you with a refund via email in an amount equal to the price you paid for the product, less the cost to ship the product to you and the return shipping back to the warehouse. The return shipping cost is the same as the initial delivery fee. If you purchase an item with promotional shipping (discounted or free shipping) and you return it because you change your mind, we will deduct the actual shipping costs from your refund. Both the cost of shipping the item to you and the cost of the return shipping to the warehouse will be deducted. We will not accept returns delivered in person to our offices or warehouse facilities.</p><br><br><p>Refunds by law: In Australia, consumers have a legal right to obtain a refund from a business for goods purchased if the goods are faulty, not fit for purpose or don't match description. More information at returns.<br></p><p></p></div></div></div> </section></div></div></body></html>";
            // dd($description);
        try {
            // dd($attribute);
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

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateJobOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $access_token_ebay;
    protected $sku;
    protected $availableQuantity;
    protected $quantityLimitPerBuyer;
    protected $query  = 'UNITEX-DATAFEED-ALL.csv';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('-------- START PROCESS -------------');
        $token = \App\Token::find(1);
        $csv = $this->DropboxConvertFileCsv('files/'.$this->query);
        foreach ($csv as $key => $value) {
            // dd($token->accesstoken_ebay);
            $offer = $this->getOffers($token->accesstoken_ebay,$value['SKU']);
      
            if($offer != NULL) {
            
                $this->deleteOffer($token->accesstoken_ebay,$offer[0]['offerId']);
                $offerId = $this->postOffer($token->accesstoken_ebay,$value['SKU']);
                $this->publishOffer($offerId,$token->accesstoken_ebay);
            
            } else {

                $offerId = $this->postOffer($token->accesstoken_ebay,$value['SKU']);
                $this->publishOffer($offerId,$token->accesstoken_ebay);
            }
        }
        \Log::info('-------- END PROCESS -------------');
    }

    public function deleteOffer($token,$offerID){
    \Log::info('Job Delete Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];

            $res = $client->request('DELETE', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer/'.$offerID,[
                            'headers'=> $header,
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results["offers"][0]["offerId"]);
        \Log::info('Job Delete Offer SUCCESS at '. now());
        return null;
        } catch(\Exception $e) {
             \Log::info('Job Delete Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Delete Offer END at '. now());
    }

    public function postOffer($token,$sku){
    // $token = \App\Token::find(1);

     \Log::info('Job Create Offer START at '. now());
        try {

            $client = new \GuzzleHttp\Client();
            $data = [];
            $data = [
                "sku"  =>$sku,
                "marketplaceId" => "EBAY_US",
                "format" => "FIXED_PRICE",
                "listingDescription" => "234",
                "availableQuantity" => 2,
                "quantityLimitPerBuyer" => 2,
                "pricingSummary" => [
                        "price"  => [
                            "value" => 0.99,
                            "currency" => "USD"
                        ]
                ],
                "listingPolicies" => [
                    "fulfillmentPolicyId" => "5808775000",
                    "paymentPolicyId"     => "5808776000",
                    "returnPolicyId"      => "5808774000"
                ],
                "categoryId" => "88433",
                "merchantLocationKey" => "loc-001",
                "tax" => [
                    "vatPercentage" => 10.2,
                    "applyTax"  => true,
                    "thirdPartyTaxCategory" => "Electronics"

                ]
            ];

            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('POST', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer',[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        \Log::info('Job Create Offer SUCCESS at '. now());
        return $search_results['offerId'];
        
        } catch(\Exception $e) {
             \Log::info('Job Create Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Create Offer END at '. now());
    }

    public function getOffers($token,$sku){

     \Log::info('Job Get Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer?sku='.$sku,[
                            'headers'=> $header,
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results["offers"][0]["offerId"]);
        \Log::info('Job Get Offer SUCCESS at '. now());
        return $search_results["offers"];
        
        } catch(\Exception $e) {
             \Log::info('Job Get Offer FAIL at '. now());
             if($e->getCode()==404) {
                // $this->postOffer($token,$sku)
                return null;
             }
        }
        \Log::info('Job Get Offer END at '. now());
    }

    public function publishOffer($orderId,$token) {
        
        \Log::info('Job Publish Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($orderId);
            $res = $client->request('POST','https://api.sandbox.ebay.com/sell/inventory/v1/offer/'.$orderId.'/publish',[
                            'headers'=> $header,
                        ]);
            
        $search_results = json_decode($res->getBody(), true);
      
        \Log::info('Job Publish Offer SUCCESS at '. now());
        return $search_results["offers"];
        
        } catch(\Exception $e) {
             \Log::info('Job Publish Offer FAIL at '. now());
            dd($e);
        }
        \Log::info('Job Publish Offer END at '. now());
    }

    public function DropboxConvertFileCsv($attribute){
        // dd($attribute);
        $csv = Array();
        $rowcount = 0;
        $file =  public_path($attribute);
        if (($handle = fopen($file, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $entry;
                }
                else {
                    return null;
                }
                $rowcount++;
            }
            //echo "Totally $rowcount rows found\n";
            fclose($handle);
        }
        else {
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        return $csv;
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetItemInEBay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $toekn_ebay;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token_ebay)
    {
        $this->token_ebay = $token_ebay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Job Get Item Ebay STATR at '. now());
        try {
           
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/'.$attribute['SKU'],[
                            'headers'=> $header,
                        ]);
            $search_results = json_decode($res->getBody(), true);
            \Log::info('Job Get Item Ebay SUCCESS at '. now());
            return $search_results;
        }
         catch(\Exception $e) {
            \Log::info('Job Get Item Ebay FAIL at '. now());
            //  if($e->getCode() == 404){
            //     $this->createItemsEbay($attribute,$namefile);
            //     $this->step5_2CreateItem($attribute,$namefile);
            // }
        }
        \Log::info('Job Get Item Ebay END at '. now());
    }
}

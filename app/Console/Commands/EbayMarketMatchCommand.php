<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\ebay\EbayMarketMatch;
use App\Product;

class EbayMarketMatchCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @example
     * php artisan command:ebay-market-match --immediate --sku 401-OATMEAL-165X115
     *
     * @var string
     */
    protected $signature = 'command:ebay-market-match {--immediate} {--sku=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        infolog('[EbayMarketMatchCommand] __construct at '. now());
    }
    public function __destruct()
    {
        infolog('[EbayMarketMatchCommand] __destruct at '. now());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $immediate=$this->option('immediate');
        $sku=$this->option('sku');

        $query=Product::join("ebay_details AS ed","ed.product_id","=","products.id")->whereNotNull("ed.listingid");
        if(strlen($sku)>0){
            infolog('[EbayMarketMatchCommand] have a SKU='.$sku.' at '. now());
            $query=$query->where("products.sku",$sku);
        }
        infolog('[EbayMarketMatchCommand] Query at '. now(),$query->toSql());
        $products=$query->get();
        infolog('[EbayMarketMatchCommand] Found '.count($products).' at '. now(),$query->toSql());
        foreach($products as $product){
            if($immediate){
                dispatch_now(new EbayMarketMatch($product));
            }else{
                dispatch(new EbayMarketMatch($product));
            }
        };
    }

}

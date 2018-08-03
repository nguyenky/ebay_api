<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;

use App\Product;
use Illuminate\Support\Facades\Storage;

class BulkInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $ifile=NULL;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Pull down stock that's changed and write it to a file.
     *
     * @return void
     */
    public function writeEbayMipFile()
    {
        $result=false;
        try{
            infolog('[BulkInventory.writeEbayMipFile] Preparing update file... '. now());
            $all=Product::whereNotNull("listingID")->whereRaw("(updated_at > ebayupdated_at OR ebayupdated_at IS NULL)")->get();
            if($all){
                $filename="inventory_".time().".csv";
                $this->ifile=public_path('files/ebay/'.$filename);
                if($fp = fopen($this->ifile, 'w')){
                    fputcsv($fp, ["SKU","Channel ID","List Price","Total Ship to Home Quantity"]);
                    foreach($all as $product){
                        fputcsv($fp, [$product->SKU,"EBAY_AU",$product->listing_price,$product->QTY]);
                    }
                    fclose($fp);
                }
            }
            infolog('[BulkInventory.writeEbayMipFile] SUCCESS at '. now());
            $result=true;
        }catch(\Exception $e) {
            infolog('[BulkInventory.writeEbayMipFile] ERROR updating eBay/MIP ('.$e->getMessage().') at '. now());
        }
        return($result);
    }

    /**
     * Push the eBay MIP file to the MIP server.
     *
     * @return void
     */
    public function pushEbayMipFile()
    {
        $result=false;
        try{
            infolog('[BulkInventory.pushEbayMipFile] Preparing to send file... '. now());

            Storage::disk('sftp')->put('inventory/'.basename($this->ifile), file_get_contents($this->ifile));

            infolog('[BulkInventory.pushEbayMipFile2] SUCCESS at '. now());

            $result=true;
        }catch(\Exception $e) {
            infolog('[BulkInventory.pushEbayMipFile] ERROR updating eBay/MIP ('.$e->getMessage().') at '. now());
        }
        return($result);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        infolog('[BulkInventory] START at '. now());
        if($this->writeEbayMipFile()){
            if($this->pushEbayMipFile()){
                infolog('[BulkInventory] TOTAL SUCCESS at '. now());
            }
        }
        infolog('[BulkInventory] END at '. now());
    }
}

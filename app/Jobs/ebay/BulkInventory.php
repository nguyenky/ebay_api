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
        infolog('[BulkInventory] __construct at '. now());
    }
    public function __destruct()
    {
        infolog('[BulkInventory] __destruct at '. now());
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
            $all=Product::join("ebay_details AS ed","ed.product_id","=","products.id")->whereNotNull("ed.listingid")->whereRaw("(products.updated_at > ed.synced_at OR ed.synced_at IS NULL)")->get();
            $counter=0;
            if($all){
                $filename="inventory_".time().".csv";
                $this->ifile=public_path('files/ebay/'.$filename);
                if($fp = fopen($this->ifile, 'w')){
                    fputcsv($fp, ["SKU","Channel ID","List Price","Total Ship to Home Quantity"]);
                    foreach($all as $product){
                        fputcsv($fp, [$product->sku,"EBAY_AU",$product->listing_price,$product->qty]);
                        $counter++;
                    }
                    fclose($fp);
                }
            }
            infolog('[BulkInventory.writeEbayMipFile] SUCCESSFULLY UPDATE '.$counter.' ITEMS at '. now());
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

            Storage::disk('mip')->put('inventory/'.basename($this->ifile), file_get_contents($this->ifile));

            infolog('[BulkInventory.pushEbayMipFile2] SUCCESS at '. now());

            $result=true;
        }catch(\Exception $e) {
            infolog('[BulkInventory.pushEbayMipFile] ERROR updating eBay/MIP ('.$e->getMessage().') at '. now());
        }
        return($result);
    }

    /**
     * Update the Sync Date so we know what was updated and when.
     *
     * @return void
     */
    public function updateSyncDate()
    {
        $result=false;
        try{
            infolog('[BulkInventory.updateSyncDate] Preparing to update sync date... '. now());
            $sQl="
                UPDATE
                  products p,
                  ebay_details ed
                SET
                  ed.synced_at=NOW()
                WHERE
                  p.id=ed.product_id
                  AND ed.listingid IS NOT NULL
                  AND (p.updated_at > ed.synced_at OR ed.synced_at IS NULL)
                ;
            ";
            DB::connection()->getpdo()->exec($sQl);
            infolog('[BulkInventory.updateSyncDate] SUCCESS at '. now());

            $result=true;
        }catch(\Exception $e) {
            infolog('[BulkInventory.updateSyncDate] ERROR updating sync date ('.$e->getMessage().') at '. now());
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
                if($this->updateSyncDate()){
                    infolog('[BulkInventory] TOTAL SUCCESS at '. now());
                }
            }
        }
        infolog('[BulkInventory] END at '. now());
    }
}

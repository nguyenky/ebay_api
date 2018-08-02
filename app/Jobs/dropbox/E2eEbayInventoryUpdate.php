<?php

namespace App\Jobs\dropbox;

use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class E2eEbayInventoryUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Load stock file into the DB and updated stock levels.
     *
     * @return void
     */
    public function updateSystemDb()
    {
        $result=false;
        try{
            //Truncate the table first
            infolog('[BulkInventory.updateSystemDb] CLEANING Tmp Table '. now());
            DB::table('stock_updates')->truncate();
            infolog('[BulkInventory.updateSystemDb] CLEANED '. now());


            //Load the master stock update to the DB
            $file = public_path('files/MASTER_STOCK_UPDATE_FILE.csv');
            $sQl = "
                LOAD DATA LOCAL INFILE
                    '" . $file . "'
                INTO TABLE
                    stock_updates
                FIELDS TERMINATED BY
                    ','
                LINES TERMINATED BY
                    '\r\n'
                IGNORE 1 LINES
                (
                    sku,
                    qty,
                    incoming,
                    due,
                    discontinued,
                    @created_at,
                    @updated_at
                )
                SET
                    created_at=NOW(),
                    updated_at=NOW()
            ";
            infolog('[BulkInventory.updateSystemDb] LOADING IN STOCK DATA '. now(),$sQl);
            DB::connection()->getpdo()->exec($sQl);
            infolog('[BulkInventory.updateSystemDb] SUCCESSFUL INSERT at '. now());

            infolog('[BulkInventory.updateSystemDb] UPDATING PRODUCT STOCK at '. now());
            $sQl="
                UPDATE
                  products p,
                  stock_updates s
                SET
                  p.QTY=IF(s.discontinued<>'Y',s.qty,0),
                  p.updated_at=NOW()
                WHERE
                  p.SKU=s.sku
                  AND p.QTY<>s.qty
                ;
            ";
            DB::connection()->getpdo()->exec($sQl);
            infolog('[BulkInventory.updateSystemDb] SUCCESS PRODUCT STOCK UPDATE at '. now());
            $result=true;
        }catch(\Exception $e) {
            infolog('[BulkInventory.updateSystemDb] ERROR in bulk inserting data ('.$e->getMessage().') at '. now());
        }
        return($result);
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
            if($all=Product::where("updated_at",">","ebayupdated_at")->get()){
                if($fp = fopen('file.csv', 'w')){
                    fputcsv($fp, ["SKU","Channel ID","List Price","Total Ship to Home Quantity"]);
                    foreach($all as $product){
                        fputcsv($fp, [$product->SKU,"EBAY_AU",$product->list_price,$product->QTY]);
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        infolog('[BulkInventory] START at '. now());
        if($this->updateSystemDb()){
            if($this->updateEbayMip()){
                infolog('[BulkInventory] TOTAL SUCCESS at '. now());
            }
        }
        infolog('[BulkInventory] END at '. now());
    }
}

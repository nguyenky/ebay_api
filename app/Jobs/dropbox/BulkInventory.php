<?php

namespace App\Jobs\dropbox;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class BulkInventory implements ShouldQueue
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        infolog('[BulkInventory] START at '. now());
        try{
            //Truncate the table first
            DB::table('stock_updates')->truncate();


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
            infolog('[BulkInventory] SQL '. now(),$sQl);
            DB::connection()->getpdo()->exec($sQl);
            infolog('[BulkInventory] SUCCESSFUL INSERT at '. now());

            infolog('[BulkInventory] UPDATING PRODUCT STOCK at '. now());
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
            infolog('[BulkInventory] SUCCESS PRODUCT STOCK UPDATE at '. now());
        }catch(\Exception $e) {
            \Log::info('ERROR in bulk inserting data - '.$e->getMessage());
            infolog('[BulkInventory] ERROR in bulk inserting data ('.$e->getMessage().') at '. now());
        }
        infolog('[BulkInventory] END at '. now());
    }
}

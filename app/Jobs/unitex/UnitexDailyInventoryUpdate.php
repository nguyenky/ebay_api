<?php

namespace App\Jobs\unitex;

use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UnitexDailyInventoryUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        infolog('[UnitexDailyInventoryUpdate] __construct at '. now());
    }
    public function __destruct()
    {
        infolog('[UnitexDailyInventoryUpdate] __destruct at '. now());
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
            infolog('[updateSystemDb] CLEANING Tmp Table '. now());
            DB::table('stock_updates')->truncate();
            infolog('[updateSystemDb] CLEANED '. now());


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
            infolog('[updateSystemDb] LOADING IN STOCK DATA '. now(),$sQl);
            DB::connection()->getpdo()->exec($sQl);
            infolog('[updateSystemDb] SUCCESSFUL INSERT at '. now());

            infolog('[updateSystemDb] UPDATING PRODUCT STOCK at '. now());
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
            infolog('[updateSystemDb] SUCCESS PRODUCT STOCK UPDATE at '. now());
            $result=true;
        }catch(\Exception $e) {
            infolog('[updateSystemDb] ERROR in bulk inserting data ('.$e->getMessage().') at '. now());
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
        infolog('[handle] START at '. now());
        if($this->updateSystemDb()){
            infolog('[handle] SUCCESS at '. now());
        }
        infolog('[handle] END at '. now());
    }
}

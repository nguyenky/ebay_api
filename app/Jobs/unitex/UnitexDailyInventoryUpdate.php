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

    #Old public $remote_stock_file="https://drive.google.com/uc?authuser=0&id=1i58qHTIccr9O7g3q8X1oFnlA7h4DcebT&export=download";
    public $remote_stock_file="https://drive.google.com/uc?authuser=0&id=101-5o_jrm3V1W8WoUDo2FM7zLjavPGCd&export=download";
    public $local_stock_file="files/MASTER_STOCK_UPDATE_FILE.csv";

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
     * Download the
     *
     * @return void
     */
    public function downloadDailyStockFile()
    {
        $result=false;
        infolog('[downloadDailyStockFile] START '. now());
        if($data=file_get_contents($this->remote_stock_file)){
            infolog('[downloadDailyStockFile] GOT DATA at '. now());
            if(file_put_contents(public_path($this->local_stock_file),$data)){
                infolog('[downloadDailyStockFile] SAVED DATA at '. now());
                $result=true;
            }else{
                infolog('[downloadDailyStockFile] FAILED TO SAVE DATA at '. now());
            }
        }else{
            infolog('[downloadDailyStockFile] FAILED TO GET DATA at '. now());
        }
        infolog('[downloadDailyStockFile] END '. now());
        return($result);
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
            $file = public_path($this->local_stock_file);
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
                  p.qty=IF(s.discontinued<>'Y',s.qty,0),
                  p.updated_at=NOW()
                WHERE
                  p.sku=s.sku
                  AND p.qty<>s.qty
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
        if($this->downloadDailyStockFile()){
            if($this->updateSystemDb()){
                infolog('[handle] SUCCESS at '. now());
            }
        }
        infolog('[handle] END at '. now());
    }
}

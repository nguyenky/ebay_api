<?php

namespace App\Jobs\bargains;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;
use App\Bargain;

class InventoryPush implements ShouldQueue
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
        infolog('[Bargains.InventoryPush] __construct at '. now());
    }
    public function __destruct()
    {
        infolog('[Bargains.InventoryPush] __destruct at '. now());
    }

    /**
     * pullProductData
     *
     * @return void
     */
    public function pullProductData()
    {
        $result=false;
        infolog('[InventoryPush.pullProductData] START at '. now());
        $result=DB::select(DB::raw("
            SELECT
              p.sku,
              p.qty,
              p.name,
              p.description,
              i.url,
              p.listing_price,
              p.rrp,
              ed.listingid
            FROM
              products AS p
              INNER JOIN ebay_details AS ed ON ed.product_id=p.id
              INNER JOIN (
                SELECT
                    MIN(im.url) url,
                    im.product_id
                FROM
                    images AS im
                GROUP BY
                    im.product_id
              ) AS i ON i.product_id=p.id
            WHERE
              ed.listingid IS NOT NULL
              AND IFNULL(p.rrp,0)>0
        "));
        infolog('[InventoryPush.pullProductData] END at '. now());
        return($result);
    }

    /**
     * pushToBargains
     *
     * @return void
     */
    public function pushToBargains($rows)
    {
        $result=false;
        infolog('[InventoryPush.pushToBargains] START at '. now());
        foreach($rows as $row){
            $b=Bargain::where("remoteid",$row->sku)->first();
            if(!$b){
                $b=new Bargain();
                $b->remoteid=$row->sku;
            }
            $b->feed_id=2;
            $b->user_id=0;
            $b->bargain_type_id=1;
            $b->swimlane_id=($row->qty<1)?0:rand(1,3); //This will disable the item if there is no quantity.
            $b->name=$row->name;
            $b->description=$row->description;
            $b->slug=str_slug($row->name,"-");
            $b->link="https://www.ebay.com.au/itm/".$row->listingid;
            $b->image=NULL;
            $b->currency="AUD";
            $b->price=$row->listing_price;
            $b->rrp=$row->rrp;
            $b->discount=100-(($row->listing_price/$row->rrp)*100);
            $b->saving=$row->rrp-$row->listing_price;
            $b->category="Rugs";
            $b->remote_category="Rugs";
            $b->remote_image="https://app.redeeming.com.au/images/".$row->url;
            $b->approved_at=date("Y-m-d H:i:s");
            $b->notified_at=date("Y-m-d H:i:s");
            $b->save();
            infolog('[InventoryPush.pushToBargains] ID='.$b->id.' at '. now());
        }
        infolog('[InventoryPush.pushToBargains] END at '. now());
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
        if($rows=$this->pullProductData()){
            if($this->pushToBargains($rows)){
                infolog('[handle] TOTAL SUCCESS at '. now());
            }
        }
        infolog('[handle] END at '. now());
    }
}

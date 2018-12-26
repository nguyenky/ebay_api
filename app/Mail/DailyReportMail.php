<?php
namespace App\Mail;

use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use Illuminate\Support\Facades\DB;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $report_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $report_data)
    {
        infolog("[DailyReportMail.__construct] at ".now());
        $this->user = $user;
        $this->report_data = $report_data;
    }
    public function __destruct()
    {
        infolog("[DailyReportMail.__destruct] at ".now());
    }

    static public function uptime() {
        $str   = @file_get_contents('/proc/uptime');
        $num   = floatval($str);
        $secs  = $num % 60;
        $num   = (int)($num / 60);
        $mins  = $num % 60;
        $num   = (int)($num / 60);
        $hours = $num % 24;
        $num   = (int)($num / 24);
        $days  = $num;

        return array(
            "days"  => $days,
            "hours" => $hours,
            "mins"  => $mins,
            "secs"  => $secs
        );
    }

    static public function getProductData() {
        $result=[
            "products_total"=>0,
            "markets"=>[]
        ];

        $result["products_total"]=Product::count();

        $sQl="
            SELECT
              CASE
                WHEN ed.id IS NOT NULL THEN 'eBay'
                ELSE 'No Market'
              END market,
              COUNT(*) items,
              SUM(IF(ed.offerid IS NOT NULL,1,0)) offered,
              SUM(IF(ed.listingid IS NOT NULL,1,0)) listed,
              SUM(IF(ed.listingid IS NOT NULL AND p.qty>0,1,0)) listed_with_qty,
              SUM(IF(ed.error IS NOT NULL,1,0)) errors,
              MIN(ed.synced_at) min_synced_at
            FROM
              products p
              LEFT JOIN ebay_details ed ON p.id=ed.product_id
            GROUP BY
              CASE
                WHEN ed.id IS NOT NULL THEN 'eBay'
                ELSE 'No Market'
              END
        ";
        $result["markets"]=DB::select(DB::raw($sQl));

        return($result);
    }

    static public function getReportData(){
        infolog("[DailyReportMail.getReportData] START at ".now());
        $result=[];

        $pd=self::getProductData();
        $result["products_total"]=$pd["products_total"];
        $result["markets"]=$pd["markets"];

        //System
        exec("uptime", $uptime); //uptime

        //Space Used
        $bytes = disk_free_space(".");
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
        $df=sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];

        //Uptime
        $uptime=self::uptime();

        $result["system"]=[
            "uptime"=>$uptime["days"]."d".$uptime["hours"]."h".$uptime["mins"]."m".$uptime["secs"]."s",
            "disk_used_percent"=>$df,
        ];
        infolog("[DailyReportMail.getReportData] END at ".now());
        return($result);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        infolog("[DailyReportMail.build] START at ".now());

        $title="Bargains Daily Sync Report";

        infolog("[DailyReportMail.build] END at ".now());
        return $this->subject($title)
            ->from('server@redeemable.com.au', "Webserver")
            ->view('emails.internal.daily-report',["title"=>$title, "user"=>$this->user, "report_data"=>$this->report_data]);
    }
}

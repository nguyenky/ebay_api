<?php

namespace App\Http\Controllers\Ebay;

use App\EbayOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportOrdersController extends Controller
{
    const SALES_RECORD_NUMBER = 0 ;
    const USER_ID = 1 ;
    const BUYER_FULLNAME = 2 ;
    const BUYER_PHONE_NUMBER = 3 ;
    const BUYER_EMAIL = 4 ;
    const BUYER_ADDRESS_1 = 5 ;
    const BUYER_ADDRESS_2 = 6 ;
    const BUYER_CITY = 7 ;
    const BUYER_STATE = 8 ;
    const BUYER_POSTCODE = 9 ;
    const BUYER_COUNTRY = 10 ;
    const ORDER_ID = 11 ;
    const ITEM_ID = 12 ;
    const TRANSACTION_ID = 13 ;
    const ITEM_TITLE = 14 ;
    const QUANTITY = 15 ;
    const SALE_PRICE = 16 ;
    const POSTAGE_AND_HANDLING = 17 ;
    const SALES_TAX = 18 ;
    const INSURANCE = 19 ;
    const TOTAL_PRICE = 20 ;
    const PAYMENT_METHOD = 21 ;
    const PAYPAL_TRANSACTION_ID = 22 ;
    const SALE_DATE = 23 ;
    const CHECKOUT_DATE = 24 ;
    const PAID_ON_DATE = 25 ;
    const POSTED_ON_DATE = 26 ;
    const POSTAGE_SERVICE = 27 ;
    const FEEDBACK_LEFT = 28 ;
    const FEEDBACK_RECEIVED = 29 ;
    const NOTES_TO_YOURSELF = 30 ;
    const CUSTOM_LABEL = 31 ;
    const PRIVATE_NOTES = 32 ;
    const PRODUCT_ID_TYPE = 33 ;
    const PRODUCT_ID_VALUE = 34 ;
    const PRODUCT_ID_VALUE_2 = 35 ;
    const VARIATION_DETAILS = 36 ;
    const PRODUCT_REFERENCE_ID = 37 ;
    const GLOBAL_POSTAGE_REFERENCE_ID = 38 ;
    const POST_TO_ADDRESS_1 = 39 ;
    const POST_TO_ADDRESS_2 = 40 ;
    const POST_TO_CITY = 41 ;
    const POST_TO_STATE = 42 ;
    const POST_TO_POSTCODE = 43 ;
    const POST_TO_COUNTRY = 44 ;
    const PHONE = 45 ;

    public function process(Request $request){
        $path=$request->file('file')->store('public/uploads/ebay-orders');
        if($path){
            infolog("YEAH! UPloaded",$path);
            $file = fopen(storage_path("app/".$path), 'r');
            $deets=false;
            $c=0;
            while (($line = fgetcsv($file)) !== FALSE) {
                if((int)$line[self::SALES_RECORD_NUMBER]>0){
                    //if order id, then we need to process differently
                    if((double)$line[self::ORDER_ID]>0){
                        //if order id, then we need to process differently
                        $deets=$c;
                        continue;
                    }else{
                        //otherwise, it's a one-off transaction
                        $deets=$c;
                    }
                    if(EbayOrder::where("transactionid",$line[self::TRANSACTION_ID])->exists()){
                        infolog("Transaction Exists");
                    }
                    $row=[];
                    $row[""]=$line[$c][self::SALES_RECORD_NUMBER];
                }
                $rows[]=$line;
                $c++;
            }
            fclose($file);
        }else{
            return(redirect()->route("ebay.import-orders.index")->with("error","[FATAL ERROR] Could upload the file."));
        }
        return view('tools.generic-import.index',["items"=>[]]);
    }

    public function index(){
        return view('ebay.import-orders.index',["items"=>[]]);
    }
}

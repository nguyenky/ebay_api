<?php

namespace App\Http\Controllers\Ebay;

use App\Jobs\ebay\BulkInventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EbayController extends Controller
{
    public function index(Request $request){
    }

    public function inventorySync(Request $request){
        dispatch_now(new BulkInventory);
    }
}

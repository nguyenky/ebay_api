<?php

namespace App\Http\Controllers\Ebay;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EbayDescriptionController extends Controller
{
    public function index(Request $request){
        $item=false;
        if((int)@$request->id>0){
            $item=Product::find($request->id);
        }
        if(strlen(@$request->itemid)>0){
            $item=Product::where("listingID",$request->itemid)->first();
        }
        if(!$item){
            $item=Product::inRandomOrder()->first();
            $item->Name="[RANDOM]".$item->Name;
        }
        $desc=@$item->Description;
        if(strlen($desc)>0){
            //Get rid of excessive line breaks
            $desc=preg_replace("/\n{3,}/","\n\n",$desc);

            //Convert * to LIs
            $desc = preg_replace("/\*+(.*)?/i","<ul><li>$1</li></ul>",$desc);
            $desc = preg_replace("/(\<\/ul\>\n(.*)\<ul\>*)+/","",$desc);

            //Convert new lines
            $desc=preg_replace("/\n/","<br />",$desc);

            //Convert Special Bolds
            $desc=preg_replace("/([^:<>]+):/","<strong>$1</strong>:",$desc);

            //Get rid BRs before ULs
            $desc=preg_replace("/((<br \/>\s*)+|\s+)<ul/","<ul",$desc);

            $item->Description=$desc;
        }
        return(view("ebay.description",[
            "item"=>$item
        ]));
    }
}

<?php

namespace App\Http\Controllers\Ebay;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EbayDescriptionController extends Controller
{
    public function index(Request $request){
        $item=false;
        $images=false;
        $p=Product::select(DB::raw("products.*"))->leftJoin("ebay_details AS ed","ed.product_id","=","products.id");
        if((int)@$request->id>0){
            $item=$p->where("products.id",$request->id)->first();
            if(!$item){
                dd("Item Not Found",$p->toSql());
            }
        }
        if(strlen(@$request->itemid)>0){
            $item=$p->where("listingid",$request->itemid)->first();
        }
        if(!$item){
            $item=$p->inRandomOrder()->first();
            $item->name="[RANDOM]".$item->name;
        }
        $desc=@$item->description;
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

            $item->description=$desc;
        }

        if($item){
            $images=$item->getImagesArray();
        }
        return(view("ebay.description",[
            "item"=>$item,
            "images"=>$images
        ]));
    }
}

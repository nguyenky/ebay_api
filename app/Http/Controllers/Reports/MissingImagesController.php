<?php

namespace App\Http\Controllers\Reports;

use App\Image;
use App\Product;
use App\System;
use App\Http\Controllers\Controller;

class MissingImagesController extends Controller
{
    public $shopify_url="https://d51d10f0cb176de247e68f0da7c7a8eb:9bbe494c556ca81e5c0f0cd45451f92a@unitex-international.myshopify.com/admin/products.json?page=70";
    public $found=[];
    public $normal_files=[];
    public $not_found=[];
    public $not_found_fixed=[];
    public $do_fix=true;

    public function convertToArray($attribute){
        $csv = Array();
        $rowcount = 0;
        $file =  public_path($attribute);
        if (($handle = fopen($file, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $entry;
                }
                else {
                    return null;
                }
                $rowcount++;
            }
            fclose($handle);
        }
        else {
            error_log("csvreader: Could not read CSV \"$file\"");
            return null;
        }
        return $csv;
    }

    public function findImageInArray($img,$ar){
        $result=false;
        if(strlen($img)>0){
            if(in_array($img,$ar)){
                $result=true;
                $this->found[]=$img;
            }else{
                $this->not_found[]=$img;
                $norm=preg_replace("/[^a-zA-Z0-9\.]/","",$img);
                if(in_array($norm,array_keys($this->normal_files))){
                    $this->not_found_fixed[$img]="Can fix to $norm";
                    if($this->do_fix){
                        copy(public_path("images/".$this->normal_files[$norm]),public_path("images/".$img));
                        infolog("Fixed ".public_path("images/".$this->normal_files[$norm])." to ".public_path("images/".$img));
                    }
                }
            }
        }else{
            $result=true;
        }
        return($result);
    }

    public function getUnitexMissingImagesReport(){
        $result=[];

        $system=System::first();
        $csv=$this->convertToArray('files/'.$system->filecsv);
        if(!$csv){
            infolog("Error reading the CSV file: ".$system->filecsv);
            dd("Error");
        }

        $files=[];
        foreach (glob(public_path("images/")."*") as $filename) {
            $files[]=basename($filename);
            $this->normal_files[preg_replace("/[^a-zA-Z0-9\.]/","",basename($filename))]=basename($filename);
        }
        if(!$files){
            infolog("Error finding ANY image files: ".public_path("images/"));
            dd("Error");
        }

        foreach($csv as $row){
            $this->findImageInArray($row["Image1"],$files);
            $this->findImageInArray($row["Image2"],$files);
            $this->findImageInArray($row["Image3"],$files);
            $this->findImageInArray($row["Image4"],$files);
            $this->findImageInArray($row["Image5"],$files);
        }

        return($result);
    }

    public function generateImagesPercentages(){
        $all=Product::paginate(250);
        foreach($all as $product){
            if($per=$product->calculateImagePercent(true)){
                infolog($product->SKU." has ".$per."% images found.");
            }else{
                infolog($product->SKU." has an error.");
            }
        }
    }

    public function tryFindImages(){
        $all=Product::where("images_percent","<",100)->whereNotNull("listingID")->paginate(request("ps",500));
        $fixed=0;
        foreach($all as $product){
            $sku=$product->SKU;
            if(strrpos($sku,"-")>0){
                $prefix=substr($sku,0,strrpos($sku,"-"));
                $images=[];
                if(file_exists(public_path("images/".$prefix.".jpg"))){
                    $images[]=$prefix.".jpg";
                }
                for($c=1;$c<7;$c++){
                    if(file_exists(public_path("images/".$prefix."-$c.jpg"))){
                        $images[]=$prefix."-1.jpg";
                    }elseif(file_exists(public_path("images/".$prefix."_$c.jpg"))){
                        $images[]=$prefix."_$c.jpg";
                    }
                }
                infolog("$sku Found ".count($images)." images.");
                if($images){
                    $fixed++;
                }
            }
        }
        infolog("Fixed $fixed listings.");
    }

    public function checkUnitexBySku($sku){
        $web_path="https://b2b.unitexint.com/productimages/";
        $suffixes=["","_1","_2","_3","_4"];
        $found=0;
        if(strrpos($sku,"-")>0){
            $prefix=substr($sku,0,strrpos($sku,"-"));
            $img_c=0;
            foreach($suffixes as $suffix){
                if(file_exists(public_path("images/".$prefix.$suffix.".jpg"))){
                    infolog("Image Found: images/".$prefix.$suffix.".jpg");
                    infolog("- Found.");
                    $found++;
                    $img_c++;
                    continue;
                }
                infolog("Checking: ".$web_path.$prefix.$suffix.".jpg");
                if($img=@file_get_contents($web_path.$prefix.$suffix.".jpg")){
                    infolog("- Found.");
                    $found++;
                    if(@file_put_contents(public_path("images/".$prefix.$suffix.".jpg"),$img)){
                        $img_c++;
                    }else{
                        infolog("--- Couldn't put file: ".public_path("images/".$prefix.$suffix.".jpg").".");
                    }
                }
            }
            for($c=$img_c+1;$c<=5;$c++){
                infolog("- NOT Found $img.");
            }
        }
    }

    public function relateImages(){
        $all=Image::paginate(request("ps",5000));
        infolog("Images:",count($all));
        $verified=0;
        $errors=0;
        foreach($all as $image){
            if(file_exists(public_path("images/".$image->url))){
                infolog(" - Found: images/".$image->url);
                $image->valid=1;
                if($image->save()){
                    $verified++;
                    infolog(" - Saved");
                }else{
                    $errors++;
                    infolog(" - Error Saving!!!");
                }
            }
        }
        infolog("Verified $verified images.");
        infolog("Had $errors errors.");
    }

    public function saveUnitexImage($image){
        $result=false;
        $web_path="https://b2b.unitexint.com/productimages/";
        infolog("[saveUnitexImage] Checking Image: ".$web_path.$image);
        if($data=@file_get_contents($web_path.$image)){
            infolog("[saveUnitexImage] FOUND! Saving...");
            if(file_put_contents(public_path("images/".$image),$data)){
                infolog("[saveUnitexImage] SAVED!");
                $result=$image;
            }else{
                infolog("[saveUnitexImage] ERROR! Could not save image!");
            }
        }else{
            infolog("[saveUnitexImage] Could NOT find image.");
        }
        return($result);
    }

    public function checkUnitexMultipleWaysByImage($image){
        $result=false;

        //Let's check the way it is.
        if($this->saveUnitexImage($image)){
            $result=$image;
        }
        if(!$result && preg_match("/.*\-(\d)\.jpg/i",$image,$matches)){ //We have a dash-number, so let's try with an underscore.
            $img=substr($image,0,-6)."_".substr($image,-5);
            if($this->saveUnitexImage($img)){
                $result=$img;
            }
        }
        if(!$result && preg_match("/.*_(\d)\.jpg/i",$image,$matches)){ //We have a underscore-number, so let's try with a dash.
            $img=substr($image,0,-6)."-".substr($image,-5);
            if($this->saveUnitexImage($img)){
                $result=$img;
            }
        }
        return($result);
    }

    public function index(){
        $report=false;

        $fixed=0;

        $page=request("page",0);
        $page=($page<1)?1:$page;
        print("<p><a href='?page=".($page+1)."'>NEXT PAGE &gt;</a></p>");

        $images=Image::where("valid",0)->orderBy("id","DESC");
        $total=$images->count();
        print("<p>There are ".number_format($total,0)." images that are not valid.</p>");
        $images=$images->paginate(20);
        foreach($images as $image){
            infolog("Image URL: ".$image->url);
            if($image->exists()){
                infolog(" - Exists Already!");
                $image->valid=1;
                if($image->save()){
                    infolog("    - Image Fixed!");
                    $fixed++;
                }
            }else{
                $is_found=false;
                infolog(" - Not Found.");
                $url=$image->url;
                if(substr($url,-6,1)=="-"){
                    infolog("  - Has dash, trying underscore.");
                    $u=substr($url,0,-6)."_".substr($url,-5);
                    if($image->exists($u)){
                        infolog("   - Image Found!",$u);
                        $image->url=$u;
                        $image->valid=1;
                        if($image->save()){
                            infolog("    - Image Fixed!");
                            $is_found=true;
                            $fixed++;
                        }
                    }else{
                        infolog("   - Image Still Not Found!",$u);
                    }
                }
                if(!$is_found && substr($url,-8,2)=="-R"){
                    infolog("  - Trying to RU Fix.");
                    $u=substr($url,0,-8)."-RU".substr($url,-4);
                    if($image->exists($u)){
                        infolog("   - Image Found!",$u);
                        $image->url=$u;
                        $image->valid=1;
                        if($image->save()){
                            infolog("    - Image Fixed!");
                            $is_found=true;
                            $fixed++;
                        }
                    }else{
                        infolog("   - Image Still Not Found!",$u);
                    }
                }

                if(!$is_found){
                    infolog("  - Trying to find image on Unitex.");
                    if($u=$this->checkUnitexMultipleWaysByImage($image->url)){
                        $image->url=$u;
                        $image->valid=1;
                        if($image->save()){
                            infolog("    - Image Fixed!");
                            $is_found=true;
                            $fixed++;
                        }
                    }
                }
            }
        }
        dd("Finished with $fixed images fixed");

        return view('reports.missing-images',[
            "report"=>$report
        ]);
    }
}

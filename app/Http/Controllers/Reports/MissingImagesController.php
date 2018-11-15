<?php

namespace App\Http\Controllers\Reports;

use App\Product;
use App\System;
use App\Http\Controllers\Controller;

class MissingImagesController extends Controller
{
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

    public function bulletProofFix(){
        $web_path="https://b2b.unitexint.com/productimages/";
        $all=Product::where("images_percent","<",100)->whereNull("ebayupdated_at")->where("QTY",">",0)->orderBy("SKU")->paginate(request("ps",5));
        $suffixes=["","_1","_2","_3","_4"];
        $found=0;
        $fixed=0;
        foreach($all as $product){
            $sku=$product->SKU;
            if(strrpos($sku,"-")>0){
                $prefix=substr($sku,0,strrpos($sku,"-"));
                $images=[];
                $img_c=0;
                foreach($suffixes as $suffix){
                    infolog("Checking: ".$web_path.$prefix.$suffix.".jpg");
                    if($img=@file_get_contents($web_path.$prefix.$suffix.".jpg")){
                        infolog("- Found.");
                        $found++;
                        if(@file_put_contents(public_path("images/".$prefix.$suffix.".jpg"),$img)){
                            $img_c++;
                            $img="Image".$img_c;
                            $product->$img=$prefix.$suffix.".jpg";
                        }else{
                            infolog("--- Couldn't put file: ".public_path("images/".$prefix.$suffix.".jpg").".");
                        }
                    }
                }
                for($c=$img_c+1;$c<=5;$c++){
                    $img="Image".$c;
                    infolog("- NOT Found $img.");
                    $product->$img=NULL;
                }
                if($product->save()){
                    $product->images_percent=100;
                    $product->save();
                    $fixed++;
                    infolog("-- Saved.");
                }
            }
        }
        infolog("Found $found images.");
        infolog("Fixed $fixed listings.");
    }

    public function tmp2(){
        $results=[];
        $str="Highly positive impact
-
Highly positive impact
Somewhat positive impact
Highly positive impact
Highly positive impact
Somewhat positive impact
Somewhat positive impact
-
Somewhat positive impact
-
Highly positive impact
Highly positive impact
Somewhat positive impact
Somewhat positive impact
Somewhat positive impact
Highly positive impact
Somewhat positive impact
-
Highly positive impact
Highly positive impact
Highly positive impact
Highly positive impact
Somewhat positive impact
Highly positive impact
Highly positive impact
Somewhat positive impact
Highly positive impact
Somewhat positive impact
Highly positive impact
Highly positive impact
Somewhat positive impact
Highly positive impact
Highly positive impact
Somewhat positive impact
";
        $ar=preg_split("/\r\n/",trim($str));
        $counter=0;
        foreach($ar as $entry){
            $a=trim($entry);
            if(strlen($entry)>0) {
                if (!in_array($a, array_keys($results))) {
                    $results[$a] = 0;
                }
                $results[$a]++;
                $counter++;
            }
        }
        infolog("COUNTER: $counter");
        foreach($results as $a=>$c){
            infolog("$a: $c / ".round(($c/$counter)*100,1)."%");
        }
    }

    public function tmp(){
        $results=[];
        $str="Meaning and purpose=Highly positive impact, Stress levels=Somewhat positive impact, Promotability=Highly positive impact, Remuneration=No impact, Productivity=No impact, Professional reputation=Somewhat positive impact
-
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=Somewhat positive impact, Productivity=Highly positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Highly positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Highly positive impact, Stress levels=No impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=No impact, Professional reputation=No impact
-
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
-
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Highly positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=No impact, Remuneration=Somewhat positive impact, Productivity=No impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=No impact, Stress levels=Highly negative impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=No impact, Professional reputation=No impact
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Highly positive impact, Professional reputation=Highly positive impact
Meaning and purpose=No impact, Stress levels=No impact, Promotability=No impact, Remuneration=No impact, Productivity=No impact, Professional reputation=No impact
-
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=No impact, Professional reputation=Highly positive impact
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Highly positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=No impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=No impact
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Somewhat positive impact, Remuneration=Somewhat positive impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=No impact
Meaning and purpose=No impact, Stress levels=Somewhat negative impact, Promotability=No impact, Remuneration=No impact, Productivity=No impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Highly positive impact, Remuneration=Highly positive impact, Productivity=Somewhat positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=Somewhat positive impact, Productivity=Somewhat positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=No impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Somewhat positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Highly positive impact, Stress levels=Highly positive impact, Promotability=Highly positive impact, Remuneration=Somewhat positive impact, Productivity=Highly positive impact, Professional reputation=Highly positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=No impact, Remuneration=No impact, Productivity=No impact, Professional reputation=No impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat negative impact, Promotability=Highly positive impact, Remuneration=No impact, Productivity=No impact, Professional reputation=Highly positive impact
Meaning and purpose=Highly positive impact, Stress levels=Somewhat positive impact, Promotability=Somewhat positive impact, Remuneration=No impact, Productivity=Highly positive impact, Professional reputation=Somewhat positive impact
Meaning and purpose=Somewhat positive impact, Stress levels=Somewhat positive impact, Promotability=No impact, Remuneration=No impact, Productivity=No impact, Professional reputation=Somewhat positive impact
";
        $ar=preg_split("/\r\n/",trim($str));
        foreach($ar as $entry){
            $qs=preg_split("/,/",trim($entry));
            foreach($qs as $qa){
                if(strpos($qa,"=")>0){
                    $qanda=preg_split("/=/",trim($qa));
                    $q=trim($qanda[0]);
                    $a=trim($qanda[1]);
                    if(!in_array($q,array_keys($results))){
                        $results[$q]=[];
                    }
                    if(!in_array($a,array_keys($results[$q]))){
                        $results[$q][$a]=0;
                    }
                    $results[$q][$a]++;
                }else{
                    infolog("Not found: $qa.");
                }
            }
        }
        foreach($results as $q=>$as){
            infolog("$q");
            $counter=0;
            foreach($as as $a=>$num){
                $counter += (int)$num;
            }
            $results[$q]["counter"]=$counter;
        }
        foreach($results as $q=>$as){
            infolog("$q");
            foreach($as as $a=>$num){
                if($a!="counter"){
                    infolog(" - $a: $num/".round($num/$results[$q]["counter"]*100,1)."%");
                }
            }
        }
        dd("Finished",$results);
    }

    public function index(){
        $report=false;
        $this->getUnitexMissingImagesReport();

        infolog("Found: ".count($this->found));
        infolog("Not Found: ".count($this->not_found));
        infolog("Can Fix: ".count($this->not_found_fixed));
        dump("Not Found",$this->not_found);
        dd("Finished");

        return view('reports.missing-images',[
            "report"=>$report
        ]);
    }
}

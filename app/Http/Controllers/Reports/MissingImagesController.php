<?php

namespace App\Http\Controllers\Reports;

use App\Product;
use App\System;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class MissingImagesController extends Controller
{
    public $found=[];
    public $not_found=[];

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
        }
        if(!$files){
            infolog("Error finding ANY image files: ".public_path("images/"));
            dd("Error");
        }

        infolog("Files:",$files);

        foreach($csv as $row){
            $this->findImageInArray($row["Image1"],$files);
            $this->findImageInArray($row["Image2"],$files);
            $this->findImageInArray($row["Image3"],$files);
            $this->findImageInArray($row["Image4"],$files);
            $this->findImageInArray($row["Image5"],$files);
        }

        return($result);
    }

    public function index(){
        $report=$this->getUnitexMissingImagesReport();

        infolog("Found: ".count($this->found));
        infolog("Not Found: ".count($this->not_found));
        dd("Die");

    	return view('reports.missing-images',[
    	    "report"=>$report
        ]);
    }
}

<?php

namespace App\Http\Controllers\ModeTest;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\System;
class ModeTestController extends Controller
{
    public function index(){
    	$system = System::first();
    	$products = \App\Product::where('product_mode_test',1)->get();
    	return view('mode_test.mode_test',[
    		'system'=>$system,
    		'items'=>$products
    		]);
    }

    public function update(Request $request){
        $ids=$request->check;
        Product::whereIn("id",$ids)->where("product_mode_test",1)->update(["product_mode_test"=>0]);
    	return redirect()->route('mode-test');
    }

    public function updateSystemTest(Request $request){
    	$system = System::first();
    	$system->mode_test = (int)$request->mode_test;
    	$system->save();
    	return redirect()->route('mode-test');
    }

    public function create(Request $request){
    	$system = \App\System::find(1);

        $csv = $this->convertToArray('files/'.$system->filecsv,$request->sku);
        if(!count($csv)){
        	$request->session()->flash('status','Sku not found !!!!');    
        	return redirect()->route('mode-test'); 
        }

        $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;

        // dd($csv);
        foreach ($csv as $key => $value) {
       	 $find = \App\Product::where('SKU',$value['SKU'])->where('product_mode_test',1)->first();
            if(!$find){
                $desc=preg_replace($regex, '$1', $value['Description']);
                $product = \App\Product::create([
                    'SKU'=> $value['SKU'],
                    'Name'=> $value['Name'],
                    'Description'=>$desc,
                    'Category'=>$value['Category'],
                    'Size'=>$value['Size'],
                    'Color'=>$value['Color'],
                    'Cost'=>$value['Cost (Ex.GST) '],
                    'Sell'=>$value['Sell'],
                    'RRP'=>$value['RRP'],
                    'QTY'=>$value['QTY'],
                    'Image1'=>$value['Image1'],
                    'Image2'=>$value['Image2'],
                    'Image3'=>$value['Image3'],
                    'Image4'=>$value['Image4'],
                    'Image5'=>$value['Image5'],
                    'Length'=>$value['Length'],
                    'Width'=>$value['Width'],
                    'Height'=>$value['Height'],
                    'UnitWeight'=>$value['UnitWeight'],
                    'Origin'=>$value['Origin'],
                    'Construction'=>$value['Construction'],
                    'Material'=>$value['Material'],
                    'Pileheight'=>$value['Pileheight'],
                    'product_mode_test'=>1
                ]);
                $product->setListingPrice();
            }else{
            	$request->session()->flash('status','Sku alredy !!!!');
            }
        }
        return redirect()->route('mode-test');
    }
    public function convertToArray($attribute,$sku){
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
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        $filtered = collect($csv)->filter(function ($value, $key) use($sku) {
            // return $value['SKU'] == '401-OATMEAL-165X115' || $value['SKU'] == '871-LATTE-300X80';
            // return $value['SKU'] == '401-RED-165X115' || $value['SKU'] == '401-RED-225X155';
            return $value['SKU'] == $sku;
        });


        return $filtered->all();
    }
    public function delete($id){
    	$product = \App\Product::find($id)->delete();
    	return redirect()->route('mode-test');
    }
}

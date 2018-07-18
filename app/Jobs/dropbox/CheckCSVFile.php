<?php

namespace App\Jobs\dropbox;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckCSVFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $system = \App\System::find(1);

        $csv = $this->convertToArray('files/'.$system->filecsv);
        if($csv){
            foreach ($csv as $key => $value) {
                $find = \App\Product::where('SKU',$value['SKU'])->first();
                if(!$find){
                    $product = \App\Product::create([
                        'SKU'=> $value['SKU'],
                        'Name'=> $value['Name'],
                        'Description'=>$value['Description'],
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
                        'Pileheight'=>$value['Pileheight']
                    ]);
                }else{
                    if( 
                        $find->SKU != $value['SKU'] ||
                        $find->Name != $value['Name'] ||
                        $find->Description != $value['Description'] ||
                        $find->Category != $value['Category'] ||
                        $find->Size != $value['Size'] ||
                        $find->Color != $value['Color'] ||
                        $find->Cost != $value['Cost (Ex.GST) '] ||
                        $find->Sell != $value['Sell'] ||
                        $find->RRP != $value['RRP'] ||
                        $find->QTY != $value['QTY'] ||
                        $find->Image1 != $value['Image1'] ||
                        $find->Image2 != $value['Image2'] ||
                        $find->Image3 != $value['Image3'] ||
                        $find->Image4 != $value['Image4'] ||
                        $find->Image5 != $value['Image5'] ||
                        $find->Length != $value['Length'] ||
                        $find->Width != $value['Width'] ||
                        $find->Height != $value['Height'] ||
                        $find->UnitWeight != $value['UnitWeight'] ||
                        $find->Origin != $value['Origin'] ||
                        $find->Construction != $value['Construction'] ||
                        $find->Material != $value['Material'] ||
                        $find->Pileheight != $value['Pileheight']
                    ){
                        $find->SKU = $value['SKU'];
                        $find->Name = $value['Name'];
                        $find->Description = $value['Description'];
                        $find->Category = $value['Category'];
                        $find->Size = $value['Size'];
                        $find->Color = $value['Color'];
                        $find->Cost = $value['Cost (Ex.GST) '];
                        $find->Sell = $value['Sell'];
                        $find->RRP = $value['RRP'];
                        $find->QTY = $value['QTY'];
                        $find->Image1 = $value['Image1'];
                        $find->Image2 = $value['Image2'];
                        $find->Image3 = $value['Image3'];
                        $find->Image4 = $value['Image4'];
                        $find->Image5 = $value['Image5'];
                        $find->Length = $value['Length'];
                        $find->Width = $value['Width'];
                        $find->Height = $value['Height'];
                        $find->UnitWeight = $value['UnitWeight'];
                        $find->Origin = $value['Origin'];
                        $find->Construction = $value['Construction'];
                        $find->Material = $value['Material'];
                        $find->Pileheight = $value['Pileheight'];
                        $find->save();
                        dispatch(new \App\Jobs\ebay\UpdateEbay($find));
                    }
                }
                
            }
        }
    }
    
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
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        $filtered = collect($csv)->filter(function ($value, $key) {
            // return $value['SKU'] == '401-OATMEAL-165X115' || $value['SKU'] == '871-LATTE-300X80';
            return $value['SKU'] == '401-RED-165X115' || $value['SKU'] == '401-RED-225X155';
        });


        return $filtered->all();
    }
}

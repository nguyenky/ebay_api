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

        infolog('Start Check CSV File !');

        if($system->mode_test){

            infolog('Mode test !!');

            $this->modeTest($csv);

        }else{
            infolog('Mode live !!');

            $this->modeLive($csv);

        }
        infolog('End Check CSV File !');


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
            error_log("csvreader: Could not read CSV \"$file\"");
            return null;
        }
        return $csv;
    }

    public function modeTest($csv){

        $productsTest = \App\Product::where('product_mode_test',1)->get();

        foreach ($productsTest as $key_product => $productTest) {

            foreach ($csv as $key_csv => $value) {
                $find = \App\Product::where('SKU',$value['SKU'])->where('product_mode_test',1)->first();
                if($find){
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
    public function modeLive($csv){

        if($csv){
            foreach ($csv as $key => $value) {

                infolog('Foreach '.$key);

                $find = \App\Product::where('SKU',$value['SKU'])->where('product_mode_test',0)->first();

                $value['product_mode_test'] = 0;

                if(!$find){

                    dispatch(new \App\Jobs\dropbox\SaveProduct($value));
                    
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
                        $find->Image1 != str_replace("-","_",$this->product['Image1']) ||
                        $find->Image2 != str_replace("-","_",$this->product['Image2']) ||
                        $find->Image3 != str_replace("-","_",$this->product['Image3']) ||
                        $find->Image4 != str_replace("-","_",$this->product['Image4']) ||
                        $find->Image5 != str_replace("-","_",$this->product['Image5']) ||
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
                        $find->Image1 = str_replace("-","_",$this->product['Image1']);
                        $find->Image2 = str_replace("-","_",$this->product['Image2']);
                        $find->Image3 = str_replace("-","_",$this->product['Image3']);
                        $find->Image4 = str_replace("-","_",$this->product['Image4']);
                        $find->Image5 = str_replace("-","_",$this->product['Image5']);
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
}

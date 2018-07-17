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
                // if(!$find){
                //     $product = \App\Product::create([

                //     ]);
                // }
                
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
            return $value['SKU'] == '401-OATMEAL-165X115' || $value['SKU'] == '871-LATTE-300X80' ;
        });


        return $filtered->all();
    }
}

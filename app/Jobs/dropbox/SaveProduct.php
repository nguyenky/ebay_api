<?php

namespace App\Jobs\dropbox;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product;
class SaveProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $product;
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $product = Product::create([
                'SKU'=> $this->product['SKU'],
                'Name'=> $this->product['Name'],
                'Description'=>$this->product['Description'],
                'Category'=>$this->product['Category'],
                'Size'=>$this->product['Size'],
                'Color'=>$this->product['Color'],
                'Cost'=>$this->product['Cost (Ex.GST) '],
                'Sell'=>$this->product['Sell'],
                'RRP'=>$this->product['RRP'],
                'QTY'=>$this->product['QTY'],
                'Image1'=>$this->product['Image1'],
                'Image2'=>$this->product['Image2'],
                'Image3'=>$this->product['Image3'],
                'Image4'=>$this->product['Image4'],
                'Image5'=>$this->product['Image5'],
                'Length'=>$this->product['Length'],
                'Width'=>$this->product['Width'],
                'Height'=>$this->product['Height'],
                'UnitWeight'=>$this->product['UnitWeight'],
                'Origin'=>$this->product['Origin'],
                'Construction'=>$this->product['Construction'],
                'Material'=>$this->product['Material'],
                'Pileheight'=>$this->product['Pileheight'],
                'product_mode_test'=>$this->product['product_mode_test']
            ]);
        }catch(\Exception $e) {

            \Log::info('ERROR search file csv - '.$e->getMessage());

        } 
    }
}

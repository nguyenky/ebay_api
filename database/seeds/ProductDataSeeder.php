<?php

use Illuminate\Database\Seeder;
use App\Product;

class ProductDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $product = Product::where('SKU','401-RED-165X115')->where('product_mode_test',0)->first();
        // $data = $product;
        Product::truncate();
        // dd($data);
        // Product::create($data);
    }
}

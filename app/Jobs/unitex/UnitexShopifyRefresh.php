<?php

namespace App\Jobs\unitex;

use App\Image;
use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UnitexShopifyRefresh implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $shopifyProduct=NULL;
    private $shopifyVariant=NULL;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($variant, $product)
    {
        infolog('[UnitexShopifyRefresh] __construct at '. now());

        $this->shopifyVariant=$variant;
        $this->shopifyProduct=$product;
    }
    public function __destruct()
    {
        infolog('[UnitexShopifyRefresh] __destruct at '. now());
    }

    static public function pullAndParseFeed($page=1)
    {
        $result=false;
        infolog('[UnitexShopifyRefresh.pullAndParseFeed] START at '. now());

        $feed=config("SHOPIFY_PRODUCT_FEED","https://d51d10f0cb176de247e68f0da7c7a8eb:9bbe494c556ca81e5c0f0cd45451f92a@unitex-international.myshopify.com/admin/products.json");
        if($feed){
            $feed.="?page=$page";

            infolog('[UnitexShopifyRefresh.pullAndParseFeed] Pulling Shopify Product Feed at '. now(),$feed);

            if($data=@file_get_contents($feed)){
                if($json=json_decode($data,true)){
                    if(array_key_exists("products",$json) && is_array($json["products"])){
                        if(count($json["products"])>0){
                            $result=$json["products"];
                        }else{
                            infolog('[UnitexShopifyRefresh.pullAndParseFeed] ERROR: "products" is an empty array at '. now());
                        }
                    }else{
                        infolog('[UnitexShopifyRefresh.pullAndParseFeed] ERROR: "products" key not present or is not an Array in JSON at '. now(),$json);
                    }
                }else{
                    infolog('[UnitexShopifyRefresh.pullAndParseFeed] ERROR: Unable to parse feed to JSON at '. now(),$data);
                }
            }else{
                infolog('[UnitexShopifyRefresh.pullAndParseFeed] ERROR: Unable to read feed '. now(),$feed);
            }
        }else{
            infolog('[UnitexShopifyRefresh.pullAndParseFeed] ERROR: Feed URL not found at '. now());
        }
        infolog('[UnitexShopifyRefresh.pullAndParseFeed] END at '. now());
        return($result);
    }

    /**
     * Check the product exists and warn if there's an Qty difference.
     *
     * @return void
     */
    public function productSkuChecks()
    {
        $result=false;
        infolog('[productSkuChecks] START at '. now());
        $v=$this->shopifyVariant;
        $product=Product::where("sku",$v["sku"])->first();
        if($product){
            if($product->qty<>$v["inventory_quantity"]){
                dump_warn('[productSkuChecks] WARNING: QTY Differences: Local='.$product->qty.'<>'.$v["inventory_quantity"].'! at '. now(),$v["sku"]);
            }
            infolog('[productSkuChecks] SUCCESS at '. now());
            $result=$product;
        }elseif($v["inventory_quantity"]>0){
            dump_err('[productSkuChecks] ERROR: SKU NOT FOUND: '.$v["sku"].' and has QTY='.$v["inventory_quantity"].'! at '. now());
        }else{
            dump_warn('[productSkuChecks] WARNING: SKU Not found, but has no Qty anyway! at '. now(),$v["sku"]);
        }
        infolog('[productSkuChecks] END at '. now());
        return($result);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function deleteExistingImages(Product $product)
    {
        $result=false;
        infolog('[deleteExistingImages] START at '. now());

        $images=Image::where("product_id",$product->id)->get();
        if(count($images)>0){
            foreach($images as $image){
                $url=$image->url;
                if($image->delete()){
                    infolog('[deleteExistingImages] DELETED image: '.$url.' at '. now());
                }else{
                    dump_warn('[deleteExistingImages] ERROR: could not delete image: '.$url.' at '. now());
                }
            }
            $result=true;
        }else{
            dump_warn('[productSkuChecks] WARNING: No existing images to delete! at '. now(),$product->sku);
        }
        infolog('[deleteExistingImages] END at '. now());
        return($result);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function downloadAndRefreshImages(Product $product)
    {
        $result=false;
        infolog('[downloadAndRefreshImages] START at '. now());
        $sp=$this->shopifyProduct;

        $this->deleteExistingImages($product);

        if(array_key_exists("images",$sp) && is_array($sp["images"]) && count($sp["images"])>0){
            $success=0;
            $errors=0;
            foreach($sp["images"] as $img){
                $opts = [
                    "http" => [
                        "method" => "GET",
                        "header" =>
                            "Accept-language: en-US\r\n" .
                            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36\r\n" .
                            "Pragma: no-cache\r\n" .
                            "Accept-Encoding: gzip, deflate, br\r\n"
                    ]
                ];
                $context = stream_context_create($opts);
                if($data=file_get_contents($img["src"], false, $context)){
                    $imageName=$product->sku."-".$img["position"]."jpg";
                    if(@file_put_contents(public_path("images/".$imageName),$data)){
                        $image=new Image();
                        $image->product_id=$product->id;
                        $image->url=$imageName;
                        $image->valid=1;
                        if($image->save()){
                            $success++;
                        }else{
                            dump_err('[productSkuChecks] ERROR: Could not store the shopify image in the database! at '. now());
                            $errors++;
                        }
                    }else{
                        dump_err('[productSkuChecks] ERROR: Could not save shopify image! at '. now(),public_path("images/".$imageName));
                        $errors++;
                    }
                }else{
                    dump_err('[productSkuChecks] ERROR: Could not pull shopify image! at '. now(),$img["src"]);
                    $errors++;
                }
            }
            if($success>0 && $errors>0){
                dump_warn('[productSkuChecks] WARNING: Refreshed '.$success.' shopify images, but experienced errors with the other '.$errors.' images! at '. now(),$sv["sku"]);
                $result=$success;
            }elseif($success>0){
                infolog('[downloadAndRefreshImages] SUCCESS: Refreshed ALL shopify images! at '. now());
                $result=$success;
            }else{
                dump_err('[downloadAndRefreshImages] ERROR: Could not save ANY images from shopify! at '. now());
            }
        }else{
            dump_err('[productSkuChecks] ERROR: No Shopify Images Found! at '. now(),$sp);
        }
        infolog('[downloadAndRefreshImages] END at '. now());
        return($result);
    }

    /**
     * Check that the SKU has at least one valid image.
     *
     * @return void
     */
    public function imageSkuChecks(Product $product, $fix=true)
    {
        $result=false;
        infolog('[imageSkuChecks] START at '. now());
        $sv=$this->shopifyVariant;
        $sp=$this->shopifyProduct;
        $images=Image::where("product_id",$product->id)->get();
        if(count($images)>0){
            $good=0;
            foreach($images as $image){
                if($image->valid){
                    $good++;
                }else{
                    dump_warn('[productSkuChecks] WARNING: Invalid image found: '.$image->url.'! at '. now(),$sv["sku"]);
                }
            }
            if($good>0){
                if(array_key_exists("images",$sp) && is_array($sp["images"]) && count($sp["images"])>$good){
                    infolog('[imageSkuChecks] WARNING: Shopify has more images!!! Local='.$good.', Shopify='.count($sp["images"]).' at '. now());
                }else{
                    infolog('[imageSkuChecks] SUCCESS: Found '.$good.' images for '.$sv["sku"].' at '. now());
                }
                $result=true;
            }else{
                dump_err('[imageSkuChecks] ERROR: None of the images are valid! '.$sv["sku"].' (product_id='.$product->id.')! at '. now());
                if($fix){
                    if($this->downloadAndRefreshImages($product)){
                        return($this->imageSkuChecks($product, false));
                    }
                }
            }
        }else{
            dump_err('[imageSkuChecks] ERROR: NO IMAGES FOUND: '.$sv["sku"].' (product_id='.$product->id.')! at '. now());
            if($fix){
                if($this->downloadAndRefreshImages($product)){
                    return($this->imageSkuChecks($product, false));
                }
            }
        }
        infolog('[imageSkuChecks] END at '. now());
        return($result);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        infolog('[handle] START at '. now());
        if($product=$this->productSkuChecks()){
            if($this->imageSkuChecks($product)){
                infolog('[handle] SUCCESS at '. now());
            }else{
                infolog('[handle] ERROR: Image Checks FAILED! at '. now());
            }
        }else{
            infolog('[handle] ERROR: SKU Checks FAILED! at '. now());
        }
        infolog('[handle] END at '. now());
    }
}

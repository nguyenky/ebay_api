<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['SKU','Name','Description','Category','Size','Color','Cost','Sell','RRP','QTY','Image1','Image2','Image3','Image4','Image5','images_percent','Length','Width','Height','UnitWeight','Origin','Construction','Material','Pileheight','listing_price','OfferID','listingID','ebayupdated_at','product_mode_test'];
    public $timestamps = false;

    public function getListingPrice(){
        //Rule: Price=Cost+$25+10%_GST+10%_SaleCost+15%_Margin
        $cost=$this->Cost;
        $shipping=27;
        $tax=0.1;
        $saleCost=0.1;
        $margin=0.2;

        $costMargin=($cost*$margin);

        $price=$costMargin+$shipping+($costMargin*$saleCost)+($costMargin*$tax);

        return($price);
    }

    public function setListingPrice($save=true){
        $price=$this->getListingPrice();
        if($price!=$this->listing_price){
            $this->listing_price=$price;
            if($save){
                $this->save();
            }
        }
        return($this->listing_price);
    }

    public function getImagesArray($full_path=true,$prepend=NULL){
        $result=[];
        $images=Image::where("product_id",$this->id)->get();
        if($images){
            $prepend=($prepend===NULL)?env("PROD_APP_URL"):$prepend;
            $prepend=($full_path)?$prepend:"";
            foreach($images as $image){
                if(strlen($image->url)>0 && file_exists(public_path("images/".$image->url))){
                    $result[]=$prepend."/images/".$image->url;
                }else{
                    #infolog("File: ".public_path("images/".$image)." does not exist.");
                }
            }
        }
        #infolog("Image Results",$result);
        return($result);
    }

    public function getSpecifics($arrayed=false){
        $result=[];
        $specifics=Specific::where("product_id",$this->id)->get();
        if($specifics){
            foreach($specifics as $specific){
                if(strlen($specific->name)>0 && strlen($specific->value)>0){
                    if($arrayed){
                        $result[$specific->name]=[$specific->value];
                    }else{
                        $result[$specific->name]=$specific->value;
                    }
                }
            }
        }
        #infolog("Image Results",$result);
        return($result);
    }

    public function calculateImagePercent($save=true){
        $result=0;
        $imagesCount=0;
        $imagesFound=0;
        $images=[];
        foreach (glob(public_path("images/")."*") as $filename) {
            $images[]=basename($filename);
        }
        if(strlen($this->Image1)>0){
            $imagesCount++;
            if(in_array($this->Image1,$images)){
                $imagesFound++;
            }
        }
        if(strlen($this->Image2)>0){
            $imagesCount++;
            if(in_array($this->Image2,$images)){
                $imagesFound++;
            }
        }
        if(strlen($this->Image3)>0){
            $imagesCount++;
            if(in_array($this->Image3,$images)){
                $imagesFound++;
            }
        }
        if(strlen($this->Image4)>0){
            $imagesCount++;
            if(in_array($this->Image4,$images)){
                $imagesFound++;
            }
        }
        if(strlen($this->Image5)>0){
            $imagesCount++;
            if(in_array($this->Image5,$images)){
                $imagesFound++;
            }
        }
        if($imagesCount>0){
            $result=round(($imagesFound/$imagesCount) * 100,2);
        }

        $this->images_percent=$result;

        if($save){
            $this->save();
        }

        return($result);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['SKU','Name','Description','Category','Size','Color','Cost','Sell','RRP','QTY','Image1','Image2','Image3','Image4','Image5','Length','Width','Height','UnitWeight','Origin','Construction','Material','Pileheight','listing_price','OfferID','listingID','ebayupdated_at','product_mode_test'];
    public $timestamps = false;

    public function getListingPrice(){
        //Rule: Price=Cost+$25+10%_GST+10%_SaleCost+15%_Margin
        $cost=$this->Cost;
        $shipping=25;
        $tax=0.1;
        $saleCost=0.1;
        $margin=0.15;

        $price=$cost+$shipping+($cost*$saleCost)+($cost*$tax)+($cost*$margin);

        return($price);
    }

    public function setListingPrice(){
        $price=$this->getListingPrice();
        if($price!=$this->listing_price){
            $this->listing_price=$price;
            $this->save();
        }
        return($this->listing_price);
    }
}

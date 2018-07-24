<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['SKU','Name','Description','Category','Size','Color','Cost','Sell','RRP','QTY','Image1','Image2','Image3','Image4','Image5','Length','Width','Height','UnitWeight','Origin','Construction','Material','Pileheight','OfferID','listingID','product_mode_test'];
    public $timestamps = false; 
}

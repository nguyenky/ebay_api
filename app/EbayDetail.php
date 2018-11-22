<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EbayDetail extends Model
{
    protected $fillable = ['product_id','categoryid','manual_margin','manual_shipping','offerid','listingid','error','sync','synced_at'];
}

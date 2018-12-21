<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EbayDetail extends Model
{
    protected $fillable = ['product_id','categoryid','margin','shipping','price','offerid','listingid','error','sync','synced_at'];
}

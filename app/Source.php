<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    const UNITEX_RUGS_STANDARD=1;
    const DROPSHOPZONE_DEFAULT=2;
    const UNITEX_RUGS_HANDMADE=3;
    const UNITEX_RUGS_ACCESSORIES=4;
    const UNITEX_SHOPIFY=5;
}

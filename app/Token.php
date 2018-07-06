<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'tokens';
    protected $fillable = ['grant_code','accesstoken_dropbox','refresh_token_ebay','accesstoken_ebay'];
}

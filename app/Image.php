<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public function exists($url=NULL){
        $url=($url===NULL)?$this->url:$url;
        return(strlen($url)>0 && file_exists(public_path("images/".$url)));
    }
}

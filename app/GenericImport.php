<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GenericImport extends Model
{
    protected $fillable=["user_id","path","table","header_row_x","header_row_y","mapping","error"];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $table = 'systems';
    protected $fillable = ['filecsv'];
}

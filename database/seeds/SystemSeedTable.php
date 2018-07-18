<?php

use Illuminate\Database\Seeder;
use App\System;
class SystemSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$system = [
    		'filecsv'=>'UNITEX-DATAFEED-ALL.csv',
    	];
        System::truncate();
        System::create($system);
    }
}

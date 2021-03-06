<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Token;
use App\Jobs\UploadProductToEbay;

class UpdateProductToEbayOne extends Command
{

    protected  $friend;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ebay-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //------- Call Jobs ---------------
        dispatch(new UploadProductToEbay)->onQueue('uploads');
    }

}

<?php

namespace App\Console\Commands;

use App\Jobs\ebay\DescriptionUpdate;
use Illuminate\Console\Command;

class QueueDescriptionUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:queue-description-updates';

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
        DescriptionUpdate::queueAllEbay();
    }
}

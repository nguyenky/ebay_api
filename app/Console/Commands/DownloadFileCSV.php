<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DownloadFileCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:download-file-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download file csv from dropbox';

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
        dispatch(new \App\Jobs\dropbox\DownloadCSV)->onQueue('uploads');
    }
}

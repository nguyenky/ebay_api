<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the daily report to administrators.';

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
        $report_data=DailyReportMail::getReportData();
        $users=User::where("authorised",1)->where("id",1)->get();
        if(count($users)>0){
            foreach($users as $user){
                Mail::to($user)->send(new DailyReportMail($user, $report_data));
                //Mail::to($user)->queue(new DailyReportMail($user, $report_data)); //Queued
            }
        }
    }
}

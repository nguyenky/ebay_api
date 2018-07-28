<?php

namespace App\Http\Controllers;

use App\Jobs\dropbox\CheckCSVFile;
use App\Jobs\dropbox\DownloadCSV;
use Illuminate\Http\Request;

class ManualProcessingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('manual-processing.index');
    }

    /**
     * Step1
     *
     * @return void
     */
    public function step1()
    {
        $step=new DownloadCSV();
        $step->handle();
        session('manual-step','step1');
    }

    /**
     * Step1
     *
     * @return void
     */
    public function step2()
    {
        $step=new CheckCSVFile();
        $step->handle();
        session('manual-step','step2');
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\dropbox\CheckCSVFile;
use App\Jobs\dropbox\DownloadCSV;
use App\Jobs\ebay\CreateInventoryEbay;
use App\Jobs\ebay\CreateOfferEbay;
use App\Jobs\ebay\PublicOfferEbay;
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
        dispatch_now(new DownloadCSV);
        session('manual-step','step1');
    }

    /**
     * Step2
     *
     * @return void
     */
    public function step2()
    {
        dispatch_now(new CheckCSVFile);
        session('manual-step','step2');
    }

    /**
     * Step3
     *
     * @return void
     */
    public function step3()
    {
        dispatch_now(new CreateInventoryEbay);
        session('manual-step','step3');
    }

    /**
     * Step3
     *
     * @return void
     */
    public function step4()
    {
        dispatch_now(new CreateOfferEbay);
        session('manual-step','step4');
    }

    /**
     * Step3
     *
     * @return void
     */
    public function step5()
    {
        dispatch_now(new PublicOfferEbay);
        session('manual-step','step5');
    }
}

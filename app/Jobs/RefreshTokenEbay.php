<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RefreshTokenEbay implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $refresh_token;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($refresh_token)
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Job Refresh Token STATR at '. now());

        try {
            $client = new \GuzzleHttp\Client();
            $appID = env('EBAY_APPID');
            $clientID = env('CERT_ID');

            $code = $appID .':'.$clientID;

            $this->base64 = 'Basic '.base64_encode($code);

            $header = [
                'Content-Type'=>'application/x-www-form-urlencoded',
                'Authorization'=> $this->base64,
            ];
            $body = [
                'grant_type'=>'refresh_token',
                'refresh_token'=>$this->refresh_token,
                'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
            ];
            $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                                'headers'=> $header,
                                'form_params'  => $body
                            ]);

            $search_results = json_decode($res->getBody(), true);
            $token = \App\Token::find(1);
            $token->accesstoken_ebay = $getAccessToken['access_token'];
            $token->save();
            
            \Log::info('Job Refresh Token SUCCESS at '. now());
        }
         catch (\Exception $e){
            \Log::info('Job Refresh Token FAIL at '. $e);
            dd($e);
        }
        \Log::info('Job Refresh Token END at '. now());
    }
}

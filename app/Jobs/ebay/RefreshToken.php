<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RefreshToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected  $token;
    protected  $api;
    public function __construct()
    {
        if(env('EBAY_SERVER') == 'sandbox'){

            $this->api = 'https://api.sandbox.ebay.com/';

        }else{

            $this->api = 'https://api.ebay.com/';
        }
        $this->token = \App\Token::find(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	\Log::info('Start refresh token job at '. now());
        try {

            $client = new \GuzzleHttp\Client();
            $appID = env('EBAY_APPID');
            $clientID = env('CERT_ID');

            $code = $appID .':'.$clientID;

            $header = [
                'Content-Type'=>'application/x-www-form-urlencoded',
                'Authorization'=> 'Basic '.base64_encode($code)
            ];
            $body = [
                'grant_type'=>'refresh_token',
                'refresh_token'=>$this->token->refresh_token_ebay,
                'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
            ];
            $res = $client->request('POST', $this->api.'identity/v1/oauth2/token',[
                                'headers'=> $header,
                                'form_params'  => $body
                            ]);

            $search_results = json_decode($res->getBody(), true);
            $token = \App\Token::find(1);
            $token->accesstoken_ebay = $search_results['access_token'];
            $token->save();
	    \Log::info('Completed refresh token job at' . now());
	        return($token);
        }
         catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e->getMessage());
            report($e);
        }
    }
}

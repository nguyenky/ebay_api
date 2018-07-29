<?php

namespace App\Http\Controllers\Ebay;

use App\Product;
use App\Token;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MerchantLocationController extends Controller
{
    public $token;
    public $api='https://api.ebay.com/';

    public function getMerchantLocation($key){
        try {
            $this->token=Token::first();

            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', $this->api.'sell/inventory/v1/location/'.$key,[
                'headers'=> $header,
            ]);
            $search_results = json_decode($res->getBody(), true);

            infolog('[getMerchantLocation] END at '. now(), $search_results);
            return $search_results;

        } catch (\Exception $e) {
            infolog('[getMerchantLocation] FAIL:  at '. now());
            if($e->getCode()==404){
                return false;
            }
        }
    }

    public function index(Request $request){
        infolog("merchantLocationKey:",env("MERCHANTLOCATIONKEY"));
        $location=$this->getMerchantLocation(env("MERCHANTLOCATIONKEY"));
        infolog("Location:",$location);
    }
}

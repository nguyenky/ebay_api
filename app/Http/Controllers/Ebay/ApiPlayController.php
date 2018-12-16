<?php

namespace App\Http\Controllers\Ebay;

use App\Token;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiPlayController extends Controller
{
    public $token;
    public $api='https://api.ebay.com/';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        infolog('[ApiPlayController] __construct at '. now());
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __destruct()
    {
        infolog('[ApiPlayController] __destruct at '. now());
    }

    /**
     * @param $key
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @example
     *   $location=$this->getMerchantLocation(env("MERCHANTLOCATIONKEY"));
     */
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

    /**
     * URL:
     *  https://api.ebay.com/sell/inventory/v1/inventory_item?limit=100&offset=0
     *
     * Headers:
     * Authorization:Bearer v^1.1#i^1#f^0#p^3#I^3#r^0#t^H4sIAAAAAAAAAOVYe2wURRjv9QUtVtSKkMrjWEDjY+9mX3e7G+6Sg5ZwUNrauxIKKWQfs+3K3u65s9f2lJizGFCBgCQSA9EUQRPQCP7RoAYwAU0FBSNq1GgMGA0PTQzxgZBAnL0+uJbwaEtCE++Pu8zM9/p93++bmxmQKS55dM38NRfKPGPyOzMgk+/xUONASXHRY3cX5FcU5YEcAU9nZmamsKPgzGwkJYykWA9R0jIR9LYnDBOJ2ckQkbJN0ZKQjkRTSkAkOooYiyyqFmkfEJO25ViKZRDeaGWI0AIspzFsgJJ5jYYywLNmn824FSJ4joFQloWARgVkWWLxOkIpGDWRI5lOiKABxZMUTVJcnGJEEBRpwYdNLiW8i6GNdMvEIj5AhLPhilldOyfWG4cqIQRtBxshwtHIvFhtJFpZVROf7c+xFe7NQ8yRnBQaOJprqdC7WDJS8MZuUFZajKUUBSJE+MM9HgYaFSN9wQwj/GyqOVbmBIGBXEAQeFrgb0sq51l2QnJuHIc7o6uklhUVoenoTvpmGcXZkJ+EitM7qsEmopVe9+eJlGTomg7tEFE1J9LYEKuqJ7yxujrbatVVqLpIeZ7lgkGWI8J6e9KwbIgc/CWlUK+jHmu9aR7kaa5lqrqbNOStsZw5EEcNB+aGFrmc3GChWrPWjmiOG1GuHNefQ2qpW9SeKqacFtOtK0zgRHizw5tXoI8SV0lwu0gBWU1SaUEDQKECLE9fQwq314dBjLBbm0hdnd+NBcpSmkxI9kroJA1JgaSC05tKQFtXRYbTaIbXIKkGBI1kBU0jZU4NkJQGIXBbXxH4/xM/HMfW5ZQD+zkyeCELEhcO51TUJU10rJXQjKeTkBgsmd16eonRjkJEi+MkRb+/ra3N18b4LLvZTwNA+Zcsqo4pLTAhEf2y+s2FST1LEQViLaSLDg4gRLRjBmLnZjMRrq+aV18Vm78iXruwqqaPvQMiCw+evQ7SmGIlYZ1l6Ep6dEFkbLVOsp10DBoGnhgRSOSCvNPw3F4fCNG1gbARKan7XMb5FCvhtyS8a7lTK7JRe29FyI9wknw9ewC27MPtplqmkR6O8hB0dLMVt5Blp4fjsF95CDqSolgp0xmOu17VIWhoKUPTDcPdJYbjMEd9KGGakpF2dAX1uxwR8SPJZDSRSDmSbMCoeqc7YCD7aZ4N8oERwxtlqKolG8Ust9ftFrLvD5Csq68kWVpRZBXKCilIKhtUNH5E2Bc166MMugBohsOnHAaAkUGrhK2jrayA1niZkYMkRQUByXIyTcqqJpH42sQIDNDkgAZGhHmuoeOdYvSdNOZbyIHqyKDhI/HoAuW2Y183MpIMSAVwAskCTiP5II+HCnvLDPYXitc9W15zrfAPvNeH87IfqsPTBTo87+V7PMAPZlEzwPTigobCgrsqkO5AHz6H+pDebOLrqg19K2E6Kel2frFn2eS9u1bkvCR0NoFJ/W8JJQXUuJyHBTD56koRNX5iGcVTNMVRDAjSwlIw4+pqIfVA4f2fb/e8NOXYxYa1oUPxMW8vYA8s31QKyvqFPJ6ivMIOT95WYaafzwQ3btj4yqtftuY1VB793XvkgyWPf7Jt19TN+5qe2na2e+bz7XHjXnnnpOhv6v71687v2Htp1c7GQw+d+nXr8VXja081/dLCFR/jL885ze5Q73u9voRkup57cMO7ntKD0c6j4Ef749OZaTXH30989NqM6ti00qPfvbPtm/KCF5awbzQv3/2w8sgJ5wh3z7EXzx4pHXvyrak/n9vy4RcT95Sta/pp42J+6uHVn/1weN+Ghiln/m2JZOjvu8tfrghzXbufjn96pXn7gtUHv248vmf12ulsctOzf0/oHrt54Yk3y5f5zs8aUxHw/TGh+lznQupi14ELlxqvzN7S3Qkvf5s4vL+l4J9nvppe/ufJv3rK9x+VtsTa4xEAAA==
    Accept:application/json
    Content-Type:application/json
     */

    /**
     * @param $page
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @example
     *   $this->checkInventoryImages($request->page);
     */
    public function checkInventoryImages($page){
        infolog('[checkInventoryImages] START at '. now());
        $page=((int)$page<1)?1:$page;
        $offset=($page-1)*100;
        try {
            $this->token=Token::first();

            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'Content-Language'=>'en-AU',
                'Accept'=>'application/json',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', $this->api.'sell/inventory/v1/inventory_item/?limit=100&offset='.$offset,[
                'headers'=> $header,
            ]);
            $search_results=json_decode($res->getBody(), true);
            print("<p><a href='?page=".($page+1)."'>NEXT</a></p>");

            #infolog('[checkInventoryImages] search_results at '. now(), $search_results);
            print("<pre>");
            if(is_array($search_results) && array_key_exists("inventoryItems", $search_results) && is_array($search_results["inventoryItems"])){
                foreach($search_results["inventoryItems"] as $item){
                    if(is_array($item) && array_key_exists("product", $item) && is_array($item["product"])){
                        if(array_key_exists("imageUrls", $item["product"]) && is_array($item["product"]["imageUrls"])){
                            #infolog('[checkInventoryImages] we have images at '. now(), $item["product"]["imageUrls"]);
                            foreach($item["product"]["imageUrls"] as $image){
                                $ext=strtolower(pathinfo($image,PATHINFO_EXTENSION));
                                if($ext!="jpg"){
                                    print($item["sku"]."\t[ERROR] Invalid Image extension\t".$image."\n");
                                }
                            }
                        }else{
                            #infolog('[checkInventoryImages] we DONT have images at '. now(), $item);
                        }
                    }else{
                        #infolog('[checkInventoryImages] we DONT have a product at '. now(), $item);
                    }
                }
            }
            print("</pre>");

            infolog('[checkInventoryImages] END at '. now(), $search_results);
            return $search_results;

        } catch (\Exception $e) {
            infolog('[checkInventoryImages] FAIL:  at '. now(),$e->getMessage());
            if($e->getCode()==404){
                return false;
            }
        }
    }

    public function index(Request $request){
        infolog('[index] START at '. now());
        $this->checkInventoryImages($request->page);
        infolog('[index] END at '. now());
        dd("Done.");
    }
}

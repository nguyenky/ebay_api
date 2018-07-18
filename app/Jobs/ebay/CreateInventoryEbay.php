<?php

namespace App\Jobs\ebay;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Dropbox;
class CreateInventoryEbay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected  $token;
    protected  $api;
    protected  $path_image;

    public function __construct()
    {
        if(env('EBAY_SERVER') == 'sandbox'){

            $this->api = 'https://api.sandbox.ebay.com/';

        }else{

            $this->api = 'https://api.ebay.com/';
        }
        $this->token = \App\Token::find(1);
        $this->path_image = '/DROPSHIP/IMAGES/2018 COLLECTIONS';  
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    

    public function handle()
    {
        $products = \App\Product::all();
        foreach ($products as $key => $value) {
            $images = $this->searchImages($value);
            $refreshToken = $this->refreshToken();
            $product = $this->createInventory($value,$images);
        }
    }
    public function searchImages($attribute){
        $namefile = Array();
        $data = Array();
        $data[] = $attribute['Image1'];
        $data[] = $attribute['Image2'];
        $data[] = $attribute['Image3'];
        $data[] = $attribute['Image4'];
        $data[] = $attribute['Image5'];
        foreach ($data as $key_image => $item) {

            $pathPublic = public_path().'/images/';
            if(!\File::exists($pathPublic)) {

                \File::makeDirectory($pathPublic, $mode = 0777, true, true);

            }
            if(!\File::exists($pathPublic.$item)){
                $value = json_encode(
                    [
                        'path' => $this->path_image,
                        'mode' => 'filename',
                        'query' => $item
                    ]
                );
                $response = Dropbox::api()->request(
                'POST', '/2/files/search',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token->accesstoken_dropbox,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $value
                ]);
                $search_results = json_decode($response->getBody(), true);
                $matches = $search_results['matches'];

                foreach ($matches as $key_matches => $value) {
                    $this->downImage($value['metadata']['path_lower']);
                    $namefile[] = $value['metadata']['name'];
                    break;
                }
            }  
        }
        return $namefile;
    }

    public function downImage($attribute){
        $data = json_encode([
            'path' => $attribute
        ]);

        $response = Dropbox::content()->request(
            'POST',
            '/2/files/download',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' .$this->token->accesstoken_dropbox,
                    'Dropbox-API-Arg' => $data
                ]
        ]);

        $result = $response->getHeader('dropbox-api-result');
        $file_info = json_decode($result[0], true);

        $content = $response->getBody();

        $filename = $file_info['name'];

        $file_extension = substr($filename, strrpos($filename, '.'));

        $file = $filename;

        $file_size = $file_info['size'];

        $pathPublic = public_path().'/images/';

        if(\File::exists($pathPublic.$file)){

            unlink($pathPublic.$file);
      
        }

        if(!\File::exists($pathPublic)) {

            \File::makeDirectory($pathPublic, $mode = 0777, true, true);

        }
        try {
            \File::put(public_path() . '/images/' . $file, $content);
        } catch (\Exception $e){
            dd($e);
        }
        return null;
    }

    public function createInventory($attribute,$images){
        $imageUrls =[];
        foreach ($images as $key => $value) {
            $url = url('/images/'.$value); 
            array_push($imageUrls,$url);
        }
        try {
            $client = new \GuzzleHttp\Client();
            $data = [];
            $data = [
                'availability'  => [
                    'shipToLocationAvailability'    => [
                        'quantity'  => $attribute->QTY,
                    ]
                ],
                'condition'     => 'NEW',
                'product'       => [
                    'title'     => $attribute->Name,
                    'imageUrls' =>$imageUrls,
                    'aspects'   => [
                        'size' => [$attribute->Size],
                        'color' => [$attribute->Color],
                        'length' => [$attribute->Length],
                        'width' => [$attribute->Width],
                        'height' => [$attribute->Height],
                        'unitweight' => [$attribute->UnitWeight],
                        'construction' => [$attribute->Construction ? $attribute->Construction : 'NEW'],
                        'material' => [$attribute->Material],
                        'pileheight' => [$attribute->Pileheight]
                    ],
                    'category' => $attribute->Category,
                    // 'description'=> 'Description',
                    'description'=> $attribute->Description,
                    'cost' => $attribute->Cost,
                    'sell' => $attribute->Sell,
                    'rrp' => $attribute->RRP,
                    'origin' => $attribute->Origin,
                ]
            ];
            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$this->token->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_AU',
                'Content-Language'=>'en-AU',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('PUT', $this->api.'sell/inventory/v1/inventory_item/'.$attribute->SKU,[
                'headers'=> $header,
                'body'  => $json
            ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        \Log::info('Job [Ebay] SUCCESS ----Create item---- at '. now());
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL ----Create item---- at '. now());
            dd($e);
        }
        \Log::info('Job [Ebay] END ----Create item---- at '. now());      
        return null;
    }

    public function refreshToken(){
        try {

            $client = new \GuzzleHttp\Client();
            $appID = env('EBAY_APPID');
            $clientID = env('CERT_ID');

            $code = $appID .':'.$clientID;

            $header = [
                'Content-Type'=>'application/x-www-form-urlencoded',
                'Authorization'=> 'Basic '.base64_encode($code),
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

            $this->token->refresh_token_ebay = $search_results['access_token'];

            $token = \App\Token::find(1);
            $token->refresh_token_ebay = $search_results['access_token'];
            $token->save();
        }
         catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e);
            dd($e);
        }
        return $search_results;
    }
}

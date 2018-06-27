<?php

namespace App\Http\Controllers\Dropbox;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dropbox;
use Purl\Url;
use App\Product;
use App\User;
use Auth;

class DropboxController extends Controller
{

    private $api_client;
    private $content_client;
    private $access_token;
    private $access_token_ebay;
    private $grantCode;
    private $base64;
    // private $refreshCode = 'v^1.1#i^1#r^1#f^0#p^3#I^3#t^Ul4xMF8xMDo4NDAyNEI4MDkzNjk3QzZDMEE1QURGMjVDOTJGNTZBQ18yXzEjRV4xMjg0';

    // ----- Dropbox ---------

    private $path   = '/picture';
    private $mode   = 'filename';
    private $query  = 'UNITEX-DATAFEED-ALL.csv';


    public function __construct(Dropbox $dropbox)
    {
        $this->api_client = $dropbox->api();
        $this->content_client = $dropbox->content();
        $this->access_token = session('access_token');
    }
    // 1
    public function index(){
        $url = new Url('https://www.dropbox.com/1/oauth2/authorize');

        $url->query->setData([
            'response_type' => 'code',
            'client_id' => env('DROPBOX_APP_KEY'),
            'redirect_uri' => env('DROPBOX_REDIRECT_URI')
        ]);

        return redirect($url->getUrl());
    }

    public function loginDropbox(Request $request){
        // dd('asdsd');
        if ($request->has('code')) {

            $data = [
                'code' => $request->input('code'),
                'grant_type' => 'authorization_code',
                'client_id' => env('DROPBOX_APP_KEY'),
                'client_secret' => env('DROPBOX_APP_SECRET'),
                'redirect_uri' => env('DROPBOX_REDIRECT_URI')
            ];

            $response = $this->api_client->request(
                'POST',
                '/1/oauth2/token',
                ['form_params' => $data]
            );

            $response_body = json_decode($response->getBody(), true);
            $access_token = $response_body['access_token'];
            // dd($access_token);
            $this->updateToken($access_token);

            session(['access_token' => $access_token]);

            return redirect('home');
        }

        return redirect('/');
    }
    public function updateToken($accessToken){
        $user = \App\User::find(\Auth::user()->id);
        $user->accesstoken_dropbox = $accessToken;
        $user->save();
        // dd($user);
    }
    public function userDropboxInfor(){
        // dd(\Auth::user()->accesstoken_dropbox);
        $response = $this->api_client->request('POST', '/2/users/get_current_account', [
            'headers' => [
                'Authorization' => 'Bearer ' . \Auth::user()->accesstoken_dropbox,
            ]
        ]);

        $user = json_decode($response->getBody(), true);
        dd($user);
        $page_data = [
            'user' => $user
        ];

        return view('admin.user', $page_data);
    }


    public function start(){
        // $client = new \GuzzleHttp\Client();
        // $res = $client->request('GET', 'https://auth.sandbox.ebay.com/oauth2/authorize?client_id=SFRSoftw-sfrsoftw-SBX-72ccbdeee-fce8a005&response_type=code&redirect_uri=SFR_Software-SFRSoftw-sfrsof-watlbqpzg&scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/buy.order.readonly https://api.ebay.com/oauth/api_scope/buy.guest.order https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly https://api.ebay.com/oauth/api_scope/sell.marketplace.insights.readonly https://api.ebay.com/oauth/api_scope/commerce.catalog.readonly');
        // dd(file_get_contents($res->getBody()));
        // $this->step1GetGrantCode();

    }
    public function step1GetGrantCode(Request $request){
        $input = $request->all();
        if($input['code']){
            $this->grantCode = $input['code'];
            $this->step2GetAccessTokenEbay();
        }

    }
    public function step2GetAccessTokenEbay(){
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
            'grant_type'=>'authorization_code',
            'code'=>$this->grantCode,
            'redirect_uri'=>env('RUNAME'),
        ];
        // dd($body);

        $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                            'headers'=> $header,
                            'data'  => $body
                        ]);
        dd($res->getBody());
        // dd($this->base64);
        $this->step3GetAccessTokenEbay();

    }
    ///----------------------------
    public function step3RefreshToken(){
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
            'refresh_token'=>$this->refreshCode,
            'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
        ];
        $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                            'headers'=> $header,
                            'form_params'  => $body
                        ]);
        // dd($res);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        $this->access_token_ebay = $search_results['access_token'];
        // $this->createItemsEbay();
        // dd($search_results);
    }
    // public function step4Create(){

    // }

    public function createItemsEbay($attribute,$filename){

        try {
            \Log::info('Job [Ebay] START at '. now());

            $client = new \GuzzleHttp\Client();

            $data = [];
            $data = [
                'availability'  => [
                    'shipToLocationAvailability'    => [
                        'quantity'  => $attribute['QTY'],
                        // 'quantity'  => 12,
                    ]
                ],
                'condition'     => 'NEW',
                'product'       => [
                    'title'     => $attribute['Name'],
                    // 'title'     => 'uchiha',
                    'imageUrls' =>[
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1000.jpg",
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1001.jpg",
                        "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1002.jpg"
                    ],
                    'aspects'   => [
                        'size' => [$attribute['Size']],
                        'color' => [$attribute['Color']],
                        'length' => [$attribute['Length']],
                        'width' => [$attribute['Width']],
                        'height' => [$attribute['Height']],
                        'unitweight' => [$attribute['UnitWeight']],
                        'construction' => [$attribute['Construction']],
                        'material' => [$attribute['Material']],
                        'pileheight' => [$attribute['Pileheight']]
                    ],
                    'category' => $attribute['Category'],
                    'description'=> $attribute['Description'],
                    'cost' => $attribute['Cost (Ex.GST) '],
                    'sell' => $attribute['Sell'],
                    'rrp' => $attribute['RRP'],
                    'origin' => $attribute['Origin'],
                ]
            ];
            $json = json_encode($data);
            $header = [
            'Authorization'=>'Bearer '.$this->access_token_ebay,
            'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
            'Content-Language'=>'en-US',
            'Content-Type'=>'application/json'
        ];
            $res = $client->request('PUT', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/'.$attribute['SKU'],[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL at '. now());
            dd($e);
        }      
        // dd($this->access_token_ebay);
    }

    public function getAllItems(){
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.Auth::user()->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/401-OATMEAL-280X190',[
                            'headers'=> $header,
                            // 'body'  => $json
                        ]);
            $search_results = json_decode($res->getBody(), true);
            dd($search_results);
            // return true;
        }
         catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL at '. now());
            dd($e->getCode());
        }
       
    }
    // ---- update token ---working
    public function updateGrantCode(Request $request){
        $input = $request->all();
        $user = User::find(Auth::user()->id);
        $user->grant_code = $input['code'];
        $this->grantCode = $input['code'];
        $token = $this->getAccessToken();
        $user->accesstoken_ebay = $token['access_token'];
        $user->refresh_token_ebay = $token['refresh_token'];
        $user->save();
        return 'ok';
    }

    public function getAccessToken(){
        // dd(Auth::user()->toArray());
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
            'grant_type'=>'authorization_code',
            // 'code'=>Auth::user()->grant_code,
            'code'=>$this->grantCode,
            'redirect_uri'=>env('RUNAME'),
        ];
        // dd($body);

        $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                            'headers'=> $header,
                            'form_params'  => $body
                        ]);
        $search_results = json_decode($res->getBody(), true);
        return $search_results;
    }
    //--- end update token ----

    /// begin process 

    public function beginProcess(){
        set_time_limit(30);
        $matches    = $this->step1DropboxSearchFileCsv();
        $filename   = $this->step2DropboxDownFileCsv($matches);
        $csv        = $this->step3DropboxConvertFileCsv('files/'.$filename);
        $getAccessToken = $this->step4EbayRefreshToken($csv);
        $user = User::find(Auth::user()->id);
        $user->accesstoken_ebay = $getAccessToken['access_token'];
        $user->save();

        $products = $this->step5EbayCreadtItems($csv);
         // dd($csv);

       
    }
    public function step1DropboxSearchFileCsv(){

     try {       

            \Log::info('Job [Ebay] START at '. now());

            $data = json_encode(
                    [
                        'path' => $this->path,
                        'mode' => $this->mode,
                        'query' => $this->query,
                    ]
                );

            $response = $this->api_client->request(
                'POST', '/2/files/search',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . \Auth::user()->accesstoken_dropbox,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $data
            ]);

            $search_results = json_decode($response->getBody(), true);

            $matches = $search_results['matches'];
            
            \Log::info('Job [Ebay] END at '. now());

           
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL at '. $e);
            dd($e);
        }
             return $matches;
    }

    public function step2DropboxDownFileCsv($matches){
        try {

            \Log::info('Job [Ebay] START at '. now());

            if($matches == null) {
                return;
            }
            else {
                $data = json_encode([
                        'path' => $matches[0]['metadata']['path_lower']
                    ]);
                $response = $this->content_client->request(
                    'POST',
                    '/2/files/download',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' .\Auth::user()->accesstoken_dropbox,
                            'Dropbox-API-Arg' => $data
                        ]
                ]);

                $result = $response->getHeader('dropbox-api-result');
                $file_info = json_decode($result[0], true);

                $content = $response->getBody();
                $filename = $file_info['name'];
                // dd($filename);
                $file_extension = substr($filename, strrpos($filename, '.'));
                // dd($file_extension);
                // $file ='lenguyenky'.$file_extension;

                $file_size = $file_info['size'];

                $pathPublic = public_path().'/files/';

                if(\File::exists($pathPublic.$filename)){

                    unlink($pathPublic.$filename);
              
                }

                if(!\File::exists($pathPublic)) {

                    \File::makeDirectory($pathPublic, $mode = 0777, true, true);

                }
                try {
                    \File::put(public_path() . '/files/' . $filename, $content);
                } catch (\Exception $e){
                    dd($e);
                }

            }  
            \Log::info('Job [Ebay] END at '. now());
        } 
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e);
            dd($e);
        }
            
        return $filename;
        
        // dd($content);
    }
    public function step3DropboxConvertFileCsv($attribute){
        $csv = Array();
        $rowcount = 0;
        if (($handle = fopen($attribute, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $entry;
                }
                else {
                    return null;
                }
                $rowcount++;
            }
            //echo "Totally $rowcount rows found\n";
            fclose($handle);
        }
        else {
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }

        return $csv;
    }
    public function step4EbayRefreshToken(){
        // dd(Auth::user());
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
                'refresh_token'=>Auth::user()->refresh_token_ebay,
                'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
            ];
            $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                                'headers'=> $header,
                                'form_params'  => $body
                            ]);

            $search_results = json_decode($res->getBody(), true);
            $this->access_token_ebay = $search_results['access_token'];
        }
         catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e);
            dd($e);
        }
        return $search_results;
    }

    public function step5EbayCreadtItems($attributes){

        try {
            foreach ($attributes as $key => $attribute) {
                // echo $key.'<br>';
            $namefile = Array();
            $data = Array();
            $data[] = $attribute['Image1'];
            $data[] = $attribute['Image2'];
            $data[] = $attribute['Image3'];
            $data[] = $attribute['Image4'];
            $data[] = $attribute['Image5'];
            // dd($data);

            foreach ($data as $key => $item) {
                // dd($item);
                $value = json_encode(
                    [
                        'path' => '/DROPSHIP/IMAGES/2018 COLLECTIONS',
                        'mode' => 'filename',
                        'query' => $item
                    ]
                );
                $response = $this->api_client->request(
                'POST', '/2/files/search',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . \Auth::user()->accesstoken_dropbox,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $value
                ]);
                $search_results = json_decode($response->getBody(), true);
                $matches = $search_results['matches'];
                foreach ($matches as $key => $value) {
                    $this->step5_1downloadImage($value['metadata']['path_lower']);
                    $namefile[] = $value['metadata']['name'];
                    break;
                }
                
            }

            // // dd($namefile);
            $product = $this->step5_2CreateItem($attribute,$namefile);
            // dd($product);
            // //------------- Delete Image -------------
            foreach ($namefile as $key => $item) {
                 unlink(public_path('/files/'.$item));
            }
            // ------------- End Delete Image -----------

            }

            return "Update finish";

        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e);
            dd($e);
        }

        
    }

    public function step5_1downloadImage($attribute){
 
        $data = json_encode([
                'path' => $attribute
            ]);

            $response = $this->content_client->request(
                'POST',
                '/2/files/download',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' .\Auth::user()->accesstoken_dropbox,
                        'Dropbox-API-Arg' => $data
                    ]
            ]);

            $result = $response->getHeader('dropbox-api-result');
            $file_info = json_decode($result[0], true);

            $content = $response->getBody();

            $filename = $file_info['name'];
            // dd($filename);
            $file_extension = substr($filename, strrpos($filename, '.'));
            // dd($file_extension);
            $file = $filename;

            $file_size = $file_info['size'];

            $pathPublic = public_path().'/files/';

            if(\File::exists($pathPublic.$file)){

                unlink($pathPublic.$file);
          
            }

            if(!\File::exists($pathPublic)) {

                \File::makeDirectory($pathPublic, $mode = 0777, true, true);

            }
            try {
                \File::put(public_path() . '/files/' . $file, $content);
            } catch (\Exception $e){
                dd($e);
            }
    }

    public function getItems($attribute){
        try {
            // dd($attribute);
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.Auth::user()->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/'.$attribute,[
                            'headers'=> $header,
                        ]);
            $search_results = json_decode($res->getBody(), true);
            return $search_results;
        }
         catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL at '. now());
            dd($e->getCode());
        }
       
    }

    public function step5_2CreateItem($attribute,$namefile){

        try {
            \Log::info('Job [Ebay] START at '. now());

            $search_results = $this->getItems($attribute['SKU']);

            $product = $search_results['product'];
         
            if($product['title'] == $attribute['Name'] && $product['description'] == $attribute['Description']){
                    if($product['aspects']['pileheight'][0] == $attribute['Pileheight'] && $product['aspects']['height'][0] == $attribute['Height'] && $product['aspects']['color'][0] == $attribute['Color'] && $product['aspects']['width'][0] == $attribute['Width'] &&$product['aspects']['length'][0] == $attribute['Length'] && $product['aspects']['unitweight'][0] == $attribute['UnitWeight'] && $product['aspects']['construction'][0] == $attribute['Construction'] && $product['aspects']['material'][0] == $attribute['Material'] && $product['aspects']['size'][0] == $attribute['Size']){
                            return
                    } else {
                        $this->createItemsEbay($attribute,$namefile);
                    }

            } else {
               $this->createItemsEbay($attribute,$namefile);
            }
                // return $product;
            \Log::info('Job [Ebay] END at '. now());
        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL at '. $e);
            if($e->getCode() == 404){
                $this->createItemsEbay($attribute,$namefile);
                $this->step5_2CreateItem($attribute,$namefile);
            }
        }

    }




    public function upload(){
        $devID = env('EBAY_DEVID');
        $appID = env('EBAY_APPID');
        $certID = env('CERT_ID');

        //the token representing the eBay user to assign the call with
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**D/0xWw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4agD5OGqA6dj6x9nY+seQ**4J8EAA**AAMAAA**r/iy+VnJKThrUxY8Ojucb44A7xgLjqriJM2KT9VHhadZqMCHitZl6Wgga/KGO8NufOabDg6h1bV5DUnB4CVGLyfEoxSnOlnLySN4V+8A4hEQb+VawHhf3M0TDmvhTIFrFfGzZuru3jyhYJjPbLxKV4/zzZq0OxY+XKO021EwPstqW0LiVZmIr47wbT3i158i6aAQeikQ7OfqhXf689/tbTebfhCDHocLR24F+pX/MXWv9AfMkfKLT2+QJZPhdwPiob5Uk8omlhTVpu9WhbhY2dSkPjU5FXAf4gKDfRRX80DSnTITZFlMAVX49ztz7IfGo+IUXNLDtsPuDvyntH3MHzup4W6+02WS0Vj8KNnhkrjUoFMswZLYMsSLoNXnReLWmmp4HKc7wSLtCv1y6G6R7v30VHsBWGdGBJPV1f/37ZoVGhpToAaabz9r59hiYHU7F63iQPfoA+jOiktYg/2Z6TIjtBvOkP7IjERzBAmx5o4Rhr3LprPFndmRnslg4R9PeKelNTpLBR7AE6NN4nlUaUW3UBN2y3rA6LRrB98yX6/acouWuKvYfo6Ujy5GdMoF+2CTg6SxgjFeWL57+hkCzWrfKQtnX2UGtYYEL9hvR/MmrlrR7Wk2umtw7JQdVeWTLaU+TaeVjKWM+shHu0xzRrQ7PutcJuamM9j76l4BCNklgINEG1oO7ClmqHErzaHdUNw8bbqyjD4JJVFD+eCdrZDb/7M1scUBVXgdYkIvl9vYB1KI7eV+3bpe/Ll/vsVn';

        // dd($userToken);

        $siteID  = 0;                            // siteID needed in request - US=0, UK=3, DE=77...
        $verb    = 'UploadSiteHostedPictures';   // the call being made:
        $version = 517;                          // eBay API version
        
        $file      = 'uchiha.jpg';       // image file to read and upload
        $picNameIn = 'my_pic';
        $handle = fopen($file,'r');         // do a binary read of image
        $multiPartImageData = fread($handle,filesize($file));
        
        fclose($handle);

        ///Build the request XML request which is first part of multi-part POST
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlReq .= "<Version>$version</Version>\n";
        $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";    
        $xmlReq .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>\n";
        $xmlReq .= "<PictureSet>Standard</PictureSet>\n";
        $xmlReq .= '</' . $verb . 'Request>';

        $boundary = "MIME_boundary";
        $CRLF = "\r\n";
        
        // The complete POST consists of an XML request plus the binary image separated by boundaries
        $firstPart   = '';
        $firstPart  .= "--" . $boundary . $CRLF;
        $firstPart  .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
        $firstPart  .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
        $firstPart  .= $xmlReq;
        $firstPart  .= $CRLF;

        $secondPart   = '';
        $secondPart .= "--" . $boundary . $CRLF;
        $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
        // $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
        $secondPart .= "Content-Type: image/jpeg" . $CRLF . $CRLF;
        $secondPart .= $multiPartImageData;
        $secondPart .= $CRLF;
        $secondPart .= "--" . $boundary . "--" . $CRLF;
        
        $fullPost = $firstPart . $secondPart;
        
        // Create a new eBay session (defined below) 
        $session = new eBaySession($userToken, $devID, $appID, $certID, false, $version, $siteID, $verb, $boundary);

        $respXmlStr = $session->sendHttpRequest($fullPost);   // send multi-part request and get string XML response
        
        if(stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '')
            die('<P>Error sending request');
            
        $respXmlObj = simplexml_load_string($respXmlStr);     // create SimpleXML object from string for easier parsing
                                                              // need SimpleXML library loaded for this
        /* Returned XML is of form 
          <?xml version="1.0" encoding="utf-8"?>
          <UploadSiteHostedPicturesResponse xmlns="urn:ebay:apis:eBLBaseComponents">
            <Timestamp>2007-06-19T16:53:50.370Z</Timestamp>
            <Ack>Success</Ack>
            <Version>517</Version>
            <Build>e517_core_Bundled_4784308_R1</Build>
            <PictureSystemVersion>2</PictureSystemVersion>
            <SiteHostedPictureDetails>
              <PictureName>my_pic</PictureName>
              <PictureSet>Standard</PictureSet>
              <PictureFormat>JPG</PictureFormat>
              <FullURL>http://i21.ebayimg.com/06/i/000/a5/e9/0e60_1.JPG?set_id=7</FullURL>
              <BaseURL>http://i21.ebayimg.com/06/i/000/a5/e9/0e60_</BaseURL>
              <PictureSetMember>...</PictureSetMember>
              <PictureSetMember>...</PictureSetMember>
              <PictureSetMember>...</PictureSetMember>
            </SiteHostedPictureDetails>
          </UploadSiteHostedPicturesResponse>
        */
        dd($respXmlObj);
        $ack        = $respXmlObj->Ack;
        $picNameOut = $respXmlObj->SiteHostedPictureDetails->PictureName;
        $picURL     = $respXmlObj->SiteHostedPictureDetails->FullURL;
    }
}

class eBaySession
        {
            private $requestToken;
            private $devID;
            private $appID;
            private $certID;
            private $serverUrl;
            private $compatLevel;
            private $siteID;
            private $verb;
            private $boundary;

            public function __construct($userRequestToken, $developerID, $applicationID, $certificateID, $useTestServer,
                                        $compatabilityLevel, $siteToUseID, $callName, $boundary)
            {
                $this->requestToken = $userRequestToken;
                $this->devID = $developerID;
                    $this->appID = $applicationID;
                $this->certID = $certificateID;
                $this->compatLevel = $compatabilityLevel;
                $this->siteID = $siteToUseID;
                $this->verb = $callName;
                    $this->boundary = $boundary;
                if(!$useTestServer)
                $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
                else
                    $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
            }
            
            /** sendHttpRequest
                Sends a HTTP request to the server for this session
                Input:  $requestBody
                Output: The HTTP Response as a String
            */
            public function sendHttpRequest($requestBody)
            {        
                $headers = array (
                    'Content-Type: multipart/form-data; boundary=' . $this->boundary,
                    'Content-Length: ' . strlen($requestBody),
                'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->compatLevel,  // API version
                    
                'X-EBAY-API-DEV-NAME: ' . $this->devID,     //set the keys
                'X-EBAY-API-APP-NAME: ' . $this->appID,
                'X-EBAY-API-CERT-NAME: ' . $this->certID,

                    'X-EBAY-API-CALL-NAME: ' . $this->verb,     // call to make 
                'X-EBAY-API-SITEID: ' . $this->siteID,      // US = 0, DE = 77...
                );
            //initialize a CURL session - need CURL library enabled
            $connection = curl_init();
            curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
                curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
            curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($connection, CURLOPT_POST, 1);
            curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($connection, CURLOPT_FAILONERROR, 0 );
                curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1 );
                //curl_setopt($connection, CURLOPT_HEADER, 1 );           // Uncomment these for debugging
                //curl_setopt($connection, CURLOPT_VERBOSE, true);        // Display communication with serve
                curl_setopt($connection, CURLOPT_USERAGENT, 'ebatns;xmlstyle;1.0' );
                curl_setopt($connection, CURLOPT_HTTP_VERSION, 1 );       // HTTP version must be 1.0
            $response = curl_exec($connection);
                
                if ( !$response ) {
                    print "curl error " . curl_errno($connection ) . "\n";
                }
            curl_close($connection);
            return $response;
            } // function sendHttpRequest
        }  // class eBaySe



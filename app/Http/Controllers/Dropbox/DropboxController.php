<?php

namespace App\Http\Controllers\Dropbox;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dropbox;
use Purl\Url;
use App\Product;

class DropboxController extends Controller
{

    private $api_client;
    private $content_client;
    private $access_token;
    private $access_token_ebay;
    private $grantCode='v^1.1#i^1#r^1#I^3#f^0#p^3#t^Ul41XzQ6RDUyODI4N0I3QTQzMzIzOUZDNTNDNzdFOTc4MzFBMzVfMF8xI0VeMTI4NA==';
    private $base64;
    private $refreshCode = 'v^1.1#i^1#r^1#f^0#p^3#I^3#t^Ul4xMF8xMDo4NDAyNEI4MDkzNjk3QzZDMEE1QURGMjVDOTJGNTZBQ18yXzEjRV4xMjg0';


    public function __construct(Dropbox $dropbox)
    {
        $this->api_client = $dropbox->api();
        $this->content_client = $dropbox->content();
        $this->access_token = session('access_token');
    }

    public function index(){
        $url = new Url('https://www.dropbox.com/1/oauth2/authorize');

        $url->query->setData([
            'response_type' => 'code',
            'client_id' => env('DROPBOX_APP_KEY'),
            'redirect_uri' => env('DROPBOX_REDIRECT_URI')
        ]);

        return redirect($url->getUrl());
    }
    public function postIndex(){

    }

    public function loginDropbox(Request $request){

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
        $user->remember_token = $accessToken;
        $user->save();
        // dd($user);
    }
    public function userDropboxInfor(){
        // dd(\Auth::user()->remember_token);
        $response = $this->api_client->request('POST', '/2/users/get_current_account', [
            'headers' => [
                'Authorization' => 'Bearer ' . \Auth::user()->remember_token,
            ]
        ]);

        $user = json_decode($response->getBody(), true);
        // dd($user);
        $page_data = [
            'user' => $user
        ];

        return view('admin.user', $page_data);
    }

    public function getSearch(){
        return view('dropbox.search');
    }
    public function postSearch(Request $request){
        $input = $request->all();
        $page_data = [];
        if ($request->has('path') && $request->has('query')) {
            $path = $request->input('path');
            $query = $request->input('query');

            $data = json_encode(
                [
                    'path' => $path,
                    'mode' => 'filename',
                    'query' => $query
                ]
            );

            $response = $this->api_client->request(
                'POST', '/2/files/search',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . \Auth::user()->remember_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $data
            ]);

            $search_results = json_decode($response->getBody(), true);
            $matches = $search_results['matches'];

            $page_data = [
                'path' => $path,
                'query' => $query,
                'matches' => $matches
            ];
            // dd($page_data);
        }
        return view('dropbox.datasearch',[
            'data'=>$page_data,
        ]);
    }

    public function download(Request $request){
        $input = $request->all();
        // dd($input);

        if ($request->has('path')) {
            $path = $request->input('path');
            $data = json_encode([
                'path' => $path
            ]);

            $response = $this->content_client->request(
                'POST',
                '/2/files/download',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' .\Auth::user()->remember_token,
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
            $file ='lenguyenky'.$file_extension;

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
            
            // dd($content);
            // return response($content)
            //     ->header('Content-Description', 'File Transfer')
            //     ->header('Content-Disposition', "attachment; filename={$file}")
            //     ->header('Content-Transfer-Encoding', 'binary')
            //     ->header('Connection', 'Keep-Alive')
            //     ->header('Content-Length', $file_size);
            return redirect('search');
        } else {
            return redirect('search');
        }
    }

    public function uploadFileEbay(){
    
        $csvfile = 'files/lenguyenky.csv';

        // $csva = file_get_contents($path);
        // $no_blanks = str_replace("\r\n\r\n", "\r\n", $csva);
        // file_put_contents($path, $no_blanks);

       
        // $csv = explode(PHP_EOL, file_get_contents($path));
        // foreach ($csv as $key => $line)
        //     {
        //         $csv[$key] = str_getcsv($line);
        //     }
        // dd($csv);  

        $csv = Array();
        $rowcount = 0;
        if (($handle = fopen($csvfile, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            //dd($header_colcount);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                   // dd($entry);
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
        // dd(count($csv));
        $data = Array();

        foreach ($csv as $key => $value) {

            $this->createItemsEbay($value);
            // $data['SKU'] = $value['SKU'];
            // $data['Name'] = $value['Name'];
            // $data['Description'] = $value['Description'];
            // $data['Category'] = $value['Category'];
            // $data['Size'] = $value['Size'];
            // $data['Color'] = $value['Color'];
            // $data['Cost'] = $value['Cost (Ex.GST) '];
            // $data['Sell'] = $value['Sell'];
            // $data['RRP'] = $value['RRP'];
            // $data['QTY'] = $value['QTY'];
            // $data['Image1'] = $value['Image1'];
            // $data['Image2'] = $value['Image2'];
            // $data['Image3'] = $value['Image3'];
            // $data['Image4'] = $value['Image4'];
            // $data['Image5'] = $value['Image5'];
            // $data['Length'] = $value['Length'];
            // $data['Width'] = $value['Width'];
            // $data['Height'] = $value['Height'];
            // $data['UnitWeight'] = $value['UnitWeight'];
            // $data['Origin'] = $value['Origin'];
            // $data['Construction'] = $value['Construction'];
            // $data['Material'] = $value['Material'];
            // $data['Pileheight'] = $value['Pileheight'];   
            // $product = Product::create($data);
        }  
        // return redirect('products/all');  
        $products = Product::all();
        dd($products);          
    }

    public function getAllProduct(){
        $products = Product::all();
        dd($products->toArray());
    }
    /// ----- Download dropbox ----



    ///------


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
        dd($body);

        $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                            'headers'=> $header,
                            'data'  => $body
                        ]);
        dd($res->getBody());
        // dd($this->base64);
        $this->step3GetAccessTokenEbay();

    }
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
        $this->createItemsEbay();
        dd($search_results);
    }
    // public function step4Create(){

    // }

    public function createItemsEbay(){
        $client = new \GuzzleHttp\Client();

        $data = [];
        $data = [
            'availability'  => [
                'shipToLocationAvailability'    => [
                    // 'quantity'  => $product['QTY'],
                    'quantity'  => 12,
                ]
            ],
            'condition'     => 'NEW',
            'product'       => [
                // 'title'     => $product['Name'],
                'title'     => 'uchiha',
                'imageUrls' =>[
                    "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1000.jpg",
                    "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1001.jpg",
                    "http://i.ebayimg.com/images/i/182196556219-0-1/s-l1002.jpg"
                ],
                'aspects'   => [
                    'Brand' => ['GoPro'],
                    'Type'  => ['Helmet/Action'],
                    'Storage Type' => ['Removable'],
                    'Recording Definition' => ['High Definition'],
                    'Media Format'=>['Flash Drive (SSD)'],
                    'Optical Zoom'=> ['10x']
                ],
                'category' => 'SDSAD',
                // 'description'=> $product['Description']
                'description'=> 'ádsad'
            ]
        ];
        $json = json_encode($data);

        
        // dd($this->access_token_ebay);
        $header = [
            'Authorization'=>'Bearer '.$this->access_token_ebay,
            'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
            'Content-Language'=>'en-US',
            'Content-Type'=>'application/json'
        ];
        // dd($header);
        $res = $client->request('PUT', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/GP-Cam-09',[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        dd($json);
        return true;
    }
}

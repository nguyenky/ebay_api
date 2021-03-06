<?php

namespace App\Http\Controllers\Dropbox;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dropbox;
use Purl\Url;
use App\Token;
use Auth;
use App\Jobs\CreateJobOffer;

class TestController extends Controller
{
    protected  $path   = '/picture';
    protected  $mode   = 'filename';
    protected  $query  = 'UNITEX-DATAFEED-ALL.csv';
    protected  $friend;
    protected  $access_token_ebay;
    protected  $filecsv;

    public function __construct()
    {
    	$token = \App\Token::find(1);
        $this->friend = $token;  
    }
    public function index()
    {   
        \Log::info('---------------------');
        $matches = $this->step1DropboxSearchFileCsv();
        $filename = $this->step2DropboxDownFileCsv($matches);
        $csv = $this->step3DropboxConvertFileCsv('files/'.$filename);
    
        $getAccessToken = $this->step4EbayRefreshToken();

        $token = Token::find($this->friend->id);
        $token->accesstoken_ebay = $getAccessToken['access_token'];
        $token->save();
        // dd('123');
        $this->step5EbayCreadtItems($csv);
        \Log::info('---------------------');
    }
    public function step1DropboxSearchFileCsv(){

    \Log::info('Job [Ebay] START Search File '. now());
    try {       
            $data = json_encode(
                    [
                        'path' => $this->path,
                        'mode' => $this->mode,
                        'query' => $this->query,
                    ]
                );

            $response = Dropbox::api()->request(
                'POST', '/2/files/search',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->friend->accesstoken_dropbox,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $data
            ]);

            $search_results = json_decode($response->getBody(), true);

            $matches = $search_results['matches'];
            \Log::info('Job [Ebay] SUCCESS Search File at '. now());
            
            

           
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL Search File at '. $e);
            dd($e);
        }
        \Log::info('Job [Ebay] END Search File at '. now());
             return $matches;
    }

    public function step2DropboxDownFileCsv($matches){
        \Log::info('Job [Ebay] START Down File at '. now());
        try {
           
            if($matches == null) {
                return;
            }
            else {
                $data = json_encode([
                        'path' => $matches[0]['metadata']['path_lower']
                    ]);
                $response = Dropbox::content()->request(
                    'POST',
                    '/2/files/download',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' .$this->friend->accesstoken_dropbox,
                            'Dropbox-API-Arg' => $data
                        ]
                ]);

                $result = $response->getHeader('dropbox-api-result');
                $file_info = json_decode($result[0], true);

                $content = $response->getBody();
                $filename = $file_info['name'];
                $file_extension = substr($filename, strrpos($filename, '.'));

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
            \Log::info('Job [Ebay] SUCCESS Down File at '. now());
        } 
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL Down File at '. $e);
            dd($e);
        }
        \Log::info('Job [Ebay] END Down File at '. now());
        return $filename;
    }

    public function step3DropboxConvertFileCsv($attribute){
        // dd($attribute);
        $csv = Array();
        $rowcount = 0;
        $file =  public_path($attribute);
        if (($handle = fopen($file, "r")) !== FALSE) {
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

        \Log::info('Job [Ebay] START Refresh Token at '. now());
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
                'refresh_token'=>$this->friend->refresh_token_ebay,
                'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
            ];
            $res = $client->request('POST', 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',[
                                'headers'=> $header,
                                'form_params'  => $body
                            ]);

            $search_results = json_decode($res->getBody(), true);
            $this->access_token_ebay = $search_results['access_token'];
            \Log::info('Job [Ebay] SUCCESS Refresh Token at '. now());
        }
         catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL Refresh Token at '. $e);
            dd($e);
        }
        \Log::info('Job [Ebay] END Refresh Token at '. now());
        return $search_results;
    }

    public function step5EbayCreadtItems($attributes){
        \Log::info('Job [Ebay] START step5 Ebay Create item ');
        try {
            foreach ($attributes as $key => $attribute) {
            	if($key == 102){
                \Log::info('--------START-----'.$key.'--------');
                \Log::info('Job [Ebay] START step5 Ebay Create item '. $key);
	            $namefile = Array();
	            $data = Array();
	            $data[] = $attribute['Image1'];
	            $data[] = $attribute['Image2'];
	            $data[] = $attribute['Image3'];
	            $data[] = $attribute['Image4'];
	            $data[] = $attribute['Image5'];
	            foreach ($data as $key => $item) {

	                $value = json_encode(
	                    [
	                        'path' => '/DROPSHIP/IMAGES/2018 COLLECTIONS',
	                        'mode' => 'filename',
	                        'query' => $item
	                    ]
	                );
	                $response = Dropbox::api()->request(
	                'POST', '/2/files/search',
	                [
	                    'headers' => [
	                        'Authorization' => 'Bearer ' . $this->friend->accesstoken_dropbox,
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

	            $product = $this->step5_2CreateItem($attribute,$namefile);

	            // //------------- Delete Image -------------
	            foreach ($namefile as $key => $item) {
	                 unlink(public_path('files/'.$item));
	            }
	            // ------------- End Delete Image -----------
	            \Log::info('Job [Ebay] END step5 Ebay Create item '. $key);
	            \Log::info('--------END'.$key.'--------');
            }
            \Log::info('Job [Ebay] SUCCESS step5 Ebay Create item ');
            // return "Update finish";
           }

        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL Create Item at '. $e);
            dd($e);
        }
        \Log::info('Job [Ebay] END step5 Ebay Create item ');

        
    }

        public function step5_1downloadImage($attribute){
 
        $data = json_encode([
                'path' => $attribute
            ]);

            $response = Dropbox::content()->request(
                'POST',
                '/2/files/download',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' .$this->friend->accesstoken_dropbox,
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

    public function getItems($attribute,$namefile){
        \Log::info('Job [Ebay] START GET ITEM '. now());
        try {
            $client = new \GuzzleHttp\Client();
            $header = [
                'Authorization'=>'Bearer '.$this->friend->accesstoken_ebay,
                'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/'.$attribute['SKU'],[
                            'headers'=> $header,
                        ]);
            $search_results = json_decode($res->getBody(), true);
            \Log::info('Job [Ebay] SUCCESS GET ITEM '. now());
            return $search_results;
        }
         catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL GET ITEM '. now());
             if($e->getCode() == 404){
                $this->createItemsEbay($attribute,$namefile);
                $this->step5_2CreateItem($attribute,$namefile);
                return null;
            }
        }
       
    }

    public function step5_2CreateItem($attribute,$namefile){
        \Log::info('Job [Ebay] START step5_2 Create Item '. now());

        try {

            $search_results = $this->getItems($attribute,$namefile);
            if($search_results){


	            $product = $search_results['product'];
	         
	            if($product['title'] == $attribute['Name'] && $product['description'] == $attribute['Description']){
	                    if($product['aspects']['pileheight'][0] == $attribute['Pileheight'] && $product['aspects']['height'][0] == $attribute['Height'] && $product['aspects']['color'][0] == $attribute['Color'] && $product['aspects']['width'][0] == $attribute['Width'] &&$product['aspects']['length'][0] == $attribute['Length'] && $product['aspects']['unitweight'][0] == $attribute['UnitWeight'] && $product['aspects']['construction'][0] == $attribute['Construction'] && $product['aspects']['material'][0] == $attribute['Material'] && $product['aspects']['size'][0] == $attribute['Size']){
	                            
	                    } else {
	                        $this->createItemsEbay($attribute,$namefile);
	                    }

	            } else {
	               $this->createItemsEbay($attribute,$namefile);
	            }
	                // return $product;
	            \Log::info('Job [Ebay] SUCCESS step5_2 Create Item '. now());
            }
        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] step5_2 Create Item '. $e);
           
        }
        \Log::info('Job [Ebay] END step5_2 Create Item '. now());

    }

    public function createItemsEbay($attribute,$filename){
        \Log::info('Job [Ebay] START create item ebay at '. now());
        try {

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
                    'description'=> 'Description',
                    // 'description'=> $attribute['Description'],
                    'cost' => $attribute['Cost (Ex.GST) '],
                    'sell' => $attribute['Sell'],
                    'rrp' => $attribute['RRP'],
                    'origin' => $attribute['Origin'],
                ]
            ];
            $json = json_encode($data);
            $header = [
            'Authorization'=>'Bearer '.$this->friend->accesstoken_ebay,
            'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
            'Content-Language'=>'en-US',
            'Content-Type'=>'application/json'
        ];
            $res = $client->request('PUT', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/'.$attribute['SKU'],[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        \Log::info('Job [Ebay] SUCCESS create item ebay at '. now());
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL create item ebay at '. now());
            dd($e);
        }
        \Log::info('Job [Ebay] END create item ebay at '. now());      

    }

    public function test(){
        $token = \App\Token::find(1);
        $offerId;
       
        $offer = $this->getOffers($token->accesstoken_ebay,'401-OATMEAL-300X80');
        // dd($offer);
        if($offer != NULL) {
            
            $this->deleteOffer($token->accesstoken_ebay,$offer[0]['offerId']);
            $offerId = $this->postOffer($token->accesstoken_ebay,'Hoa-02');
        
        } else {
           
            $offerId = $this->postOffer($token->accesstoken_ebay,'Hoa-02');
            $this->publishOffer($offerId,$token->accesstoken_ebay);
        }
        // dispatch(new CreateJobOffer);
    }

    public function deleteOffer($token,$offerID){
    \Log::info('Job Delete Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('DELETE', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer/'.$offerID,[
                            'headers'=> $header,
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results["offers"][0]["offerId"]);
        \Log::info('Job Delete Offer SUCCESS at '. now());
        return null;
        } catch(\Exception $e) {
             \Log::info('Job Delete Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Delete Offer END at '. now());
    }

    public function postOffer($token,$sku){
    // $token = \App\Token::find(1);

     \Log::info('Job Create Offer START at '. now());
        try {

            $client = new \GuzzleHttp\Client();
            $data = [];
            $data = [
                "sku"  =>$sku,
                "marketplaceId" => "EBAY_US",
                "format" => "FIXED_PRICE",
                "listingDescription" => "234",
                "availableQuantity" => 2,
                "quantityLimitPerBuyer" => 2,
                "pricingSummary" => [
                        "price"  => [
                            "value" => 0.99,
                            "currency" => "USD"
                        ]
                ],
                "listingPolicies" => [
                    "fulfillmentPolicyId" => "5808775000",
                    "paymentPolicyId"     => "5808776000",
                    "returnPolicyId"      => "5808774000"
                ],
                "categoryId" => "88433",
                "merchantLocationKey" => "loc-001",
                "tax" => [
                    "vatPercentage" => 10.2,
                    "applyTax"  => true,
                    "thirdPartyTaxCategory" => "Electronics"

                ]
            ];

            $json = json_encode($data);
            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('POST', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer',[
                            'headers'=> $header,
                            'body'  => $json
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        \Log::info('Job Create Offer SUCCESS at '. now());
        return $search_results;
        
        } catch(\Exception $e) {
             \Log::info('Job Create Offer FAIL at '. now());
             dd($e);
        }
        \Log::info('Job Create Offer END at '. now());
    }

    public function getOffers($token,$sku){

     \Log::info('Job Get Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('GET', 'https://api.sandbox.ebay.com/sell/inventory/v1/offer?sku='.$sku,[
                            'headers'=> $header,
                        ]);
        $search_results = json_decode($res->getBody(), true);
        // dd($search_results["offers"][0]["offerId"]);
        \Log::info('Job Get Offer SUCCESS at '. now());
        return $search_results["offers"];
        
        } catch(\Exception $e) {
             \Log::info('Job Get Offer FAIL at '. now());
             if($e->getCode()==404) {
                // $this->postOffer($token,$sku)
                return null;
             }
        }
        \Log::info('Job Get Offer END at '. now());
    }

    public function publishOffer($orderId,$token) {
        
             \Log::info('Job Publish Offer START at '. now());
        try {
            $client = new \GuzzleHttp\Client();

            $header = [
                'Authorization'=>'Bearer '.$token,
                'Accept'=>'application/json',
                'Content-Language'=>'en-US',
                'Content-Type'=>'application/json'
            ];
            // dd($json);
            $res = $client->request('POST','https://api.sandbox.ebay.com/sell/inventory/v1/offer/'.$orderId.'/publish',[
                            'headers'=> $header,
                        ]);
        $search_results = json_decode($res->getBody(), true);
      
        \Log::info('Job Publish Offer SUCCESS at '. now());
        return $search_results["offers"];
        
        } catch(\Exception $e) {
             \Log::info('Job Publish Offer FAIL at '. now());
             if($e->getCode()==404) {
                // $this->postOffer($token,$sku)
                return null;
             }
        }
        \Log::info('Job Publish Offer END at '. now());
    }
}

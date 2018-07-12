<?php

namespace App\Http\Controllers\Ebay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EbayController extends Controller
{

	protected $api;
	protected $tokenGloble;

	public function __construct()
    {
        if(env('EBAY_SERVER') == 'sandbox'){

        	$this->api = 'https://api.sandbox.ebay.com/';

        }else{

        	$this->api = 'https://api.ebay.com/';
        }
        $this->tokenGloble = \App\Token::find(1); 
    }

    public function index(){

    	return view('ebay.ebay');
    }
    // public function begin(){

    // 	\Log::info('-------- START PROCESS -------------');

    //     $csv = $this->step3DropboxConvertFileCsv('files/'.$filename);

    //     $this->step5EbayCreadtItems($csv);

    //     \Log::info('-------- END PROCESS -------------');

    //     return view('ebay.ebay');
    // }

    public function showItems(){

    	$filename = 'files/myproducts.csv';

  		if(\File::exists($filename)){

  			$csv = $this->step3DropboxConvertFileCsv($filename);

        }else{
        	$csv = [];
        }
    	

    	return view('ebay.items',['items'=>$csv]);

    }
    public function createInventoryItem($key){

    	$filename = 'files/myproducts.csv';

  		if(\File::exists($filename)){

  			$csv = $this->step3DropboxConvertFileCsv($filename);

        }
        $item = $csv[$key];

        $createInventoryItem = $this->createInventory($item);

    	// dd($csv[$key]);
    	return view('ebay.item',['item'=>$item]);
    }

    public function createInventory($attribute){
    	// dd('asdsd');
    	$this->freshAccessToken();

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
                    'construction' => [$attribute['Construction'] ? $attribute['Construction'] : 'need additional'],
                    'material' => [$attribute['Material']],
                    'pileheight' => [$attribute['Pileheight']]
                ],
                'category' => $attribute['Category'],
                'description'=> 'description',
                'cost' => $attribute['Cost (Ex.GST) '],
                'sell' => $attribute['Sell'],
                'rrp' => $attribute['RRP'],
                'origin' => $attribute['Origin'],
            ]
        ];
        // dd($data);
        $json = json_encode($data);
        $header = [
            'Authorization'=>'Bearer '.$this->tokenGloble->accesstoken_ebay,
            'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
            'Content-Language'=>'en-US',
            'Content-Type'=>'application/json'
        ];
        // dd($json);
        $res = $client->request('PUT', $this->api.'sell/inventory/v1/inventory_item/'.$attribute['SKU'],[
                        'headers'=> $header,
                        'body'  => $json
                    ]);
    	$search_results = json_decode($res->getBody(), true);
    	// dd($search_results);
    	return null;
    }
    public function freshAccessToken(){
    	$token = \App\Token::find(1);

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
            'refresh_token'=>$token->refresh_token_ebay,
            'scope'=>'https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.inventory',
        ];
        // dd($body);
        $res = $client->request('POST', $this->api.'identity/v1/oauth2/token',[
                            'headers'=> $header,
                            'form_params'  => $body
                        ]);

        $search_results = json_decode($res->getBody(), true);
        // dd($search_results);
        // $token = Token::find(1);
        if($token->accesstoken_ebay != $search_results['access_token']){
            $token->accesstoken_ebay = $search_results['access_token'];
            $token->save();
        }
        return null;
                        
    }

    public function step3DropboxConvertFileCsv($attribute){

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
            fclose($handle);
        }
        else {
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        return $csv;
    }

}

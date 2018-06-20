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
    private $access_token_ebay = 'Bearer v^1.1#i^1#r^0#f^0#p^3#I^3#t^H4sIAAAAAAAAAOVXa2gURxzP5aVpmtRqaUwb8Ngo1sjezdze3T5izl4e1quPxFxio1ZkH7OX1b3d686syYqlMVCpKDQfCm2hqB+KglQiFAqtKKWt0kIpFGwp2PhJwVqh9kWRinT28vCSouYhJdD7cjcz/+fv//v/bwYMlFc0HFx/8K+qwILi4wNgoDgQgJWgorxsdXVJ8TNlRaBAIHB8YPlA6WDJ9TVYzpo5qRPhnG1hFOzPmhaW8ptNjOtYki1jA0uWnEVYIqqUTm7aKEVCQMo5NrFV22SCqdYmBimqHgURQYvFY4DnRbprjdvsspsYWdD1uKKKIkTRmBBT6DnGLkpZmMgWaWIiAAosiLMR0AV4CXJSlA8BCLczwa3IwYZtUZEQYBL5cKW8rlMQ64NDlTFGDqFGmEQquS7dnky1tm3uWhMusJUYwyFNZOLiyasWW0PBrbLpoge7wXlpKe2qKsKYCSdGPUw2KiXHg5lF+HmoZQXGBKBpUagLEEYfDZTrbCcrkwfH4e8YGqvnRSVkEYN4D0OUoqHsRioZW22mJlKtQf9riyubhm4gp4lpa05u6063dTLBdEeHY+81NKT5mUIuCnkuKkaYBEGYQoicXSayMq6HrD3emLdRk2NYT3HXYlua4SOHg5tt0oxo6GgqQKAAICrUbrU7SZ34YRXKieNAgsh2v7KjpXRJr+UXF2UpGsH88uFlGOfFPSY8KmaIAkSizok85LQ45NVCZvi9Plt2JPwCJTs6wn4sSJE9Nis7exDJmbKKWJXC62aRY2gSF9MjnKAjVouLOhsVdZ1VYlqchTpCACGFDgDhf0cSQhxDcQmaIMrUg3ymTUxatXOowzYN1WOmiuSnzxgt+nET00tITgqH+/r6Qn1cyHYy4QgAMNyzaWNa7UVZmZmQNR4uzBp5zqqIamFDIl6ORtNP+UedWxkmwTlah+wQr9n16DqNTJN+jXN4UoSJqbv3SRX7qc6vJH19TA3IOSPkUzyk2tmwLdOW9rd25SMOTkcorLge9a8hJ+QgWbMt05u+XsalFB7Vnp4SptUIjXYjTWPMo9/r0/U62cAMdAxrL+Wy7XgzTHOy8gx0ZFW1XYvMxt2Y6gw0dNfUDdP023U2DgvUZxKmJZseMVQ8G5cFI5nCi41ML5mpHbpH5zjVV2Uim3ZmQn1OzZ7M5VLa/Gr29LrOtK2TPhbrDs7/SDf3sHxEVRUNIcTqKhJkAGJzyrsV7f0P8i4dLB6c2TSPoxiHOJ5FUAFsVI5qrKhEEAthjEccxwn0f3pOebeYBqV9lzffJvx6GxOkzS01etuaX0n5vB2nrRzjIasLGr15qYLIChF6/YoqCppuyuH73lf+dV8NT341JoryHzgYOAcGAx/ThyfgAQtXg1XlJd2lJY8z2CAohGVLU+z+kCHrITqfLPooclBoD/JysuEUlwd2PHtj7Z2C9+rxnWDpxIu1ogRWFjxfQd29kzL4RE0VFECcQkevvlF+O6i/d1oKny596pdWu7/ykLfib+mmrr9+4Z3FNaIFqiaEAoGyotLBQFHvxe5bL238dBHflsqFT9YereT0s8fgmc/ONNq1wyUvL97ffufyJfG54S/fWNq49trZI3Unl7wV2Hd1WHj+9KFvF1zZ8fm+VwOa/dOBN+L9i34+/+7VAwtv/CE2XnvzGPvCoT8/emwF+8UHy3lov9/V0bWrWm38bTiR3PDa3b7MAfOVkVU/PHnh4OJzt1aqDbX8rzvdihPZu4Pmd7fxEL7SfPRW3cjumiPVaFk1anivwVg51PL924cj26DD9Wwpti6dV3c7JxbGhn4c6qkrrqna32xv6s1cSlwnSw7X19Z/1Vhb9PWH/IbbXcMnB1oPj6w58uLQJ6vsb07VDyfv/n7zdOepUG6k++Kyy6Nl/AdTP3IeSRAAAA==';


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

    public function createItemsEbay($product){
        $data = [];
        $data = [
            'availability'  => [
                'shipToLocationAvailability'    => [
                    'quantity'  => $product['QTY'],
                ]
            ],
            'condition'     => 'NEW',
            'product'       => [
                'title'     => $product['Name'],
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
                'category' => 'SDSAD'
            ]
        ];
        $json = json_encode($data);

        $client = new \GuzzleHttp\Client();
        $header = [
            'Authorization'=>$accessToken,
            'X-EBAY-C-MARKETPLACE-ID'=>'EBAY_US',
            'Content-Language'=>'en-US',
            'Content-Type'=>'application/json'
        ];
        $res = $client->request('PUT', 'https://api.sandbox.ebay.com/sell/inventory/v1/inventory_item/GP-Cam-09',[
                            'headers'=> $header,
                            'body'  => $json
                        ]);

        // dd($res);
        dd($json);
        return true;
    }
}

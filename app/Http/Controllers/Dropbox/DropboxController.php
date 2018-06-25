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
        // dd($request->all());
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
            // dd($data);
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

    public function downloadImage($attribute){
        //dd($attribute);
        $data = json_encode([
                'path' => $attribute
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

    public function download(Request $request){
        // dd($attribute);
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

    public function parent_file_csv($attribute) {
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

    public function uploadFileEbay(){
    
        $csvfile = 'files/lenguyenky.csv';

        $csv = $this->parent_file_csv($csvfile);

        $data = Array();
        $namefile = Array();
        foreach ($csv as $key => $value) {
            $data[] = $value['Image1'];
            $data[] = $value['Image2'];
            $data[] = $value['Image3'];
            $data[] = $value['Image4'];
            $data[] = $value['Image5'];
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
                        'Authorization' => 'Bearer ' . \Auth::user()->remember_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $value
                ]);
                $search_results = json_decode($response->getBody(), true);
                $matches = $search_results['matches'];
                foreach ($matches as $key => $value) {
                    $this->downloadImage($value['metadata']['path_lower']);
                    $namefile[] = $value['metadata']['name'];
                    break;
                }
                
                 //------ Delete image -------
                    // unlink(public_path('/files/'.$value['metadata']['name']));
            }
            dd($namefile);
            die;            
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
            // $data[$key] = $value['Image1'];
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
    }

    public function getAllProduct(){
        $products = Product::all();
        dd($products);
    }
    /// ----- Download dropbox ----

    
    
    ///------

    public function createItemsEbay(){

        return true;
    }
}

<?php

namespace App\Http\Controllers\Dropbox;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dropbox;
use Purl\Url;
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
        dd($user);
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
        // dd('asdsd');

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
            // dd($result);
            $file_info = json_decode($result[0], true);
            // dd($file_info);
            $content = $response->getBody();
            // dd($content);
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
            // return redirect('search');
        } else {
            return redirect('search');
        }
    }

    // public function testApi(){

    //     $data = json_encode(
    //         [
    //             'path'=>'/DROPSHIP/PRODUCT INFORMATION FILE - SHOPIFY',
    //             "recursive"=>false,
    //             "include_media_info"=>false,
    //             "include_deleted"=>false,
    //             "include_has_explicit_shared_members"=>false,
    //             "include_mounted_folders"=>true
    //         ]
    //     );
    //     // dd(\Auth::user()->remember_token);
    //     $response = $this->api_client->request(
    //         'POST', '/2/files/list_folder',
    //         [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . \Auth::user()->remember_token,
    //                 'Content-Type' => 'application/json'
    //             ],
    //             'body' => $data
    //     ]);
    //     // dd('asd');
    //     $search_results = json_decode($response->getBody(), true);
    //     dd($search_results);

    // }

    public function editFile(){
        $pathPublic = public_path().'/files/lenguyenky.csv';

        // $list = array
        //     (
        //     "Peter,Griffin,Oslo,Norway",
        //     "Glenn,Quagmire,Oslo,Norway",
        //     );

        //     $file = fopen("contacts.csv","w");

        //     foreach ($list as $line)
        //     {
        //     fputcsv($file,explode(',',$line));
        //     }
        //     dd(fgetcsv($file));
        //     fclose($file);
        // dd($pathPublic);
        // $path = 'lenguyenky.csv';
        // $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // $lineCount = count($lines);
        // for ($i = 1; $i < $lineCount; $i++){                    
        //     $cols = str_getcsv(trim($lines[$i]), ",");
        //     dd($cols);                    // if 2 columns cannot be extracted, try semicolon                    
        //     if (count($cols) < 22){                        
        //         $cols = str_getcsv(trim($lines[$i]), ";");                        // skip lines with insufficient columns                        
        //         if (count($cols) < 22) {                            
        //             error_log("Line $i only has " . count($cols) . ' columns');                            
        //             continue;                        
        //         }                    
        //     }
        // }
        
        // dd($lines);
        // $path = 'lenguyenky.csv';
        // $csv = explode("\n", file_get_contents($path));
        // foreach ($csv as $key => $line)
        //     {
        //         $csv[$key] = str_getcsv($line);
        //     }
        
        // dd($csv);
        // $file = fopen($path,"r");
        // dd(array_map('str_getcsv',fgetcsv($file)));
        // fclose($csv);
        // dd('ád');

        $path = 'files/lenguyenky.csv';
        $csva = file_get_contents($path);
        $no_blanks = str_replace("\r\n\r\n", "\r\n", $csva);
        file_put_contents($path, $no_blanks);

       
        $csv = explode(PHP_EOL, file_get_contents($path));
        foreach ($csv as $key => $line)
            {
                $csv[$key] = str_getcsv($line);
            }
        dd($csv);




        $csv =[];
        if(\File::exists($pathPublic)){

            if (($handle1 = fopen($path, "r")) !== FALSE) {
                // dd(fgetcsv($handle1));
                if (($handle2 = fopen($path, "r")) !== FALSE) {
                    // dd(fgetcsv($handle2));
                    // $data = fgetcsv($handle2);
                    // dd($data);
                    while (($data = fgetcsv($handle1,1000)) !== FALSE) {
                        // dd('ád');
                       // Alter your data
                    //    $data[0] = '...';
                    dd($data);
                    // $csv[]=$data;
                    // dd
            
                       // Write back to CSV format
                       fputcsv($handle2, $data);
                    }
                    dd('get false');
                    fclose($handle2);
                }else{
                    dd('false handle 2');
                }
                fclose($handle1);
            }else{
                dd('false handle 1');
            }
            
        }else{
            dd('no');
        }

    }
}

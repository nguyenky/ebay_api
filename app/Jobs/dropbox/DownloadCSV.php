<?php

namespace App\Jobs\dropbox;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Dropbox;
class DownloadCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected  $path;
    protected  $mode;
    protected  $query;

    protected  $token;

    public function __construct()
    {   
        $this->path = env('DROPBOX_PATH');
        $this->mode =  env('DROPBOX_MODE');
        $this->query = env('DROPBOX_QUERY');

        $this->token = \App\Token::find(1);

        // dd($this->token);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('START download file csv process !');
        $searchFile = $this->searchFile();

        if(!$searchFile){
            \Log::info('ERROR search file csv !');
            \Log::info('END download file csv process !');
            // return false;
        }

        $downloadFile = $this->downloadFile($searchFile);

        if(!$downloadFile){
            \Log::info('ERROR down file csv !');
            \Log::info('END download file csv process !');
            // return false;
        }
        
        $system = \App\System::find(1);
        $system->filecsv = $downloadFile;
        $system->save();

        \Log::info('SUCCESS download file csv process !');
        \Log::info('END download file csv process !');
        // return true;
    }

    public function searchFile(){
        \Log::info('START search file csv  !');
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
                        'Authorization' => 'Bearer ' . $this->token->accesstoken_dropbox,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $data
            ]);
            $search_results = json_decode($response->getBody(), true);

            $matches = $search_results['matches'];

            \Log::info('SUCCESS search file csv  !');

            \Log::info('END search file csv  !');

            return $matches;
        }
        catch(\Exception $e) {

            \Log::info('ERROR search file csv - '.$e);

            \Log::info('END search file csv  !');

            return false;

        }   
    }
    public function downloadFile($matches){
        \Log::info('START down file csv  !');
        try {
           
            if($matches == null) {
                return false;
            }

            $data = json_encode([
                    'path' => $matches[0]['metadata']['path_lower']
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
            \Log::info('SUCCESS down file csv  !');
            \Log::info('END down file csv  !');
            return $filename;

        } 
        catch (\Exception $e){
            \Log::info('ERROR down file csv  !');
            \Log::info('END down file csv  !');
            return false;
        }

    }

}

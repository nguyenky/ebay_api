<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;


class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filecsv;
    protected $friend;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($csv,User $user)
    {
        $this->filecsv = json_decode($csv,true);
        $this->friend = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->step5EbayCreadtItems($this->filecsv);
    }

    public function step5EbayCreadtItems($attributes){

        try {
            // dd($this->friend->accesstoken_dropbox);
            \Log::info('Job [Ebay] START step5 Ebay Create item ');
            // foreach ($attributes as $key => $attribute) {
            $namefile = Array();
            $data = Array();
            $data[] = $attributes['Image1'];
            $data[] = $attributes['Image2'];
            $data[] = $attributes['Image3'];
            $data[] = $attributes['Image4'];
            $data[] = $attributes['Image5'];
            // // dd($data);
             // \Log::info('Log  '.$attribute);
            // dd($this->user);
            foreach ($data as $key => $item) {
                // dd($item);
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

            // // dd($namefile);
            $product = $this->step5_2CreateItem($attributes,$namefile);
            // dd($product);
            // //------------- Delete Image -------------
            foreach ($namefile as $key => $item) {
                 unlink(public_path('files/'.$item));
            }
            // ------------- End Delete Image -----------

            // }
            \Log::info('Job [Ebay] END step5 Ebay Create item ');
            return "Update finish";

        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] FAIL Create Item at '. $e);
            dd($e);
        }

        
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

    public function getItems($attribute,$namefile){
        try {
            // dd($attribute);

            \Log::info('Job [Ebay] START GET ITEM '. now());
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
            \Log::info('Job [Ebay] END GET ITEM '. now());
            return $search_results;
        }
         catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL GET ITEM '. now());
             if($e->getCode() == 404){
                $this->createItemsEbay($attribute,$namefile);
                $this->step5_2CreateItem($attribute,$namefile);
            }
        }
       
    }

    public function step5_2CreateItem($attribute,$namefile){

        try {
            \Log::info('Job [Ebay] START step5_2 Create Item '. now());

            $search_results = $this->getItems($attribute,$namefile);
            // dd($search_results);
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
            \Log::info('Job [Ebay] END step5_2 Create Item '. now());
        }
        catch (\Exception $e){
            \Log::info('Job [Ebay] step5_2 Create Item '. $e);
           
        }

    }

    public function createItemsEbay($attribute,$filename){

        try {
            \Log::info('Job [Ebay] START create item ebay at '. now());

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
        \Log::info('Job [Ebay] END create item ebay at '. now());
        }
        catch(\Exception $e) {
            \Log::info('Job [Ebay] FAIL create item ebay at '. now());
            dd($e);
        }      
        // dd($this->access_token_ebay);
    }
}

<?php
namespace App;

use GuzzleHttp\Client;

class Dropbox 
{
    public static function api()
    {
        $client = new Client([
            'base_uri' => 'https://api.dropboxapi.com',
        ]);
        return $client;
    }

    public static function content()
    {
        $client = new Client([
            'base_uri' => 'https://content.dropboxapi.com'
        ]);

        return $client;
    }

}
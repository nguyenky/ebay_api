<?php

use Illuminate\Database\Seeder;
use App\Token;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        Token::truncate();
    	$grant_code= '"v^1.1#i^1#I^3#p^3#f^0#r^1#t^Ul41XzM6M0E5QzgxRjZGMkE2QTNDRDIxNDgyMDYzMEQ1ODNERUJfMl8xI0VeMTI4NA==';
        Token::create([
        	'grant_code'=>$grant_code,
        	'accesstoken_dropbox'=>'cRJ80fE5KDAAAAAAAAA0ngtuNCWtTuF4Jg9AfeaAMCXCKVNntavrE8UNXZ3jfBmV',
        	'refresh_token_ebay'=>'v^1.1#i^1#r^1#I^3#f^0#p^3#t^Ul4xMF8wOjYxOTBFMjJERUFFNzIxNUM4QjhDNDk2MzExQTgwMEZEXzJfMSNFXjEyODQ=',
        	'accesstoken_ebay'=>null,
        	]);

    }
}

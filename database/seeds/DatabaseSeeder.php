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
        $token_sandbox_ky=[
            'grant_code'=>'v^1.1#i^1#I^3#p^3#f^0#r^1#t^Ul41XzM6M0E5QzgxRjZGMkE2QTNDRDIxNDgyMDYzMEQ1ODNERUJfMl8xI0VeMTI4NA==',
            'accesstoken_dropbox'=>'cRJ80fE5KDAAAAAAAAA0ngtuNCWtTuF4Jg9AfeaAMCXCKVNntavrE8UNXZ3jfBmV',
            'refresh_token_ebay'=>'v^1.1#i^1#r^1#I^3#f^0#p^3#t^Ul4xMF8wOjYxOTBFMjJERUFFNzIxNUM4QjhDNDk2MzExQTgwMEZEXzJfMSNFXjEyODQ=',
        ];
        $token_live_ky= [
            'grant_code'=>'v^1.1#i^1#p^3#I^3#f^0#r^1#t^Ul41Xzc6NkZDMDk0QTM4NUFGMTM5NzQxODVDNjMwQjkyRjJBMzVfMl8xI0VeMjYw',
            'accesstoken_dropbox'=>'cRJ80fE5KDAAAAAAAAA0ngtuNCWtTuF4Jg9AfeaAMCXCKVNntavrE8UNXZ3jfBmV',
            'refresh_token_ebay'=>'v^1.1#i^1#p^3#I^3#f^0#r^1#t^Ul4xMF8xOkEyOURFNkNBN0MyRkZGODlDMTIwNTczQ0Y4OTczODY2XzJfMSNFXjI2MA==',
        ];
        $token_live_lars = [
            'grant_code'=> 'v^1.1#i^1#I^3#r^1#p^3#f^0#t^Ul41XzM6QTM3M0JFMDkwMEM1NTk0MTIzREExOUExMjczRDk5OERfMF8xI0VeMjYw',
            'accesstoken_dropbox'=>'cRJ80fE5KDAAAAAAAAA0ngtuNCWtTuF4Jg9AfeaAMCXCKVNntavrE8UNXZ3jfBmV',
            'refresh_token_ebay'=>'v^1.1#i^1#r^1#I^3#f^0#p^3#t^Ul4xMF81OkM1ODJDQkJGMTAwNTdFMzJEQzk4RkZCQkUyNDlBNjRBXzJfMSNFXjI2MA==',
        ];
        Token::truncate();
        Token::create($token_live_lars);

    }
}

<?php

namespace App\Http\Controllers\Unitex;

use App\GenericImport;
use App\Http\Controllers\Controller;
use App\Jobs\unitex\UnitexDropboxRefresh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropboxRefreshController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function review(Request $request)
    {
        if(UnitexDropboxRefresh::parseCsvAndSave($request->id)){
            if($stats=$this->getUploadedStats($request->id)){
                return view('unitex.dropbox-refresh.review')
                    ->with("success","Successfully ");
            }else{
                return view('unitex.dropbox-refresh.index')->with("error",$result);
            }
        }else{
            return view('unitex.dropbox-refresh.index')->with("error",$result);
        }
    }

    public function upload(Request $request){
        $result="[FATAL ERROR] Unknown error :S.";
        $path=$request->file('file')->store('public/unitex');
        if($path){
            $item=GenericImport::create([
                "user_id"=>Auth::user()->id,
                "original"=>$request->file('file')->getClientOriginalName(),
                "path"=>$path
            ]);
            if($item){
                return(redirect()->route("unitex.dropbox-refresh.review",["id"=>$item->id]));
            }else{
                $result="[FATAL ERROR] Could not prepare the import in the database.";
            }
        }else{
            $result="[FATAL ERROR] Could upload the file.";
        }
        return view('unitex.dropbox-refresh.index')->with("error",$result);
    }
    public function index(){
        return view('unitex.dropbox-refresh.index');
    }
}
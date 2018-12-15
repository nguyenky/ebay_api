<?php

namespace App\Http\Controllers\Tools;

use App\GenericImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GenericImportController extends Controller
{
    public function getTableList(){
        $result=[];
        $tables=DB::select("SHOW TABLES");
        if(count($tables)){
            foreach($tables as $table){
                $result[]=$table->Tables_in_ebay_api;
            }
        }
        return($result);
    }
    public function options(){
        $rows=[];
        $tables=$this->getTableList();
        $id=\request("id",0);
        if($gi=GenericImport::find($id)){
            $file = fopen(storage_path("app/".$gi->path), 'r');
            while (($line = fgetcsv($file)) !== FALSE) {
                //$line is an array of the csv elements
                $rows[]=$line;
            }
            fclose($file);
        }
        return view('tools.generic-import.options',[
            "tables"=>$tables,
            "rows"=>$rows,
        ]);
    }
    public function upload(Request $request){
        $path=$request->file('file')->store('public/uploads/generic-imports');
        dump($path);
        if($path){
            $item=GenericImport::create([
                "user_id"=>Auth::user()->id,
                "path"=>$path
            ]);
            if($item){
                return(redirect()->route("generic-file-import-tools-options",["id"=>$item->id]));
            }else{
                dd("[FATAL ERROR] Could not prepare the import in the database.");
            }
        }else{
            dd("[FATAL ERROR] Could upload the file.");
        }
        return view('tools.generic-import.index',["items"=>[]]);
    }
    public function index(){
        return view('tools.generic-import.index',["items"=>[]]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    //
    public function index(){
        return view('messenger.index');
    }
    /**
     * Search User Profiles
     */
    public function userSearch(Request $request){
       // dd($request->all());
      // if($request->has('query')){
        $input=$request['query'];
        // dd($query);
         $getRecords=null;
         $records=User::where('id','!=',Auth::user()->id)
             ->where('name','LIKE',"%{$input}%")
             ->orWhere('user_name','LIKE',"%{$input}%")
             ->paginate(10);
             
        foreach($records as $record){
            $getRecords.=view('messenger.components.search-item',compact('record'))->render();
        }
        return response()->json([
            'records'=>$getRecords,
            'last_page'=>$records->lastPage()
        ]);
       //}
      
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Message;
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
         if($records->total() <1){
            $getRecords.="<p class='text-center'>Nothing to show.</p>";
         }       
        foreach($records as $record){
            $getRecords.=view('messenger.components.search-item',compact('record'))->render();
        }
        return response()->json([
            'records'=>$getRecords,
            'last_page'=>$records->lastPage()
        ]);
       //}
    }
       //Fetch user by Id
       public function fetchIdInfo(Request $request){
            // dd($request->all());
            $getUserInfo=User::where('id',$request['id'])->first();

            return response()->json(
                [
                    'getuserinfo'=>$getUserInfo
                ]
            );
       }

       function sendMessage(Request $request){
            // dd($request->all());
            $request->validate([
                'message'=>['required'],
                'id'=>['required','integer'],
                'temporaryMsgId'=>['required']
            ]);

            //store message in db
            $message=new Message();
            $message->from_id=Auth::user()->id;
            $message->to_id=$request->id;
            $message->body=$request->message;
            $message->save();

            return response()->json([
                'message'=>$this->messageCard($message),
                'tempID'=>$request->temporaryMsgId
            ]);


       }
       function messageCard($message){
          return view('messenger.components.message-card',compact('message'))->render();
       }

    }


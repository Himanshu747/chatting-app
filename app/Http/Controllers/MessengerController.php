<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    use FileUploadTrait;
    public function index()
    {
        return view('messenger.index');
    }
    /**
     * Search User Profiles
     */
    public function userSearch(Request $request)
    {
        $input = $request['query'];
        $getRecords = null;
        $records = User::where('id', '!=', Auth::user()->id)
            ->where('name', 'LIKE', "%{$input}%")
            ->orWhere('user_name', 'LIKE', "%{$input}%")
            ->paginate(10);
        if ($records->total() < 1) {
            $getRecords .= "<p class='text-center'>Nothing to show.</p>";
        }
        foreach ($records as $record) {
            $getRecords .= view('messenger.components.search-item', compact('record'))->render();
        }
        return response()->json([
            'records' => $getRecords,
            'last_page' => $records->lastPage()
        ]);
        //}
    }
    //Fetch user by Id
    public function fetchIdInfo(Request $request)
    {

        $getUserInfo = User::where('id', $request['id'])->first();

        return response()->json(
            [
                'getuserinfo' => $getUserInfo
            ]
        );
    }

    function sendMessage(Request $request)
    {

        $request->validate([
            // 'message'=>['required'],
            'id' => ['required', 'integer'],
            'temporaryMsgId' => ['required'],
            'attachment' => ['nullable', 'max:1024', 'image']
        ]);

        //store message in db
        $attachmentPath = $this->uploadFile($request, 'attachment');
        $message = new Message();
        $message->from_id = Auth::user()->id;
        $message->to_id = $request->id;
        $message->body = $request->message;
        if ($attachmentPath) $message->attachment = json_encode($attachmentPath);
        $message->save();

        return response()->json([
            'message' => $message->attachment ? $this->messageCard($message, true) : $this->messageCard($message),
            'tempID' => $request->temporaryMsgId
        ]);
    }
    function messageCard($message, $attachment = false)
    {
        return view('messenger.components.message-card', compact('message', 'attachment'))->render();
    }
    //fetch messages from database

    function fetchMessages(Request $request){
        //dd($request->all());
        $messages=Message::where('from_id',Auth::user()->id)->where('to_id',$request->id)
        ->orWhere('from_id',$request->id)->where('to_id',Auth::user()->id)
        ->latest()->paginate(20);
        $response=[
             'last_page'=>$messages->lastPage(),
             'last_message'=>$messages->last(),
             'messages'=>''   
        ];
        if(count($messages)<1){
            $response['messages']="<div class='d-flex justify-content-center align-items-center h-100'><p>Say hi and start messaging.</p></div>";
            return response()->json($response);
        }
        //here we have to do little validation

        $allMessages="";
        foreach($messages->reverse() as $message){
            $allMessages.=$this->messageCard($message,$message->attachment ? true:false);
        }

       $response['messages']=$allMessages;

       return response()->json($response);
    }
}

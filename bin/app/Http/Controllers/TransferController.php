<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Transfer;
use App\Models\User;
use Exception;
use WebSocket\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests=Transfer::where('state','PENDING')->paginate(request('per_page'));
        return response()->json($requests,200);
    }

    public function push($data){
        try {
    
            $client = new Client("ws://127.0.0.1:8081");
            $client->send(json_encode($data));
            $client->close();

        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data=[
            'user_id'=>Auth::user()->id,
            'contact_id'=>$request->contact_id,
            'type'=>'CHAT.TRANSFER',
            'message'=>$request->message,
            'state'=>'PENDING'
        ];

        $create_transfer=Transfer::create($data);

        //  Notificación al frontend
        $data['user_name']=Auth::user()->name;
        $ws=$this->push($data);

        return response()->json($create_transfer,200);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Transfer $id)
    {
        $id->user_name=User::where('id',$id->user_id)->first()->name;
        return response()->json($id,200);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $id)
    {
        if($request->type==='TRANSFER'){

            $last_message="";

            $chat_id=Chat::where('contact_id',$id->contact_id)->first();

            if(request()->filled('is_private') & $chat_id!=null){
                if(request('is_private')==true){
                    $data=[
                        'id_message_wp'=>"",
                        'body'=>$request->body,
                        'ack'=>1,
                        'from_me'=>false,
                        'to'=>"",
                        'media_type'=>"chat",
                        'media_path'=>"",
                        'timestamp_wp'=>time()-18000,
                        'is_private'=>true,
                        'state'=>"G_TEST",
                        'created_by'=>Auth::user()->id,
                        'chat_id'=>$chat_id->id
                    ];
        
                    $create_message=Message::create($data);
                    $last_message=$request->body;

                }else{
                    $last_message=$chat_id->last_message;
                }
            }

            if($chat_id!=null){
                Chat::where('id',$chat_id->id)
                    ->update([
                        "last_message"=>$last_message,
                        'unread_message'=>intval($id->unread_message)+1,
                        "user_id"=>$id->user_id
                    ]);
            }
            
            Contact::where('id',$id->contact_id)
                ->update([
                    "user_id"=>$id->user_id
                ]);

        }else if($request->type==='DENY'){
            $id->update([
                'state'=>'DENY',
                'transfered_by'=>Auth::user()->id
            ]);
        }

        $id->update([
            'state'=>'PROCESSED'
        ]);

        return response()->json([
            'message'=>'Proceso ejecutado correctamente.'
        ],200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Tag;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {

        if(Auth::user()->tokenCan('chats.filter.agent')){
            $chats=Chat::when(request()->filled('state'),function($query){
                $query->where('state',request('state'));
            })
            ->when(request()->filled('user_id'),function($query){
                $query->where('user_id',request('user_id'));
            })
            ->when(request()->filled('state'),function($query){
                $query->where('state',request('state'));
            })
            ->when(request()->filled('tag_id'),function($query){
                $query->where('tag_id',request('tag_id'));
            })
            ->when(request()->filled('name'),function($query){
                //$query->where('tag_id',request('tag_id'));
                $contacts=Contact::where('name','REGEXP',request('name'))->get();
                $contact_ids=[];

                foreach($contacts as $contact){
                    array_push($contact_ids,$contact->id);
                }

                if(count($contact_ids)>0){
                    $query->whereIn('contact_id',$contact_ids);
                }
            })
            ->when(request()->filled('phone'),function($query){
                $contacts=Contact::where('phone_number','REGEXP',request('phone'))->get();
                
                $contact_ids=[];

                foreach($contacts as $contact){
                    array_push($contact_ids,$contact->id);
                }
                
                if(count($contact_ids)>0){
                    $query->whereIn('contact_id',$contact_ids);
                }
            })
            ->when(request()->filled('contact_id'),function($query){
                
            })
                //->where('user_id',Auth::user()->id)
                ->orderBy('updated_at','DESC')
                ->paginate(7);

        }else{
            $chats=Chat::when(request()->filled('state'),function($query){
                $query->where('state',request('state'));
            })
            ->when(request()->filled('user_id'),function($query){
                $query->where('user_id',request('user_id'));
            })
            ->when(request()->filled('state'),function($query){
                $query->where('state',request('state'));
            })
            ->when(request()->filled('tag_id'),function($query){
                $query->where('tag_id',request('tag_id'));
            })
            ->when(request()->filled('name'),function($query){
                //$query->where('tag_id',request('tag_id'));
                $contacts=Contact::where('name','REGEXP',request('name'))->get();
                $contact_ids=[];

                foreach($contacts as $contact){
                    array_push($contact_ids,$contact->id);
                }

                if(count($contact_ids)>0){
                    $query->whereIn('contact_id',$contact_ids);
                }
            })
            ->when(request()->filled('phone'),function($query){
                $contacts=Contact::where('phone_number','REGEXP',request('phone'))->get();
                
                $contact_ids=[];

                foreach($contacts as $contact){
                    array_push($contact_ids,$contact->id);
                }

                if(count($contact_ids)>0){
                    $query->whereIn('contact_id',$contact_ids);
                }
            })
            ->when(request()->filled('contact_id'),function($query){
                
            })
                ->where('user_id',Auth::user()->id)
                ->orderBy('updated_at','DESC')
                ->paginate(7);
        }

        foreach($chats as $chat){
            try {
                
                $contact=Contact::where('id',$chat->contact_id)->first();
                $chat->ack=$chat->find($chat->id)->messages()->orderby('id','DESC')->first()->ack;
                $chat->by_user=User::where('id',$chat->user_id)->first()->name;
                $chat->from_me=$chat->find($chat->id)->messages()->orderby('id','DESC')->first()->from_me;
                $chat->tag_name=($chat->tag_id!=null) ? Tag::where('id',$chat->tag_id)->first()->name : "";
                $chat->tag_color=($chat->tag_id!=null) ? Tag::where('id',$chat->tag_id)->first()->color : "";
                $chat->contact_name=$contact->name;
                $chat->contact_phone=$contact->phone_number;
                $chat->contact_picture=$contact->profile_picture;

            } catch (\Exception $th) {

                $chat->ack="";
                $chat->by_user=User::where('id',$chat->user_id)->first()->name;
                $chat->from_me="";
                $chat->contact_name=$contact->name;
                $chat->tag_name=($chat->tag_id!=null) ? Tag::where('id',$chat->tag_id)->first()->name : "";
                $chat->tag_color=($chat->tag_id!=null) ? Tag::where('id',$chat->tag_id)->first()->color : "";
                $chat->contact_phone=$contact->phone_number;
                $chat->contact_picture=$contact->profile_picture;

            }
            
        }   
        
        return $chats;
    }
    
    public function store(Request $request)
    {
        $create_chat=Chat::create($request->all());

        return response()->json([
            "status"=>200,
            "message"=>"Chat creado correctamente.",
            "data"=>$create_chat
        ],200);
    }

    public function show(Chat $id)
    {
        $chat=$id;
        $messages=$chat->find($id->id)->messages()->orderBy('id','DESC')->paginate(10);

        foreach($messages as $message){
            $message->created_by=User::where('id',$message->created_by)->first()->name;
        }

        $chat->messages=$messages;
            
        return $chat;
    }
    
    public function search(Chat $id)
    {
        $data=$id->find($id->id)->messages()
            ->when(request()->filled('start_date'),function($query){
                $query->where('created_at','>=',request('start_date')." 00:00:00");
            })
            ->when(request()->filled('end_date'),function($query){
                $query->where('created_at','<=',request('end_date')." 23:59:59");
            })
            ->when(request()->filled('body'),function($query){
                $query->where('body','REGEXP',request('body'));
            })
            ->get();


        return $data;
    }

    public function download(Request $request,Chat $id){
        $items=[];

        $messages=$id->find($id->id)->messages()
            ->when(request()->filled('start_date'),function($query){
                $query->where('created_at','>=',request('start_date'));
            })
            ->when(request()->filled('end_date'),function($query){
                $query->where('created_at','<=',request('end_date'));
            })
            ->when(request()->filled('body'),function($query){
                $query->where('body','REGEXP',request('body'));
            })
            ->get();
        
        foreach($messages as $message){
            array_push($items,[
                "text"=>$message->body,
                "timestamp"=>$message->timestamp_wp,
                "from_me"=>$message->from_me
            ]);
        }

        $data=[
            "client_name"=>Contact::where('id',$id->contact_id)->first()->name,
            "client_phone"=>Contact::where('id',$id->contact_id)->first()->phone_number,
            "items"=>json_encode($items)
        ];

        $invoice=Pdf::loadView('ride',$data);
        $name=$data['client_name'];
        $phone=$data['client_phone'];

        //$filename=Contact::where('id',$id->contact_id)->first()->name.'_'.Contact::where('id',$id->contact_id)->first()->phone_number.'.pdf';
        $filename="Chat_$phone-$name.pdf";
        $filename=str_replace(array("/", "\\", ":", "*", "?", "«", "<", ">", "|"), "-", $filename);

        return $invoice->download($filename);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $id)
    {
        $id->update($request->all());

        return response()->json([
            "status"=>200,
            "message"=>"Chat actualizado correctamente.",
            "data"=>$id
        ],200);
    }

    public function transfer(Request $request, string $id)
    {
        $last_message="";

        $id=Chat::where('id',$id)->first();

        if(request()->filled('is_private') & $id!=null){
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
                    'chat_id'=>$id->id
                ];
    
                $create_message=Message::create($data);
                $last_message=$request->body;

            }else{
                $last_message=$id->last_message;
            }
        }

        if($id!=null){
            Chat::where('id',$id->id)
                ->update([
                    "last_message"=>$last_message,
                    'unread_message'=>intval($id->unread_message)+1,
                    "user_id"=>$request->to
                ]);
        }
        
        Contact::where('id',$id->contact_id)
            ->update([
                "user_id"=>$request->to
            ]);
            
        return response()->json([
            "status"=>200,
            "message"=>"Chat transferido."
        ],200);
    }
}

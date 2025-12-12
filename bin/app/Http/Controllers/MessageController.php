<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Connection;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use App\Services\SocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    protected SocketService $socket;
    
    public function __construct(SocketService $socket){
        $this->socket=$socket;
    }

    public function index()
    {
        return Message::paginate(10);
    }
    
    public function connectmessage(Request $request){
        $message_service=new MessageService();
        $message=$message_service->handleMessage($this->socket,$request);
        return $message;
    }
    
    public function saveMessage(Request $request)
    {
        Log::info('Save Message Request: ', $request->all());

        if($request->from_me=="true"){
            Log::info('Message is from me, finding user_id by contact number.');
            $contact=Contact::where('phone_number',$request->to)->first();

            if($contact==null){
                Log::error('No se encontró un usuario con @c.us ' . $request->to);
                Log::info('Actualizando LID: ');
                $chat = Chat::where('id',$request->chat_id)->first();
                // Actualizamos el LID del contacto asociado al chat
                $update_contact = Contact::where('id',$chat->contact_id)->update([
                    'lid'=>$request->to
                ]);
                // Reintentamos obtener el user_id con el nuevo LID
                $user_id=Contact::where('lid',$request->to)->first()->user_id;
            }else{
                $user_id=$contact->user_id;
            }

        }else{
            $user_id=Connection::where('number',$request->to)->first()->user_id;
        }

        if($request->filled('chat_id')){

            $chat_id = $request->chat_id;

            Chat::where('id',$request->chat_id)->update([
                'last_message'=>($request->filled('media')) ? "Multimedia" : $request->body,
                'unread_message'=>($request->from_me==false) ? Chat::where('id',$request->chat_id)->first()->unread_message+1 : 0
            ]);
        }else{
            // Verificamos si el contacto existe
            Log::info('Finding or creating chat for number: ' . $request->number);
            $contact=Contact::where('phone_number',$request->number)->first();
            $contact_id=0;

            if($contact!=null){
                $contact_id=$contact->id;
                $prev_chat=Chat::where('contact_id',$contact_id)->first();

                if($prev_chat!=null){
                    
                    $chat_id=$prev_chat->id;
                    $state=$prev_chat->state;

                    if($prev_chat->state=='CLOSED'){
                        $state='PENDING';
                    }

                    Chat::where('id',$chat_id)->update([
                        'last_message'=>($request->filled('media') || ($request->filled('media_type') && $request->media_type!='chat')) ? "Multimedia" : $request->body,
                        'unread_message'=>Chat::where('id',$chat_id)->first()->unread_message+1,
                        'state'=>$state
                    ]);

                }else{

                    $new_chat = Chat::create([
                        'state'=>($request->from_me==true) ? 'OPEN' : 'PENDING',
                        'last_message'=>($request->filled('media') || $request->filled('media_type')!='chat') ? "Multimedia" : $request->body,
                        'unread_message'=>($request->from_me==true) ? 0 : 1,
                        'contact_id'=>$contact_id,
                        'user_id'=>$user_id
                    ]);

                    $chat_id=$new_chat->id;
                }

            }else{
                
                $create_contact=Contact::create([
                    'name'=>$request->notify_name,
                    'phone_number'=>$request->number,
                    'profile_picture'=>"",
                    'user_id'=>$user_id
                ]);
                
                $contact_id=$create_contact->id;

                $new_chat = Chat::create([
                    'state'=>'PENDING',
                    'last_message'=>($request->filled('media') || $request->filled('media_type')!='chat') ? "Multimedia" : $request->body,
                    'unread_message'=>1,
                    'contact_id'=>$contact_id,
                    'user_id'=>$user_id
                ]);
                $chat_id=$new_chat->id;
            }
        }
        
        //  Procesamiento de contenido multimedia
        if(request()->filled('data')){
            $format=$request->fileformat;

            Log::info('Procesando contenido multimedia. Filetype: ' . $request->filetype . ', Format: ' . $format);
                
            if($request->filetype==='audio' || $request->filetype==='video'){
                $format='ogg';
            }

            $year = date('Y');
            $month = date('m');
            $day = date('d');
            $directory = public_path("files/{$request->filetype}/{$year}/{$month}/{$day}");
            
            if(!file_exists($directory)){
                mkdir($directory, 0755, true);
            }
            
            $filename = "files/{$request->filetype}/{$year}/{$month}/{$day}/" . time() . '.' . $format;
            $fullPath = public_path($filename);
            
            $stream = base64_decode($request->data);
            file_put_contents($fullPath, $stream);
            
        }else{
            $filename=$request->media_url;
        }

        $data=[
            'id_message_wp'=>$request->id_message_wp,
            'body'=>($request->media_type=='chat') ? $request->body : "Multimedia",
            'ack'=>$request->ack,
            'from_me'=>$request->from_me,
            'to'=>$request->to,
            'media_type'=>$request->media_type,
            'media_path'=>($request->media_type!='chat') ? $filename : "",
            'timestamp_wp'=>$request->timestamp,
            'is_private'=>$request->is_private,
            'state'=>"G_TEST",
            'created_by'=>$user_id,
            'chat_id'=>$chat_id
        ];

        $create_message=Message::create($data);
        
        return response()->json([
            "status"=>200,
            "message"=>"Mensaje creado correctamente.",
            "user_id"=>$user_id,
            "chat_id"=>$chat_id,
            "media"=>$data,
            'media_path'=>($request->media_type!='chat') ? $filename : ""
        ],200);
    }
    
    public function show(Message $id)
    {
        return response()->json([
            "data"=>$id,
            "status"=>200
        ],200);
    }

    public function updateACK(Request $request)
    {
        $update_ack=Message::where('id_message_wp',$request->id_wp)
            ->where('from_me',$request->from_me)
            ->update([
                "ack"=>$request->ack
            ]);
        
        return response()->json([
            "status"=>200,
            "message"=>"ACK actualizado.",
            "chat_id"=>Message::where('id_message_wp',$request->id_wp)->where('from_me',$request->from_me)->first()->chat_id,
            "user_id"=>Message::where('id_message_wp',$request->id_wp)->where('from_me',$request->from_me)->first()->created_by,
            "temp_signature"=>Message::where('id_message_wp',$request->id_wp)->where('from_me',$request->from_me)->first()->temp_signature
        ],200);
    }
}

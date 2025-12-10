<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $is_assign=false;

        if(Auth::user()->tokenCan('chats.filter.agent')){
            $contactos=Contact::when(request()->filled('name'),function($query){
                    $query->where('name','REGEXP',request('name'));
                })
                ->when(request()->filled('phone'),function($query){
                    $query->where('phone_number','REGEXP',request('phone'));
                })
                ->paginate(7);
            
            if(count($contactos)>0){
                $is_assign=true;
            }

            foreach($contactos as $contacto){
                $chat=$contacto->find($contacto->id)->chats()->first();
                $contacto->chat=$chat;
                $contacto->is_assign=$is_assign;
            }

        }else{
            $contactos=Contact::when(request()->filled('name'),function($query){
                    $query->where('name','REGEXP',request('name'));
                })
                ->when(request()->filled('phone'),function($query){
                    $query->where('phone_number','REGEXP',request('phone'));
                })
                ->paginate(7);

            foreach($contactos as $contacto){
                if($contacto->user_id==Auth::user()->id){
                    $is_assign=true;
                }

                $chat=$contacto->find($contacto->id)->chats()->first();
                $contacto->chat=$chat;
                $contacto->is_assign=$is_assign;
            }
        }

        return $contactos;
    }
    
    public function indexChats(Request $request)
    {

        if(Auth::user()->tokenCan('chats.filter.agent')){
            $contacts=Contact::when(request()->filled('name'),function($query){
                $query->where('name','REGEXP',request('name'));
            })
            ->when(request()->filled('phone'),function($query){
                $query->where('phone_number','REGEXP',request('phone'));
            })
            ->when(request()->filled('id'),function($query){
                $query->where('id',request('id'));
            })
            ->paginate(7);
            
        }else{
            $contacts=Contact::when(request()->filled('name'),function($query){
                $query->where('name','REGEXP',request('name'));
            })
            ->when(request()->filled('phone'),function($query){
                $query->where('phone_number','REGEXP',request('phone'));
            })
            ->when(request()->filled('id'),function($query){
                $query->where('id',request('id'));
            })
            ->where('user_id',Auth::user()->id)
            ->paginate(7);
        }

        foreach($contacts as $contact){

            try {
                $contact->chat=$contact->find($contact->id)->chats()->first();
                $contact->chat->ack=$contact->chat->find($contact->chat->id)->messages()->orderby('id','DESC')->first()->ack;   
            } catch (\Throwable $th) {
                $contact->chat=[];
                // $contact->chat->ack="";
            }

        }

        return $contacts;
    }

    public function store(Request $request)
    {   

        if(preg_match('/^\d{11,}$/',$request->phone_number)){

            if(Contact::where('phone_number',$request->phone_number)->first()==null){

                $data=[
                    'name'=>$request->name,
                    'phone_number'=>$request->phone_number,
                    'profile_picture'=>$request->profile_picture,
                    'user_id'=>Auth::user()->id,
                    'sync_id'=>$request->sync_id,
                    'count_edits'=>0
                ];

                $create_contact=Contact::create($data);

                return response()->json([
                    "status"=>200,
                    "message"=>"Contacto creado correctamente.",
                    "data"=>$create_contact
                ],200);

            }else{

                return response()->json([
                    "status"=>400,
                    "message"=>"Contacto ya existe."
                ],200);
                
            }

        }else{
            return response()->json([
                "status"=>400,
                "message"=>"Contacto no cumple formato para un número de whatsapp."
            ],200);
        }

    }

    public function storeImport(Request $request)
    {   

        if(preg_match('/^\d{11,}$/',$request->phone_number)){

            if(Contact::where('phone_number',$request->phone_number)->first()==null){

                $data=[
                    'name'=>$request->name,
                    'phone_number'=>$request->phone_number,
                    'profile_picture'=>$request->profile_picture,
                    'user_id'=>$request->user_id
                ];

                $create_contact=Contact::create($data);

                return [
                    "status"=>200
                ];

            }else{
                return [
                    "status"=>"FOUND"
                ];
            }
        }
    }

    public function show(Contact $id)
    {
        return $id;
    }
    
    public function update(Request $request, Contact $id)
    {
        if($id->count_edits>0){
            
            return response()->json([
                "status"=>400,
                "message"=>"Contacto ya ha sido actualizado."
            ],200);

        }else{

            $id->update($request->all());
            
            return response()->json([
                "status"=>200,
                "message"=>"Contacto actualizado correctamente.",
                "data"=>$id
            ],200);

        }
    }
}
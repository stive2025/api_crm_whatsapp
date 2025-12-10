<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $connections=Connection::when(request()->filled('number'),function ($query){
                $query->where('number',request('number'));
            })
            ->get();

        return response()->json([
            "status"=>200,
            "data"=>$connections
        ],200);
        
    }

    public function indexSessions()
    {
        $connections=Connection::get();
        $data=[];

        foreach($connections as $connection){
            array_push($data,$connection->user_id);
        }
        
        return response()->json([
            "status"=>200,
            "data"=>$data
        ],200);
        
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $create_connection=Connection::create([
            'qr_code'=>$request->code_qr,
            'name'=>$request->name,
            'greeting_message'=>$request->greeting_message,
            'farewell_message'=>$request->farewell_message,
            'status'=>"PENDING"
        ]);

        return response()->json([
            "status"=>200,
            "data"=>$create_connection,
            "message"=>"Conexión creada correctamente."
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {   

        $connection=Connection::where('user_id',$id)->first();

        return response()->json([
            "status"=>200,
            "data"=>$connection
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Connection $id)
    {
        $update_connection=Connection::where('id',$id->id)->update($request->all());

        return response()->json([
            "status"=>200,
            "message"=>"Estado de conexión actualizado."
        ],200);
    }
}

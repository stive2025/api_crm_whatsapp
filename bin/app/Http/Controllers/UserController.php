<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::when(request()->filled('name'),function($query){
                $query->where('name','REGEXP',request('name'));
            })
            ->paginate(7);
    }

    public function indexChats(Request $request)
    {
        $users=User::when(request()->filled('name'),function($query){
                $query->where('name','REGEXP',request('name'));
            })
            ->paginate(7);

        foreach($users as $user){
            $user->chats=$user->find($user->id)->chats;
        }

        return $users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data_user=[
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>$request->role,
            'abilities'=>$request->abilities
        ];

        $create_user=User::create($data_user);

        return response()->json([
            "status"=>200,
            "message"=>"Usuario creado correctamente.",
            "data"=>$create_user
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user=User::find($id);

        return response()->json([
            "status"=>200,
            "message"=>($user==null) ? "El usuario con ID $id, no existe." : "Usuario encontrado.",
            "data"=>$user
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $id)
    {

        if(request()->filled('password')){
            $id->update([
                "password"=>Hash::make($request->password)
            ]);
        }else{
            $id->update($request->all());
        }

        return response()->json([
            "status"=>200,
            "message"=>"Usuario actualizado correctamente.",
            "data"=>$id
        ],200);

    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $id)
    {
        $id->delete();

        return response()->json([
            "status"=>200,
            "message"=>"Usuario eliminado."
        ],200);
    }
}

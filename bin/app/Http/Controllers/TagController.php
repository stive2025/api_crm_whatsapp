<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Tag::when(request()->filled('name'),function($query){
            $query->where('name','REGEXP',request('name'));
        })
        ->paginate(7);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $repeat=Tag::where('name',$request->name)->first();

        if($repeat==null){
            $create_tag=Tag::create($request->all());

            return response()->json([
                "status"=>200,
                "message"=>"Tag creado correctamente.",
                "data"=>$create_tag
            ],200);

        }else{

            return response()->json([
                "status"=>400,
                "message"=>"Ya existe una etiqueta con este nombre."
            ],400);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $id)
    {
        return response()->json([
            "status"=>200,
            "data"=>$id
        ],200);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $id)
    {
        $id->update($request->all());

        return response()->json([
            "status"=>200,
            "data"=>"Etiqueta actualizada."
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $id)
    {
        $id->delete();

        return response()->json([
            "status"=>200,
            "data"=>"Etiqueta eliminada."
        ],200);
    }
}

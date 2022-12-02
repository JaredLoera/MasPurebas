<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\grupo;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class salones extends Controller
{
    public function getGrupos(Request $request){
        $grupos = grupo::all();
        return response()->json([
            "GRUPOS"=>$grupos
        ],200);
    }
    public function addGrupos(Request $request){
        $validator = Validator::make($request->all(),[
            'fecha'=>'required'
        ]);
        if($validator->fails()){
            return response()->json([
                $validator->errors()
            ],400);
        }
        $grupo = new grupo();
        $grupo->fecha_creacion=$request->fecha;
        $grupo->save();
        return response()->json([
            "Grupo creado"=>true,
            "datos"=>$grupo
        ],201);
    }
    public function deleteGrupos(Request $request){
        $validator = Validator::make($request->all(),[
            'id'=>'required'
        ]);
        if($validator->fails()){
            return response()->json([
                $validator->errors()
            ],400);
        }
        $grupo = new grupo();
        $grupo->estado=false;
        $grupo->save();
        return response()->json([
            "Se a desactivado el grupo datos"=> $grupo
        ],201);
    }
    public function updateGrupos(){}
    public function getAlumnos(){}
    public function addAlumnos(){}
    public function deleteAlumnos(){}
    public function updateAlumnos(){}
}

<?php

namespace App\Http\Controllers;

use App\Mail\VerifyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
//Modelos
use App\Models\User;
use App\Models\codigo;

class Usuarios extends Controller
{
    public function Datos(){
        $users = User::all();
        return response()->json([
            "USUARIOS"=>$users
        ],200);
    }
    public function AddUser(Request $request){
        $validacion = Validator::make($request->all(),[
            'name' => 'required | string | max:50',
            'email'=>'required',
            'password'=>'required',
            'numero_telefono'=>'required',
            'rol'=>'required'
        ]);
        if ($validacion->fails()) {
            return response()->json([
                "FALTAN VALIDAR CAMPOS"=>[$validacion->errors()]
            ],400);
        }
        $user = new User();
        $user->name     =$request->name;
        $user->email    =$request->email;
        $user->role_id  = $request->rol;
        $user->password = bcrypt($request->password);
        $user->numero_telefono=$request->numero_telefono;
        $user -> save();
        $url = URL::temporarySignedRoute('verificar',now()->addMinutes(30),['user'=>$user->id]);
        Mail::to($request->email)->send(new VerifyMail($user,$url));
        return response()->json([
            "data"=>[$user]
        ],200);
    }

    public function verificar(Request $request){
        $validacion = Validator::make($request->all(),[
            'numero_telefono'=>'required'
        ]);
        if ($validacion->fails()) {
            return response()->json([
                "FALTAN VALIDAR CAMPOS"=>[$validacion->errors()]
            ],400);
        }

        $user = User::find($request->user);
        $numRand=rand(1000,9999);

        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'from' => 'Jared Loera',
            'text' => 'Tu codigo es '.$numRand,
            'to'=>52 .$user->numero_telefono,
            'api_key'=>"c682b7c4",
            "api_secret"=>"2Lh5eqkYxjV2tU59"
        ]);

        if ($response->successful()) {
            $codigo = new codigo();
            $codigo ->user_id=$user->id;
            $codigo->codigo= $numRand;
            $codigo->save();      
            return response()->json([
                "Se a enviado el codigo"
            ],200);    
        }
    }

    public function verificarPerfil(Request $request){
        $validator = Validator::make($request->all(), [
            'codigo' => 'required | integer | exists:codigos,codigo',
        ], [
            'codigo.required' => 'El codigo es requerido',
            'codigo.integer' => 'El codigo debe ser un número entero',
            'codigo.exist' => 'El codigo es incorrecto',
        ]);
        if($validator->fails()) {
            return response()->json(["errores" => $validator->errors()], 400);
        }
        $codigo = codigo::where('codigo', $request->codigo)->first();
        $user = User::find($codigo->user_id);
        $user->active=true;
        $user->save();
        return response()->json(["Usuario Activado"],);
    }

    public function login (Request $request){
        $validator = Validator::make($request->all(), [
            'email'         =>  'required|email',
            'password'        =>  'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "Campos faltantes" => $validator->errors()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['LAS CREDENCIALES ESTAN INCORRECTAS'], 400);
        }
        $token = $user->createToken("auth_token")->plainTextToken;
        return response()->json(["Usuario Ingresado" => [
            "Usuario" => $request->email,
            "Token" => $token
        ]], 200);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(["Sesion ha cerrado"],);
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\salones;
use App\Http\Controllers\Usuarios;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum','estado')->group(function(){
    Route::get('/salones',[salones::class,'getGrupos']);
    Route::middleware('role:guert')->post('/addgrupo',[salones::class,'addGrupos']); 
});

Route::get('/login',[Usuarios::class,'login']);

Route::get('/users',[Usuarios::class,'Datos']);


Route::post('/useradd',[Usuarios::class,'AddUser']);
Route::post('/verifyuser',[Usuarios::class,'verificar'])->name('verificar')->middleware('signed');
Route::post('/activeuser',[Usuarios::class,'verificarPerfil']);

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beer;
use App\Models\Pub;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QuestController extends Controller
{
    public function getQuests(){

        $respuesta = ["status" => 1, "msg" => ""];

        try {
            $quests = Quest::with('pub') -> get();

            if ($quests){
                $respuesta['msg'] = "Quests encontrados";
                $respuesta['quests'] = $quests;
            } else {
                $respuesta["status"] = 0;
                $respuesta['msg'] = "No se han podido obtener los bares";
            }

        }catch (\Exception $e) {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
        }
        return response()->json($respuesta);
    }

    public function checkQuest(Request $req){
        $respuesta = ["status" => 1, "msg" => ""];

        $datos = $req->getContent();
        $datos = json_decode($datos);
        $usuario = User::find($req->usuario->id);

        if($usuario){

           $questMatch = Quest::where('id', $datos->id)
            ->where('code', $datos->codigo)
            ->first();

            if(isset($questMatch)) {
               
                $questMatchExist = DB::Table('user_quests')
                ->where('user_id', $usuario->id)
                ->where('quest_id', $datos->id)
                ->first();

                if($questMatchExist){
                    $respuesta["status"] = 0;
                    $respuesta["msg"] = "El usuario ya ha completado este quest."; 
                } else {
                    $questMatch->users()->syncWithoutDetaching($usuario);
                    $usuario->puntos += $datos->puntos;
                    $usuario->save();
                    $respuesta["msg"] = "Quest completado"; 
                }

            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "El codigo no coincide con el quest"; 
            }

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "No se ha encontrado el usaurio"; 
        }

        return response()->json($respuesta);



    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beer;
use App\Models\Pub;
use App\Models\Quest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class QuestController extends Controller
{
    public function getQuests(){

        $respuesta = ["status" => 1, "msg" => ""];

        try {
            $quests = $quests->pub;
            
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
}

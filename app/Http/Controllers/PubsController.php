<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beer;
use App\Models\Pub;
use App\Models\Quest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PubsController extends Controller
{
    public function getPubs(){

        $respuesta = ["status" => 1, "msg" => ""];
        
        try {

            $pubs = Pub::get();  
            if ($pubs){
                $respuesta['msg'] = "Pubs encontrados";
                $respuesta['pubs'] = $pubs;
            } else {
                $respuesta["status"] = 0;
                $respuesta['msg'] = "No se han podido obtener los bares";
            }

            $respuesta['msg'] = "Cervezas encontradas";
            $respuesta['pubs'] = $pubs;

        }catch (\Exception $e) {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
        }
        return response()->json($respuesta);
    }
}

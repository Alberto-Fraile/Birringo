<?php

namespace App\Http\Controllers;
use App\Models\Beer;
use App\Models\Pub;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class BeerController extends Controller
{
    //Obtener Listado Cervezas se le pasa un json con solo busqueda y el nombre.
    public function obtenerCervezas(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $usuario = User::find($req->usuario->id);
        
        if($usuario){
            try {

            
                //Ver cervezas por titulo o tipo
                if($req -> has('busqueda') && $req -> input('busqueda') != "" ){

                    $beers = Beer::with('pubs')
                    ->where('beers.titulo','like','%'. $req -> input('busqueda').'%')
                    ->orWhere('beers.tipo','like','%'. $req -> input('busqueda').'%')
                    /*->leftJoin('user_beers','user_beers.beer_id', 'beers.id')
                    ->where('user_beers.user_id', $usuario->id)
                    ->orWhereNull('user_beers.beer_id')
                    ->select('beers.id AS id', 'beers.titulo', 'beers.graduacion','beers.tipo', 'beers.imagen'
                    , 'beers.imagen2', 'beers.descripcion', 'user_beers.isFav')*/
                    ->get();

                //Ver todas las cervezas.
                } 
                else {
                    $beers = Beer::with('pubs')
                            ->leftJoin('user_beers','user_beers.beer_id', 'beers.id')
                            ->where('user_beers.user_id', $usuario->id)
                            ->orWhereNull('user_beers.beer_id')
                            ->select('beers.id AS id', 'beers.titulo', 'beers.graduacion','beers.tipo', 'beers.imagen'
                            , 'beers.imagen2', 'beers.descripcion', 'user_beers.isFav AS isFav')
                            ->get();
                }
                $respuesta['msg'] = "Cervezas encontradas";
                $respuesta['beers'] = $beers;
            } catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
            }
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "No se ha encontrado el usaurio"; 
        }
        return response()->json($respuesta);
    }

    //Obtener Listado Cervezas por tipo que se mostraran en el home.
    public function obtenerCervezasTiposMain(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
    
        try {
            if($req -> has('tipo') &&  $req -> input('tipo') == "Rubia"){
                $beers = Beer::with('pubs')
                ->where('beers.tipo','like','%'. "Rubia".'%')
                ->get();
           
            } else if($req -> has('tipo') &&  $req -> input('tipo') == "Lager"){
                $beers = Beer::with('pubs')
                ->where('beers.tipo','like','%'. "Lager".'%')
                ->get();
           
            } else if($req -> has('tipo') &&  $req -> input('tipo') == "Ale"){
                $beers = Beer::with('pubs')
                ->where('beers.tipo','like','%'. "Ale".'%')
                ->get();
           
            } else if($req -> has('tipo') &&  $req -> input('tipo') == "IPA"){
                $beers = Beer::with('pubs')
                ->where('beers.tipo','like','%'. "IPA".'%')
                ->get();
           
            } else if($req -> has('tipo') &&  $req -> input('tipo') == "Rellenar"){
                $beers = Beer::with('pubs')
                ->where('beers.tipo','like','%'. "Rellenar".'%')
                ->get();
           
            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "No se ha encontrado ningun tipo";
            }
            $respuesta['msg'] = "Cervezas encontradas";
            $respuesta['beers'] = $beers;

        }catch (\Exception $e) {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
        }
        return response()->json($respuesta);
    }

    //Subir una cerveza
    public function altaBeer(Request $request){

        $respuesta = ["status" => 1, "msg" => "", "msg2" => ""];

        $validator = Validator::make(json_decode($request->
        getContent(),true), [
            "titulo" => 'required|max:50',
            "graduacion" => 'required|max:10',
            "tipo" => 'required|max:100',
            "imagen" => 'nullable|max:250',
            "imagen2" => 'nullable|max:250',
            "descripcion" => 'required|max:150'
        ]);

        if($validator -> fails()){
            $respuesta["status"] = 0;
            $respuesta["msg"] = "".$validator->errors();    

        } else {

            $datos = $request -> getContent();
            $datos = json_decode($datos); 
            $controlador = true;
              
            $beer = new Beer();
            $beer -> titulo = $datos->titulo;
            $beer -> graduacion = $datos->graduacion;
            $beer -> tipo = $datos->tipo;
            $beer -> descripcion = $datos->descripcion;

            if(isset($datos->imagen))
            $beer -> imagen = $datos->imagen;

            if(isset($datos->imagen2))
            $beer -> imagen2 = $datos->imagen2;

            if(isset($datos->pubs) && !empty($datos->pubs)){
                foreach ($datos->pubs as $pubsData) {
                    $pubsData = get_object_vars($pubsData);
                    if(array_key_exists("id",$pubsData)){
                        $id = $pubsData["id"];
                        $pubs = Pub::find($id);
                        if(!$pubs)
                        $controlador = false;
                    }
                }
                if($controlador){
                    try {
                        $beer->save();
                        $respuesta["msg"] = "Cerveza Guardada";
                        $beer = Beer::find($beer->id);

                        foreach ($datos->pubs as $pubsData) {
                            $pubsData = get_object_vars($pubsData);

                            if(array_key_exists("id",$pubsData)){
                                $id = $pubsData["id"];
                                $pubs = Pub::find($id);
                                if($beer && $pubs){
                                    $beer->pubs()->attach($pubs);
                                    $respuesta["msg2"] = "Pubs asignadas correctamente a la cerveza"; 
                                }
                            }
                            elseif(array_key_exists("titulo",$pubsData) && array_key_exists("calle",$pubsData) 
                            && array_key_exists("latitud",$pubsData) && array_key_exists("longitud",$pubsData)){
                                $pub = Pub::create([
                                    'titulo' => $pubsData["titulo"],
                                    'calle' => $pubsData["calle"],
                                    'latitud' => $pubsData["latitud"],
                                    'longitud' => $pubsData["longitud"],
                                ]);
                                $pubNuevo= Pub::find($pub->id);
                                if ($beer && $pubNuevo){
                                    $beer->pubs()->attach($pubNuevo);
                                    $respuesta["msg3"] = " Pubs creados y asignados correctamente a la cerveza"; 
                                }
                            }
                        }
                    }catch (\Exception $e) {
                        $respuesta["status"] = 0;
                        $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
                    } 
                } else {
                    $respuesta["status"] = 0;
                    $respuesta["msg"] = "Algun pub asocido a la verveza no es valido o no existe, intentalo de nuevo";
                }
            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "No se ha asignado ningun pub a la cerveza, intentalo de nuevo";
            }
        }
    
        return response()->json($respuesta);
    }
}

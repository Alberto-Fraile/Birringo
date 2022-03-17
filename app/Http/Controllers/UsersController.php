<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Beer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\recoverPass;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{	//Registro usuario
    public function register(Request $req){

		$respuesta = ["status" => 1, "msg" => "", "api_token" => ""];
        $validator = validator::make(json_decode($req->getContent(),true), [
			'name' => 'required|max:55',
			'email' => 'required|email|unique:App\Models\User,email|max:30',
			'password' => 'required|regex:/(?=.*[a-z)(?=.*[A-Z])(?=.*[0-9]).{6,}/',
			'telefono' => 'nullable|numeric'
		]);

        if ($validator->fails()){
        	$respuesta['status'] = 0;
			$respuesta["msg"] = "".$validator->errors();
        }else {
	        $datos = $req->getContent();
	        $datos = json_decode($datos);
	        $user = new User();

	        $user->name = $datos->name;
	        $user->email = $datos->email;
	        $user->password = Hash::make($datos->password);

			if(isset($datos->telefono) && $datos->telefono != 0)
	        $user->telefono = $datos->telefono;

			do {
				$token = Hash::make($user->id.now());
			} while(User::where('api_token', $token) -> first());
			
	        try{
				$user -> api_token = $token;
	            $user->save();
	            $respuesta['msg'] = "Registro completado";
				$respuesta["api_token"] = $user -> api_token; 
	        }catch(\Exception $e){
	            $respuesta['status'] = 0;
	            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
	        }
        }
	    return response()->json($respuesta);
    }
	//Login usuario
    public function login(Request $req){

		$respuesta = ["status" => 1, "msg" => "", "api_token" => ""];

		$datos = $req->getContent();
		$datos = json_decode($datos);
    	$email = $datos->email;
		$user = User::where('email', $email)->first();

		if($user){
			if (Hash::check($datos->password, $user->password)) {

				if (!isset($user->api_token)) {
                    do {
                        $token = Hash::make($user->id.now());
                    } while(User::where('api_token', $token) -> first());
                    $user -> api_token = $token;
                    $user -> save();
                } 
	            $respuesta['msg'] = "Login correcto";
				$respuesta["api_token"] = $user -> api_token; 

			} else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "La contraseña no es correcta";  
            }
		}else{
			$respuesta['status'] = 0;
	        $respuesta["msg"] = "Usuario no encontrado";  
		}
		return response()->json($respuesta);
    }

	//Recuperar Contraseña
	public function recoverPass(Request $req){

		$respuesta = ["status" => 1, "msg" => ""];
		$datos = $req -> getContent();
		$datos = json_decode($datos); 
	
		$email = $datos->email;
		$usuario = User::where('email', $email) -> first();

		if($usuario){
			$caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
			$caracteresLenght = strlen($caracteres);
			$longitud = 8;
			$newPassword = "";
			
			for($i = 0; $i<$longitud; $i++) {
				$newPassword .= $caracteres[rand(0, $caracteresLenght -1)];
			}
			$usuario->api_token = null;
			$usuario->password = Hash::make($newPassword);
			$usuario -> save();
			Mail::to($usuario->email)->send(new recoverPass($newPassword));
			$respuesta["msg"] = "Se ha enviado una contraseña nueva a tu email";  

		} else {
			$respuesta["status"] = 0;
			$respuesta["msg"] = "Email no encontrado";  
		}
		return response()->json($respuesta);  
	}
	//Ver perfil de usuario logueado, se necesita api token.
	public function getUserProfile(Request $request){

        $respuesta = ["status" => 1, "msg" => ""];
        $perfil = $usuario = User::find($request->usuario->id);

        if($perfil){
            //$perfil -> makeHidden( 'password');
            $respuesta['msg'] = "Datos obtenidos";
            $respuesta['datos_perfil'] = $perfil;
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error";  
        }
        return response()->json($respuesta);
    }

	//Un usuario podra subir una foto de perfil, se necesita api token.
    public function uploadProfileImage(Request $req){
        $respuesta = ["status" => 1, "msg" => ""];

        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $usuario = $usuario = User::where('id', $req->usuario->id) -> first();
        $image = $datos->image;  // image base64 encoded

        if($image && $usuario){
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10).'.'.'jpeg';

            try {
                Storage::disk('public')->put($imageName, base64_decode($image));
                $imageUrl = "http://birringoapi.jonacedev.com/Birringo/public/storage/".$imageName;
                $usuario->imagen = $imageUrl;
                $usuario -> save();        
                $respuesta["msg"] = "Imagen guardada";        
            } 
            catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error al guardar la imagen";  
            }

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Imagen o usuario no encontrado";  
        }

        return response()->json($respuesta);  
    }

	//Ver listado de ranking top 20, se necesita api token.
	public function getRanking(Request $request){
		$respuesta = ["status" => 1, "msg" => ""];

		try {
			$ranking = DB::table('users')->take(20)
			->orderBy('puntos','DESC')
			->get();
            $respuesta['ranking'] = $ranking;

        }catch (\Exception $e) {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
        }

		return response()->json($respuesta);
	}
	//Obtener poisicion del usuario en el ranking para mostrarsela al usuario.
	public function getUserPositionRanking(Request $request){
		$respuesta = ["status" => 1, "msg" => ""];


		try {
			$ranking = DB::table('users')
			->orderBy('puntos','DESC')
			->get()->toArray();
			$userPosition = array_search($request->usuario->id, array_column($ranking, 'id'));
			$perfil = User::find($request->usuario->id);

			if ($perfil || $userPosition){
				$perfil->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
				$respuesta['posicion'] = $userPosition + 1;
				$respuesta['datos_perfil'] = $perfil;
			} else {
				$respuesta["status"] = 0;
				$respuesta["msg"] = "No se ha encontrado al usuario";  
			}
		
        }catch (\Exception $e) {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
        }

		return response()->json($respuesta);
	}

	//Añadir cerveza a favoritos.
	public function addBeerToFavourites(Request $request){
		$respuesta = ["status" => 1, "msg" => ""];

		$datos = $request -> getContent();
        $datos = json_decode($datos); 

		$usuario = User::find($request->usuario->id);
		$beer = Beer::find($datos->beerID);

		if ($usuario && $beer){
			//Con el syncWithoutDetaching evitamos que se añada una cerveza a favoritos numerosas veces, si ya esta 
			//favoritos no se añadira otra vez.
			$usuario->beers()->syncWithoutDetaching($beer);
			$respuesta["msg"] = "Cerveza añadida a favoritos";

		} else {
			$respuesta["status"] = 0;
			$respuesta['msg'] = "Cerveza o usuario no encontrado";
		}

		return response()->json($respuesta);
	}

	//Ver cervezas favoritas del usuario logueado.
	public function getFavouritesBeersFromUser(Request $request){

		$respuesta = ["status" => 1, "msg" => ""];
		$usuario = User::find($request->usuario->id);

		if ($usuario){  
			$usuario -> beers; 
			try {
				if (!$usuario -> beers -> isEmpty()){
					$beers = Beer::with('pubs')
						 ->join('user_beers','beers.id','user_beers.beer_id')
                     	 ->get();
					$pubs = DB::table('pub_beers')
						->join('pubs','pub_beers.pub_id','pubs.id')
						->get();

					//$favBeers = array_merge($usuario -> beers, ['pubs' => $pubs]);

                    $respuesta['beers'] = $beers;
					$respuesta["msg"] = "Listado obtenido";
				}
				else{
                    $respuesta["msg"] = "El usuario no tiene cervezas favoritas";
				}
			}catch (\Exception $e) {
				$respuesta["status"] = 0;
				$respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
			}
		} else {
			$respuesta["status"] = 0;
			$respuesta['msg'] = "Usuario no encontrado";
		}

        return response()->json($respuesta);
	}

	function editUserData(Request $request){

		$respuesta = ["status" => 1, "msg" => ""];

        $usuario = User::find($request->usuario->id);
        $validator = Validator::make(json_decode($request->
        getContent(),true), [
            "name" => 'max:50',
            "email" => 'email|max:30',
			"biografia" => 'max:120',
        ]);

            if($validator -> fails()){
                $respuesta["status"] = 0;
                $respuesta["msg"] = "".$validator->errors();  
            } else {

				if ($usuario){
		
					$datos = $request -> getContent();
					$datos = json_decode($datos); 
				
					if(isset($datos->name))
					$usuario -> name = $datos->name;
					if(isset($datos->email))
					$usuario -> email = $datos->email;
					if(isset($datos->telefono))
					$usuario -> telefono = $datos->telefono;
					if(isset($datos->biografia))
					$usuario -> biografia = $datos->biografia;
		
					try {
						$usuario->save();
						$respuesta["msg"] = "Cambios realizados";
					}catch (\Exception $e) {
						$respuesta["status"] = 0;
						$respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
					}
						
				} else {
					$respuesta["msg"] = "Usuario no encontrado"; 
					$respuesta["status"] = 0;
				} 
			} 
			

        return response()->json($respuesta); 

	}
	


}

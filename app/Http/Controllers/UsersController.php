<?php

namespace App\Http\Controllers;

use App\Models\User;
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
	            do{
	        		$apitoken = Hash::make($user->id.now());
	            } while(User::where('api_token', $apitoken)->first());

	            $user->api_token = $apitoken;
	            $user->save();
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
}

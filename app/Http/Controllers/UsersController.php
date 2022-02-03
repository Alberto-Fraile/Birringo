<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function register(Request $req){

		$respuesta = ["status" => 1, "msg" => ""];

        $validator = validator::make(json_decode($req->getContent(),true
    	), 
        	['name' => 'required|max:55',
        	 'email' => 'required|email|unique:App\Models\User,email|max:30',
        	 'password' => 'required|regex:/(?=.*[a-z)(?=.*[A-Z])(?=.*[0-9]).{6,}/',
        	 'telefono' => 'required|numeric'
        	]);

        if ($validator->fails()){
        	$respuesta['status'] = 0;
        	$respuesta['msg'] = $validator->errors();

        }else {
	        $datos = $req->getContent();
	        $datos = json_decode($datos);
	        $user = new User();

	        $user->name = $datos->name;
	        $user->email = $datos->email;
	        $user->password = Hash::make($datos->password);
	        $user->telefono = $datos->telefono;

	        try{
	            $user->save();
	            $respuesta['msg'] = "Usuario guardado con id ".$user->id;
	        }catch(\Exception $e){
	            $respuesta['status'] = 0;
	            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
	        }

        }

	    return response()->json($respuesta);
    }

    public function login(Request $req){
		$respuesta = ["status" => 1, "msg" => ""];

		$datos = $req->getContent();
		$datos = json_decode($datos);
    	
    	$name = $datos->name;

		$user = User::where('name', '=', $name)->first();

		if($user){
			if (Hash::check($datos->password, $user->password)) {
	            do{
	        		$apitoken = Hash::make($user->id.now());

	            }while(User::where('api_token', $apitoken)->first());

	            $user->api_token = $apitoken;
	            $user->save();
	            $respuesta['msg'] = "Login correcto".$user->api_token;


			}else {
	        	$respuesta['status'] = 0;
		        $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();		
			}
		}else{
			$respuesta['status'] = 0;
	        $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();	
		}
		return response()->json($respuesta);
    }

}

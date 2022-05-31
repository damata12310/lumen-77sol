<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required|confirmed'
        ]);

        $email = $request->input('email');
        $password = Hash::make($request->input('password'));

        User::create(['email' => $email, 'password' => $password]);

        return response()->json(['status' => 'succes', 'operation' => 'created']);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function curlGoogleMaps($address, $key)
    {
        $addressGoogle = str_replace(' ', '%20', $address);

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$addressGoogle."&key=".$key;
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'key: '.$key,
            'address: '.$addressGoogle
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);


      return $response;
    }

    // public function buscaCepSimuladorTeste($estrutura, $estado, $cidade, $valor_conta, $cep, $latitude, $longitude)
    public function buscaCepSimuladorTeste(Request $request)
    {
        $estrutura = $request->input('estrutura'); 
        $estado = $request->input('estado');  
        $cidade = $request->input('cidade');  
        $valor_conta = $request->input('valor_conta'); 
        $cep = $request->input('cep');  
        $latitude = $request->input('latitude'); 
        $longitude = $request->input('longitude'); 

        $url = "https://api2.77sol.com.br/busca-cep?estrutura=".$estrutura."&estado=".
        $estado."&cidade=".$cidade."&valor_conta=".$valor_conta."&cep=".$cep."&latitude=".$latitude."&longitude=".$longitude;
        
        $response = file_get_contents($url);
        return $response;
    }
    
    public function simulador(Request $request)
    {

        $this->validate($request, [
          'cep' => 'required',
          'valor_conta' => 'required',
          'tipo_telhado' => 'required'
        ]);

        $viaCep = "https://viacep.com.br/ws/".$request->cep."/json/";

        $resultadoViaCep = file_get_contents($viaCep);

        $decodeResult = json_decode($resultadoViaCep);

        $addressGoogle = trim($decodeResult->logradouro." - ".$decodeResult->bairro.", ".$decodeResult->localidade." - ".$decodeResult->uf.", ".$decodeResult->cep);
        $apikey = $_ENV['API_KEY_GOOGLE'];
        
        //echo $this->buscaCepSimuladorTeste('fibrocimento-metalico', 'SP', 'Itaquaquecetuba', '200', '08590510', '-23.4', '-46.2');

        $googleMaps = $this->curlGoogleMaps($addressGoogle, $apikey);

        return $googleMaps;

    }
}
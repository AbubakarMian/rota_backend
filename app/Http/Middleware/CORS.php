<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Origin'=> '*',//http://localhost:8080
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'authorization,client-id,content-type, Accept, Authorization, X-Requested-With, Application'
        ];
        if($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        foreach($headers as $key => $value){
            $response->headers->set($key, $value);
        }
//             $response->headers->set('Access-Control-Allow-Origin' , '*');
// $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
// $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');

        return $response;
    }
}

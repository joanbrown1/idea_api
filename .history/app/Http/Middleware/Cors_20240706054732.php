<?php

namespace App\Http\Middleware;
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class Cors
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->setCorsHeaders(response('', 200));
        }

        $response = $next($request);
        
        return $this->setCorsHeaders($response);
    }

    protected function setCorsHeaders($response)
    {
        if ($response instanceof Response || $response instanceof JsonResponse) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
        }

        return $response;
    }
}

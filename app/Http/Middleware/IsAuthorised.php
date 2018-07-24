<?php

namespace App\Http\Middleware;

use Closure;

class IsAuthorised
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(auth()->check()){
            $authorised = auth()->user()->authorised;
            if(!$authorised){
                auth()->logout();
                return redirect('/login');
            }
        }

        return $next($request);
    }
}

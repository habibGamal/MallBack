<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $count = auth('admin')->user()->store->branches->count();
        if($count > 0){
            return $next($request);
        }
        return redirectJson('ADMIN_NOT_COMPLETE');
    }
}

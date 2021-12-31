<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class admin_own_branch
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
        $check = auth('admin')->user()->store->branches->find($request->input('branch_id'));
        if(!empty($check)){
            return $next($request);
        }
        return redirectJson('ADMIN_NOT_OWN_BRANCH');
    }
}

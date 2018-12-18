<?php

namespace Exceedone\Exment\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Facades\Admin;

class Authenticate extends \Encore\Admin\Middleware\Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $shouldPassThrough = $this->shouldPassThrough($request);
        if($shouldPassThrough){
            return $next($request);
        }

        $user = \Admin::user();
        if(is_null($user) || is_null($user->base_user)){
            return redirect()->guest(admin_base_path('auth/login'));
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = [
            admin_base_path('auth/login'),
            admin_base_path('auth/logout'),
            admin_base_path('initialize'),
        ];

        foreach ($excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace crocodicstudio\crudbooster\middlewares;

use Closure;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class CBSuperadmin
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
        $adminPath = cbConfig('ADMIN_PATH', 'admin');

        if(CRUDBooster::myId()==''){
            $url = url($adminPath.'/login');
            return redirect($url)->with('message', cbTrans('not_logged_in'));
        }

        if(!CRUDBooster::isSuperadmin()) {
            return redirect($adminPath)->with(['message'=> cbTrans('denied_access'),'message_type'=>'warning']);
        }

        if(CRUDBooster::isLocked()){
            return redirect(url($adminPath.'/lock-screen'));
        }

        return $next($request);
    }
}

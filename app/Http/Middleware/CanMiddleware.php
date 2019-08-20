<?php

namespace App\Http\Middleware;

use App\Library\Response;
use Closure;
use Illuminate\Support\Facades\Redis;

class CanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null){

        $redis = Redis::connection();
        $redis->select(0);
        $login_session_id = $redis->get('login_'.session('user.id'));
        if($login_session_id != session()->getId()){
            if ($request->ajax()) {
                return response(['code' => Response::OTHER_LOGIN, 'msg' => '该账号已被其他用户登陆', 'data' => []]);
            }
            return redirect(route('admin.forbidden.otherlogin'));
        }
       if (!can()) {
            if ($request->ajax()) {
                return response(['code' => Response::FORBIDDEN, 'msg' => '您没有被授权访问', 'data' => []]);
            }
            return redirect(route('admin.forbidden.white'));
        }

        return $next($request);
    }
}

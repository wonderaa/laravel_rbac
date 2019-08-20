<?php

namespace App\Http\Middleware;

use App\Http\Models\AuthRule;
use App\Http\Models\Menu;
use App\Http\Models\Menu as MenuModel;
use App\Http\Models\Permission;
use App\Http\Models\Users;
use App\Http\Models\UsersPermission;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class MenuMiddleware
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
        $user = $request->session()->get('user');

        $menu_tree = [];
        $menu_arr  = [];
        $menus  = [];


        if ($user['administrator'] == Users::ADMIN_YES) {
            $menus     = MenuModel::all()->toArray();
            foreach ($menus as $m) {
                $menu_arr[$m['id']] = $m;
            }
            //超管获取所有菜单
            MenuModel::menuTree($menu_arr, $menu_tree);
        } else {
            $redis = Redis::connection();
            $redis->select(2);
            if($redis->get('caiwu_menu_'.$user['id'])){
                $menus = json_decode($redis->get('caiwu_menu_'.$user['id']),true);

            }else{
                $permission_ids = UsersPermission::where('users_id', '=', $user['id'])->pluck('permission_id')->toArray();

                $permission_route_id = Permission::whereIn('id', $permission_ids)->get()->toArray();

                if($permission_route_id){
                    $route_id = '';
                    if($permission_route_id){
                        foreach ($permission_route_id as $v){
                            $route_id .= $v['routes'].',';
                        }
                    }

                    $route_id = substr($route_id,0,-1);
                    $route_arr = explode(',',$route_id);
                    $rules_arr = AuthRule::whereIn('id',$route_arr)->pluck('name')->toArray();
                    $menus    = MenuModel::whereIn('route', $rules_arr)->get()->toArray();
                    $redis->set('caiwu_menu_'.$user['id'],json_encode($menus));
                    $redis->expire('caiwu_menu_'.$user['id'],60);
                }
            }

            if($menus){
                $menu_tmp = [];
                foreach ($menus as $m) {
                    $menu_tmp[$m['id']] = $m;
                    //同时获取父级菜单
                    if ($m['pid'] != 0 && !key_exists($m['pid'], $menu_tmp)) {
                        $menu_tmp[$m['pid']] = $menu_arr[$m['pid']];
                    }
                }
                MenuModel::menuTree($menu_tmp, $menu_tree);
            }
        }
        View::share('menu_tree', $menu_tree);

        //控制菜单选中效果
        $currRouteName = Route::currentRouteName();
        $cache_key = 'menu_route_' . session('user')['id'];
        if (Menu::where('route', $currRouteName)->count() > 0) {
            //当前路由为菜单
            Cache::put($cache_key, $currRouteName, 120);
        } else {
            if (Cache::has($cache_key)) {
                $currRouteName = Cache::get($cache_key);
            }
        }
        View::share('currRouteName', $currRouteName);

        return $next($request);
    }
}

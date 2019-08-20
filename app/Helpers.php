<?php

use App\Http\Models\AuthRule;
use App\Http\Models\Permission;

function can(){
    $currRouteName = trim(\Illuminate\Support\Facades\Route::currentRouteName());

    if (ends_with($currRouteName, 'white')) {
        return true;
    }

    $user = session()->get('user');
    if ($user['administrator'] == \App\Http\Models\Users::ADMIN_YES) {

        return true;
    }

    $permission_ids = \App\Http\Models\UsersPermission::where('users_id', $user['id'])->pluck('permission_id')->toArray();

    $permission_route_id = \App\Http\Models\Permission::whereIn('id', $permission_ids)->get()->toArray();
    if($permission_route_id){
        $route_id = '';
        if($permission_route_id){
            foreach ($permission_route_id as $v){
                $route_id .= $v['routes'].',';
            }
        }
        $route_id = substr($route_id,0,-1);
        $route_arr = explode(',',$route_id);
        $rules_arr = AuthRule::whereIn('id',$route_arr)->where('name',$currRouteName)->pluck('name')->toArray();
        if ($rules_arr > 0) {
            return true;
        }
    }
    return false;
}

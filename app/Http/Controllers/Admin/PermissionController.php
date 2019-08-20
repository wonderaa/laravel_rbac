<?php
/**
 * User:
 * Date: 2019/5/6 上午10:11
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Models\Permission;
use App\Http\Models\RolePermission;
use App\Library\Response;
use App\Service\RouteService;
use App\Validate\PermissionStoreValidate;
use App\Validate\PermissionUpdateValidate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(config('page_size'));
        $rule_list = DB::table("auth_rule")->select(['id','title','name'])->get()->toArray();
        return view('admin.permission.index', ['permissions' => $permissions,'rule_list'=>$rule_list]);
    }

    public function create(){

        $routes = RouteService::getRoutes();
        $rule_list = DB::table("auth_rule")->select(['id','name','title'])->get()->toArray();

        $new_routes = [];
        foreach ($rule_list as $k=>$v){
            foreach ($routes as $key=>$val){
                if($v->name == $val){
                    $new_routes[$v->id] = $v->title;
                }else if($v->name == "menu"){
                    $new_routes[$v->id] = $v->title;
                }
            }
        }
        return view('admin.permission.create', ['routes' => $new_routes]);
    }

    public function store(Request $request)
    {
        $validate = new PermissionStoreValidate($request);

        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;
        $permission = new Permission();

        $permission->name   = $params['name'];
        $permission->routes = implode(',', $params['route']);

        if (!$permission->save()) {
            return Response::response(Response::SQL_ERROR);
        }
        return Response::response();
    }

    public function edit(Request $request){
        $permission_id = $request->get('permission_id');

        $error      = '';
        $permission = null;

        if (!$permission_id) {
            $error = '参数有误';
        } else {
            $permission = Permission::find($permission_id);
            if (!$permission) {
                $error = '获取权限信息错误';
            } else {
                $permission->routes = explode(',', $permission->routes);
            }
        }

        $routes = RouteService::getRoutes();
        $rules_list = DB::table("auth_rule")->select(['id','name','title'])->get()->toArray();

        $new_routes = [];
        foreach ($rules_list as $k=>$v){
            foreach ($routes as $key=>$val){
                if($v->name == $val){
                    $new_routes[$v->id] = $v->title;
                }else if($v->name == "menu"){
                    $new_routes[$v->id] = $v->title;
                }
            }
        }
        return view('admin.permission.edit', ['permission' => $permission, 'error' => $error, 'routes' => $new_routes]);
    }

    public function update(Request $request)
    {
        $validate = new PermissionUpdateValidate($request);

        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;

        $permission = Permission::find($params['id']);

        $permission->name   = $params['name'];
        $permission->routes = implode(',', $params['route']);

        if (!$permission->save()) {
            return Response::response(Response::SQL_ERROR);
        }
        return Response::response();
    }

    public function delete(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return Response::response(Response::PARAM_ERROR);
        }

        DB::beginTransaction();
        try {
            Permission::where('id', $id)->delete();
            RolePermission::where('roles_id', $id)->delete();
            DB::commit();
            return Response::response();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('删除权限组数据库异常', [$e->getMessage()]);
            return Response::response(Response::SQL_ERROR);
        }
    }
}
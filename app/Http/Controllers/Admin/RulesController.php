<?php

namespace App\Http\Controllers\Admin;

use App\Http\Models\AuthRule;
use App\Http\Models\Menu;
use App\Http\Models\MenuRoles;
use App\Http\Models\Roles;
use App\Library\Response;
use App\Service\RouteService;
use App\Validate\AuthRuleStoreValidate;
use App\Validate\AuthRulesUpdateValidate;
use App\Validate\MenuUpdateValidate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RulesController extends Controller{
    //
    public function index(){
        $rules     = AuthRule::all();
        $rules_arr = [];
        foreach ($rules as $m) {
            $rules_arr[] = [
                'id'         => $m['id'],
                'title'      => $m['title'],
                'pid'        => $m['pid'],
                'name'      => $m['name'],
                'update_at' => $m['update_at'],

            ];
        }

        return view('admin.rules.index', ['rules' => json_encode($rules_arr)]);
    }
    public function create(){

        $top_menu = AuthRule::where('pid', '=', 0)->select('id', 'name','title')->get();
        $routes   = RouteService::getRoutes();
        return view('admin.rules.create', ['top_menu' => $top_menu, 'routes' => $routes]);
    }
    /**
     * 2019-06-25
     * 添加权限
     * */
    public function store(Request $request){

        $validate = new AuthRuleStoreValidate($request);
        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;
        try {
            $rules = new AuthRule();
            $rules->title  = $params['title'];
            $rules->pid   = $params['pid'];
            $rules->name = $params['name']?$params['name']:'menu';
            $rules->status = 1;
            $rules->update_at = time();

            $rules->save();
            return Response::response();
        } catch (QueryException $e) {
            Log::error('创建数据库异常', [$e->getMessage()]);
            return Response::response(Response::SQL_ERROR);
        }
    }

    public function edit(Request $request){
        $rules_id = $request->get('rules_id');
        $error = '';
        $rules  = null;
        if (!$rules_id) {
            $error = '参数有误';
        } else {
            $rules = AuthRule::find($rules_id);
            if (!$rules) {
                $error = '获取路由信息错误';
            }
        }

        //获取顶级菜单，排除当前菜单
        $top_menu = AuthRule::where('pid', '=', 0)->where('id', '!=', $rules_id)->select('id', 'name','title')->get();

        //获取所有路由标识
        $routes = RouteService::getRoutes();

        return view('admin.rules.edit', ['error' => $error, 'rules' => $rules, 'top_menu' => $top_menu, 'routes' => $routes]);
    }

    public function update(Request $request){
        $validate = new AuthRulesUpdateValidate($request);
        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;
        DB::beginTransaction();
        try {
            $rules = AuthRule::find($params['id']);

            $rules->title  = $params['title'];
            $rules->pid   = $params['pid'];
            $rules->name = $params['name']?$params['name']:'menu';
            $rules->update_at = time();
            $rules->save();
            DB::commit();
            return Response::response();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('菜单更新数据库异常', [$e->getMessage()]);
            return Response::response(Response::SQL_ERROR);
        }
    }

    public function delete(Request $request){
        $id = $request->get('id');
        if (!$id) {
            return Response::response(Response::PARAM_ERROR);
        }

        //初始化的菜单及子菜单不能被删除
        if ($id == 1) {
            return Response::response(Response::BAD_REQUEST, '当前路由不能被删除');
        }

        $rules = AuthRule::find($id);
        if (!$rules || $rules->pid == 1) {
            return Response::response(Response::BAD_REQUEST, '当前菜单不能被删除');
        }

        $sub_count = AuthRule::where('pid', $id)->count();
        if ($sub_count > 0) {
            return Response::response(Response::BAD_REQUEST, '请先删除子菜单');
        }

        DB::beginTransaction();
        try {
            AuthRule::where('id', $id)->delete();
            DB::commit();
            return Response::response();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('删除菜单数据库异常', [$e->getMessage()]);
            return Response::response(Response::SQL_ERROR);
        }
    }
}

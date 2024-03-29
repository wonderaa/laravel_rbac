<?php
/**
 * User:
 * Date: 2019/5/6 上午10:11
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Models\Permission;
use App\Http\Models\Users;
use App\Http\Models\UsersPermission;
use App\Library\Response;
use App\Validate\UserStoreValidate;
use App\Validate\UserUpdateValidate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller{
    public function index(){

        $users = Users::select('id', 'username', 'administrator', 'status', 'created_at')->paginate(config('web.page_size'));

        return view('admin.user.index', ['users' => $users]);
    }

    public function create()
    {
        $permission = Permission::all();
        return view('admin.user.create', ['permission' => $permission]);
    }

    public function store(Request $request)
    {


        $validate = new UserStoreValidate($request);
        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;

        DB::beginTransaction();
        try {
            $user = new Users();
            $user->realname         = $params['realname'];
            $user->username         = $params['username'];
            $user->password      = Hash::make($params['password']);
            $user->status        = $params['status'];
            $user->administrator = $params['administrator'];
            $user->creator_id    = session('user')['id'];
            $user->save();

            $permission = $params['permission'] ?? '';
            if ($permission) {
                $pivot = [];
                foreach ($params['permission'] as $role) {
                    $pivot[] = [
                        'users_id'   => $user->id,
                        'permission_id'   => $role,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                UsersPermission::insert($pivot);
            }

            DB::commit();
            return Response::response();
        } catch (QueryException $e) {
            DB::rollBack();
            return Response::response(Response::SQL_ERROR);
        }

    }

    public function edit(Request $request)
    {
        $user_id = $request->get('user_id');

        $error    = '';
        $user     = null;
        $role_ids = [];
        if (!$user_id) {
            $error = '参数有误';
        } else {
            $user = Users::find($user_id);
            if (!$user) {
                $error = '用户信息错误';
            } else {
                $role_ids = UsersPermission::where('users_id', '=', $user_id)->pluck('permission_id')->toArray();
            }
        }

        $permission = Permission::all();

        return view('admin.user.edit', ['permission' => $permission, 'error' => $error, 'role_ids' => $role_ids, 'user' => $user]);
    }

    public function update(Request $request)
    {
        $validate = new UserUpdateValidate($request);
        if (!$validate->goCheck()) {
            return Response::response(Response::PARAM_ERROR, $validate->errors->first());
        }

        $params = $validate->requestData;

        DB::beginTransaction();
        try {
            $user = Users::find($params['id']);

            $user->realname         = $params['realname'];
            $user->username         = $params['username'];
            //$user->status        = $params['status'];
            $user->administrator = $params['administrator'];

            $password = $params['password'] ?? '';
            if ($password) {
                $user->password = Hash::make($params['password']);
            }
            $user->save();

            //删除原有用户-角色关系
            UsersPermission::where('users_id', '=', $params['id'])->delete();

            $permission = $params['permission'] ?? '';
            if ($permission && $user->administrator == Users::ADMIN_NO) {
                $pivot = [];
                foreach ($params['permission'] as $role) {
                    $pivot[] = [
                        'users_id'   => $user->id,
                        'permission_id'   => $role,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                UsersPermission::insert($pivot);
            }

            DB::commit();
            return Response::response();
        } catch (QueryException $e) {
            DB::rollBack();
            return Response::response(Response::SQL_ERROR);
        }

    }

    /**
     * 修改用户状态
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function status(Request $request)
    {
        $user_id = $request->get('user_id');
        if (!$user_id) {
            return Response::response(Response::PARAM_ERROR);
        }

        if ($user_id == session('user')['id']) {
            return Response::response(Response::BAD_REQUEST, '你不能修改自己的状态');
        }

        $user = Users::find($user_id);
        if (!$user) {
            return Response::response(Response::BAD_REQUEST);
        }

        if ($user->administrator == Users::ADMIN_YES && $user->status == Users::STATUS_ENABLE) {
            //除了当前管理员，至少有一个启用状态的管理员
            if (Users::where('id', '!=', $user_id)->where('administrator', '=', Users::ADMIN_YES)->where('status', '=', Users::STATUS_ENABLE)->count() <= 0) {
                return Response::response(Response::BAD_REQUEST, '至少有一个管理员');
            }
        }

        $user->status = $user->status == Users::STATUS_ENABLE ? Users::STATUS_DISABLE : Users::STATUS_ENABLE;

        if (!$user->save()) {
            return Response::response(Response::SQL_ERROR);
        }
        return Response::response();
    }

    public function reset(Request $request)
    {
        $user_id = $request->get('id');
        if (!$user_id) {
            return Response::response(Response::PARAM_ERROR);
        }

        $user = Users::find($user_id);
        if (!$user || $user->status != Users::STATUS_ENABLE) {
            //启用的用户才可以重置密码
            return Response::response(Response::BAD_REQUEST);
        }

        //统一重置密码为admin123
        $user->password = Hash::make('admin123');

        if (!$user->save()) {
            return Response::response(Response::SQL_ERROR);
        }
        return Response::response(Response::OK, '密码已成功重置为：admin123');
    }
}
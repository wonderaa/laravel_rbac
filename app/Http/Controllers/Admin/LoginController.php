<?php
/**
 * User:
 * Date: 2019/5/5 下午5:24
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Models\LoginLog;
use App\Http\Models\Users;
use App\Library\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index()
    {
        if (session('user')) {
            return redirect(route('admin.index.white'));
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required',
            'password' => 'required',
        ], [
            'username.required'    => '请输入登录名',
            'password.required' => '请输入密码',
        ]);

        if ($validator->fails()) {
            return Response::response(Response::PARAM_ERROR, $validator->errors()->first());
        }

        $data = $validator->getData();

        $user = Users::where('username', '=', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return Response::response(Response::BAD_REQUEST, '登录名或密码有误');
        }

        if ($user->status == Users::STATUS_DISABLE) {
            return Response::response(Response::BAD_REQUEST, '您的账户被禁用，请联系管理员');
        }

        session(['user' => $user]);
        $user->session_id = session()->getId();
        $user->save();
        $login_log = new LoginLog();
        $login_log->user_id = $user->id;
        $login_log->create_at = time();
        $login_log->login_ip = $request->getClientIp();
        $login_log->save();
        $redis = Redis::connection();
        $redis->select(0);
        $redis->set('login_'.$user->id,session()->getId());
        return Response::response();
    }

    public function logout(Request $request){
        $user = session()->get('user');
        $redis = Redis::connection();
        $redis->select(0);
        $redis->del('login_'.$user['id'],session()->getId());
        $request->session()->forget('user');

        return redirect(route('admin.login.white'));
    }

    public function test(Request $request){
        $url = 'http://www.locpay.com/preorderbak190812.php?rid=1679432&price=5000&channel=test';

        echo '<meta http-equiv="refresh" content="2;url='.$url.'"> ';exit();
        header('Location:'.$url);
//        $ch = curl_init();//启动一个CURL会话
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置请求超时时间
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl获取页面内容, 不直接输出
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//        $data = curl_exec($ch); // 已经获取到内容，没有输出到页面上。
//        curl_close($ch);
//        print_r($data);
    }
}
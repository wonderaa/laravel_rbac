<?php

namespace App\Http\Controllers\Admin;

use App\Library\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class WidthdrawController extends Controller
{
    protected static  $bank_list  = [
        102=>'工商银行',
        103=>'农业银行',
        104=>'中国银行',
        105=>'建设银行',
        403=>'邮储银行',
        308=>'招商银行'
    ];
    /**
     * 2019-06-26
     *绑定银行卡
    */
    public function bind_bank_card(){
        return view('admin.widthdraw.bind_bank_card',['bank_list'=>self::$bank_list]);
    }

    public function store(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $realname     = $request->get('realname','');
        $bank_account = $request->get('bank_account','');
        $bank_branch  = $request->get('bank_branch','');
        $bank_code    = $request->get('bank_code','');
        $bank_province = $request->get('bank_province','');
        $bank_city     = $request->get('bank_city','');

        if(!$bank_code){
            return Response::response(-1,"请选择银行");
        }

        if(!$realname){
            return Response::response(-1,"请填写真实姓名");
        }

        if(!$bank_account){
            return Response::response(-1,"请填写银行卡号");
        }


        $redis = Redis::connection();
        $redis->select(15);
        $bank_info = $redis->get('bank_info');
        if($bank_info){
            $bank_info = json_decode($bank_info,true);
        }
        $tmp = [
            'realname'=>$realname,
            'bank_account'=>$bank_account,
            'bank_branch'=>$bank_branch,
            'bank_name'=>self::$bank_list[$bank_code],
            'bank_code'=>$bank_code,
            'bank_province'=>$bank_province,
            'bank_city'=>$bank_city,
        ];

        $bank_info[$bank_code] = $tmp;
        $redis->set("bank_info",json_encode($bank_info));
        return Response::response(0,"设置成功");
    }

    /**
     * 2019-06-28
     * 获取银行信息
     */
    public function get_bank_info(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $bank_code     = $request->get('bank_code','');
        if(!$bank_code){
            return Response::response(-1,"请选择银行");
        }

        $redis = Redis::connection();
        $redis->select(15);
        $bank_info = $redis->get('bank_info');

        if($bank_info){
            $bank_info = json_decode($bank_info,true);
            if(isset($bank_info[$bank_code])){
                return Response::response(0,"",$bank_info[$bank_code]);
            }
        }
        return Response::response(-1,"暂未设置");
    }
    /**
     * 2019-06-28
     * 提款申请
     */
    public function index(){

        return view('admin.widthdraw.index',['bank_list'=>self::$bank_list]);
    }
    public function remit_act(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $bank_code   = $request->get('bank_code','');
        $remit_type  = $request->get('remit_type','');
        $amount      = intval($request->get('amount',''));

        if(!$bank_code){
            return Response::response(-1,"请选择银行");
        }

        if(!$remit_type){
            return Response::response(-1,"请选择出款商户");
        }

        if($amount<100){
            return Response::response(-1,"提款金额必须大于100");
        }
        $redis = Redis::connection();
        $redis->select(15);
        $bank_info = $redis->get('bank_info');
        if(!$bank_info){
            return Response::response(-1,"尚未填写该银行账户");
        }
        $bank_info = json_decode($bank_info,true);
        if(!isset($bank_info[$bank_code])){
            return Response::response(-1,"尚未填写该银行账户");
        }

        print_r($bank_info[$bank_code]);

    }
}

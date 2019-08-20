<?php

namespace App\Http\Controllers\Admin;

use App\Http\Models\ApplyWidthdraw;
use App\Library\AlipayService;
use App\Library\GoogleVerify;
use App\Library\Response;
use Common\Controller\AlipayServiceController;
use Common\Controller\MyRedis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller{

    protected static $reset_order_id = [10];
    //订单过期时间 默认一周
    protected static $expire_time = 86400*7;
    /**
     * 2019-06-26
     * 接单页面
     * @params user_type 1:支付宝 2:银行卡 3:支付宝自动 4:银行卡自动',
     * DB::connection()->enableQueryLog();

       print_r(DB::getQueryLog());die;
     */
    public function apply(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $order_id = $request->get('order_id','');
        $realname = $request->get('realname','');
        $bank_name    = $request->get('bank_name','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_id'=>$order_id,
            'realname'=>$realname,
            'bank_name'=>$bank_name,
        ];
        $where = [['state',0]];
        if($search_s){
            $tmp_arr = ['create_at',">=",strtotime($search_s)];
            array_push($where,$tmp_arr);
        }
        if($search_e){
            $tmp_arr = ['create_at',"<=",strtotime($search_e)];
            array_push($where,$tmp_arr);
        }
        if($user_id){
            $tmp_arr = ['user_id',intval($user_id)];
            array_push($where,$tmp_arr);
        }
        if($order_id){
            $tmp_arr = ['order_sn','like','%'.$order_id];
            array_push($where,$tmp_arr);
        }
        if($realname){
            $tmp_arr = ['realname','like','%'.$realname.'%'];
            array_push($where,$tmp_arr);
        }
        if($bank_name){
            $tmp_arr = ['bank_name','like','%'.$bank_name.'%'];
            array_push($where,$tmp_arr);
        }
        $receive_user_info = session()->get('user');
        if(in_array($receive_user_info['user_type'],[1,3])){
            $tmp_arr = ['bank_name','支付宝'];
            array_push($where,$tmp_arr);
        }
        if(in_array($receive_user_info['user_type'],[2,4])){
            $tmp_arr = ['bank_name','<>','支付宝'];
            array_push($where,$tmp_arr);
        }

        $data = DB::table('apply_widthdraw_diamond')
            ->where($where)
            ->paginate(config("web.page_size"));

        $count = DB::table('apply_widthdraw_diamond')->where($where)->count();
        return view('admin.order.apply',['data'=>$data,'count'=>$count,'search_data'=>$search_data,'user'=>$receive_user_info]);
    }
    /**
     * 2019-06-26
     * 系统接单
     */
    public function receive_order(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $receive_user_info = session()->get('user');

        //如果用户积压订单超过20单,停止接单
        if(DB::table('apply_widthdraw_diamond')
            ->where([['receive_id',$receive_user_info['id'],['state',1]]])
            ->count()>20){
            return Response::response(-1,'请先处理完其他订单后再接单');
        }
        $where = [['state',0],['receive_id',0]];
        if(in_array($receive_user_info['user_type'],[1,3])){
            $tmp_arr = ['bank_name','支付宝'];
            array_push($where,$tmp_arr);
        }
        if(in_array($receive_user_info['user_type'],[2,4])){
            $tmp_arr = ['bank_name','<>','支付宝'];
            array_push($where,$tmp_arr);
        }
        $order_info = ApplyWidthdraw::where($where)
            ->orderBy('create_at','asc')
            ->first();
        if(!$order_info){
            return Response::response(-1,'系统暂无订单');
        }
        if($order_info->state != 0){
            return Response::response(-1,'该订单已被接收');
        }
        //防止重复接单
        $redis = Redis::connection();
        $redis->select(1);
        if(!$redis->setnx($order_info->order_sn,1)){
            $redis->expireat($order_info->order_sn,time()+self::$expire_time);
            return Response::response(-1,'该订单已被接收');
        }
        $order_info->receive_id = $receive_user_info['id'];
        $order_info->state = 1;
        $order_info->receive_at = time();
        if($order_info->save()){
            if($order_info->widthdraw_type==1){
                /*** 如果是推广员提现修改提现状态*/
                $this->mod_widthdraw_state($order_info,1);
            }else{
                $this->returnToGm($order_info,1);
            }
            //记录日志
            $this->operator_order_log($receive_user_info['id'],$order_info->order_sn,0,1);
            return Response::response(0,'接单成功');
        }else{
            //如果写入失败则 清除订单状态
            $redis->del($order_info->order_sn);
            return Response::response(-1,'接单失败');
        }
    }
    /**
     * 2019-06-026
     * 手动接单
    */
    public function hand_receive_order(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $receive_user_info = session()->get('user');

        //如果用户积压订单超过20单,停止接单
        if(DB::table('apply_widthdraw_diamond')
                ->where([['receive_id',$receive_user_info['id'],['state',1]]])
                ->count()>20){
            return Response::response(-1,'请先处理完其他订单后再接单');
        }

        $order_id = intval($request->get('order_id',''));
        $order_info = ApplyWidthdraw::where([['receive_id',0],['state',0],['id',$order_id]])
            ->first();

        if(!$order_info){
            return Response::response(-1,'订单已被处理');
        }

        $redis = Redis::connection();
        $redis->select(1);
        if(!$redis->setnx($order_info->order_sn,1)){
            $redis->expireat($order_info->order_sn,time()+self::$expire_time);
            return Response::response(-1,'该订单已被接收');
        }

        $order_info->receive_id = $receive_user_info['id'];
        $order_info->state = 1;
        $order_info->receive_at = time();
        if($order_info->save()){
            if($order_info->widthdraw_type==1){
                /*** 如果是推广员提现修改提现状态*/
                $this->mod_widthdraw_state($order_info,1);
            }else{
                $this->returnToGm($order_info,1);
            }
            //记录日志
            $this->operator_order_log($receive_user_info['id'],$order_info->order_sn,0,1);
            return Response::response(0,'接单成功');
        }else{
            //如果写入失败则 清除订单状态
            $redis->del($order_info->order_sn);
            return Response::response(-1,'接单失败');
        }
    }

    /**
     * 2019-06-26
     * 接单日志
     */
    protected  function operator_order_log($user_id,$order_sn,$ori_state,$cur_state){
        $log_dir = './Log/receive_order';
        if(!file_exists($log_dir)){
            mkdir($log_dir,0777,true);
        }
        $fp = fopen($log_dir.'/'.date('Y-m-d').'.txt',"a+");
        fputs($fp,date('Y-m-d H:i:s').','.$user_id.",".$order_sn.",".$ori_state.",".$cur_state.",receive_order\r\n");
        fclose($fp);
    }
    /**
     * 2018-10-11
     * 如果是推广员提现修改提现状态
     */
    private function mod_widthdraw_state($order_info,$state,$remark=""){
        $this->write_order_state_log($order_info,$state,$remark);
        DB::connection('mysql_web')
           ->table('tg_widthdraw_record')
           ->where([['order_num',$order_info['order_sn']],['aid',$order_info['user_id']]])
           ->update(['state'=>$state,'remark'=>$remark]);
        //恢复可提现金 退回状态
        if($state==3||$state==5){
            DB::connection('mysql_web')
                ->table('tg_agency_remaid_info')
                ->where('aid',$order_info['user_id'])
                ->increment('widthdraw',($order_info['apply_amount']));
        }
    }
    /**
     * 记录订单状态变化日志
     */
    private function  write_order_state_log($order_info,$to_state,$remark=""){
        $receive_user_info = session()->get('user');
        $save_data = [
            'ori_state'=>$order_info->state,
            'up_state'=>$to_state,
            'operator_id'=>$receive_user_info->id,
            'order_num'=>$order_info->order_sn,
            'user_id'=>$order_info->user_id,
            'withdraw'=>$order_info->amount,
            'user_type'=>$order_info->widthdraw_type,
            'create_at'=>time(),
            'remark'=>$remark,
        ];
       DB::table('order_log')->insert($save_data);
    }
    /**
     * 2018-09-28
     * 处理后返回给游戏端
     * @params 1 用户已接收 2 转账成功 3 转账失败返回给用户
     * 失败只记录 不返还给用户
     */
    protected function returnToGm($order_info,$state,$remark="failed"){
        if($order_info->state!=3){
            //如果是已出款在退款
            if($order_info->is_draw==1 && $state==3){
                $state = 5;
            }
            $redis = Redis::connection('game_redis');
            $redis->select(15);
            $push_data = array(
                'msghead'=>array( 'msgname'=>"update_withdraw_state",'sign'=>'1'),
                'msgbody'=>array(
                    'rid'=>intval($order_info->user_id),
                    'keyid'=>$order_info->keyid,
                    'state'=>intval($state),
                    'amount'=>$order_info->amount,
                    'remark'=>$remark
                )
            );
            $redis->rPush('inwithdrawmq',json_encode($push_data));
            //提现退回说明邮件
            if($state==3){
                $send_data = array(
                    'msghead'=>array(
                        'msgname'=>"sendmail_to_rids"
                    ),
                    'msgbody'=>array(
                        'rids'=>[$order_info->user_id],
                        'fields'=>array(
                            'subject'=>"金币提现退回",
                            'content'=>"提现".$order_info->apply_amount."元,金币提现退回",
                            'attach_json'=>[],
                            'mail_type'=>0,
                        )
                    ),
                    'return_id'=>0
                );
                $redis->rPush('requestmq',json_encode($send_data));
            }

            $this->write_order_state_log($order_info,$state,$remark);
        }
    }
    /**
     * 2019-06-26
     * 重置订单状态
     */
    public function reset_order_state(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $receive_user_info = session()->get('user');
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $verify_code  = $request->get('verify_code','');
        //google验证码
        $google_verify  = new GoogleVerify();
        $google_code  = $google_verify->getCode(config('web.google_reset_order_key'));
        if($google_code != $verify_code){
            return Response::response('-1','验证码错误');
        }

        $order_info =  ApplyWidthdraw::where([
                ['id',$order_id],
                ['user_id',$user_id]
            ])
            ->first();

        if(!$order_info){
            return Response::response('-1','订单不存在');
        }

        if($order_info->state == 3 || $order_info->state == 5){
            return Response::response('-1',"已返还游戏账户，不能回退请联系后台审核");
        }

        $order_info->receive_id =  $receive_user_info['id'];
        $order_info->state =  1;
        $order_info->update_at =  time();
        $order_info->receive_at =  time();
        if($order_info->save()){
            if($order_info->widthdraw_type==1){
                /*** 如果是推广员提现修改提现状态*/
                $this->mod_widthdraw_state($order_info,1);
            }else{
                $this->returnToGm($order_info,1);
            }
            $redis = Redis::connection();
            $redis->select(2);
            $redis->del($order_info->order_sn);

            if($order_info->bank_name=='支付宝'){
                DB::table('drawalirecord')
                    ->where([
                        ['user_id',$order_info->user_id],
                        ['order_sn',$order_info->order_sn]
                    ])
                    ->delete();
            }else{
                DB::table('drawbankrecord')
                    ->where([
                        ['user_id',$order_info->user_id],
                        ['order_sn',$order_info->order_sn]
                    ])
                    ->delete();
            }

            return Response::response(0,"重置成功");
        }else{
            return Response::response('-1',"重置订单失败");
        }
    }
    /**
     * 2019-06-26
     * 全部订单
     */
    public function index(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $order_id = $request->get('order_id','');
        $state = $request->get('state','-1');
        $realname    = $request->get('realname','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_id'=>$order_id,
            'state'=>$state,
            'realname'=>$realname,
        ];
        $user_info = session()->get('user');
        $where = [];
        if($search_s){
            $tmp_where = ['create_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }
        if($order_id){
            $tmp_where = ['order_sn',"like",'%'.$order_id];
            array_push($where,$tmp_where);
        }
        if($realname){
            $tmp_where = ['realname',"like",'%'.$realname];
            array_push($where,$tmp_where);
        }
        if($state>=0 && $state != 3){
            $tmp_where = ['state',$state];
            array_push($where,$tmp_where);
        }
        if($user_info['id'] != 10){
            $tmp_where = ['receive_id',$user_info['id']];
            array_push($where,$tmp_where);
        }
        if($state ==3){
            $data = DB::table('apply_widthdraw_diamond')
                ->where($where)
                ->where(function($query){
                    $query->orWhere('state',3)->orWhere('state',5);
                })
                ->paginate(config('web.page_size'));
            $count = DB::table('apply_widthdraw_diamond')
                ->where($where)
                ->where(function($query){
                    $query->orWhere('state',3)->orWhere('state',5);
                })
                ->count();
        }else{
            $data = DB::table('apply_widthdraw_diamond')
                ->where($where)
                ->orderBy('create_at','desc')
                ->paginate(config('web.page_size'));
            $count = DB::table('apply_widthdraw_diamond')
                ->where($where)
                ->count();
        }
        return view('admin.order.index',['data'=>$data,'count'=>$count,'search_data'=>$search_data,'user'=>$user_info,'reset_order_id'=>self::$reset_order_id]);
    }
    /**
     * 2019-06-28
     * 处理成功订单
     */
    public function disposed(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $receive_id = $request->get('receive_id','');
        $user_type = $request->get('user_type','-1');
        $order_sn  = $request->get('order_sn','');
        $realname    = $request->get('realname','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_sn'=>$order_sn,
            'receive_id'=>$receive_id,
            'realname'=>$realname,
            'user_type'=>$user_type,
        ];
        $admin_user_info = session()->get('user');
        $where = [['state',2]];
        if($search_s){
            $tmp_where = ['sub_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['sub_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }
        if($order_sn){
            $tmp_where = ['order_sn',"like",'%'.$order_sn];
            array_push($where,$tmp_where);
        }
        if($realname){
            $tmp_where = ['realname',"like",'%'.$realname];
            array_push($where,$tmp_where);
        }

        if($receive_id){
            $tmp_where = ['receive_id',$receive_id];
            array_push($where,$tmp_where);
        }else{
            $tmp_where = ['receive_id',$admin_user_info['id']];
            array_push($where,$tmp_where);
        }

        if($user_type>=0){
            $tmp_where = ['widthdraw_type',$user_type];
            array_push($where,$tmp_where);
        }

        $data = DB::table('apply_widthdraw_diamond')
            ->where($where)
            ->orderBy('create_at','desc')
            ->paginate(config('web.page_size'));

        $count = DB::table('apply_widthdraw_diamond')
            ->where([
                ['receive_id',$admin_user_info['id']],
                ['sub_at','>=',strtotime(date('Y-m-d 00:00:00'))]
            ])->count();

        //当日出款总额
        if($search_s && $search_e){
            $trans_info['total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime($search_s."00:00:00")],
                    ['sub_at','<=',strtotime($search_e."23:59:59")],
                ])
                ->sum('amount');
            $trans_info['success_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime($search_s."00:00:00")],
                    ['sub_at','<=',strtotime($search_e."23:59:59")],
                ])
                ->sum('amount');
            $trans_info['bank_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime($search_s."00:00:00")],
                    ['sub_at','<=',strtotime($search_e."23:59:59")],
                    ['bank_name','!=',"支付宝"]
                ])
                ->sum('amount');

            $trans_info['zfb_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime($search_s."00:00:00")],
                    ['sub_at','<=',strtotime($search_e."23:59:59")],
                    ['bank_name',"支付宝"]
                ])
                ->sum('amount');
        }else{
            //默认显示当天出款信息
            $trans_info['total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime(date("Y-m-d 00:00:00"))],
                ])
                ->sum('amount');
            $trans_info['success_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime(date("Y-m-d 00:00:00"))],
                ])
                ->sum('amount');
            $trans_info['bank_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime(date("Y-m-d 00:00:00"))],
                    ['bank_name','!=',"支付宝"]
                ])
                ->sum('amount');

            $trans_info['zfb_total_amount'] = DB::table('apply_widthdraw_diamond')
                ->where([
                    ['state',2],
                    ['receive_id',$admin_user_info['id']],
                    ['sub_at','>=',strtotime(date("Y-m-d 00:00:00"))],
                    ['bank_name',"支付宝"]
                ])
                ->sum('amount');
        }
        return view('admin.order.disposed',['search_data'=>$search_data,'data'=>$data,'count'=>$count,'trans_info'=>$trans_info]);
    }
    /**
     * 2019-06-27
     * 处理失败订单
     */
    public function failed(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $receive_id = $request->get('receive_id','');
        $user_type = $request->get('user_type','-1');
        $order_sn  = $request->get('order_sn','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_sn'=>$order_sn,
            'receive_id'=>$receive_id,
            'user_type'=>$user_type,
        ];
        $admin_user_info = session()->get('user');
        $where = [];
        if($search_s){
            $tmp_where = ['sub_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['sub_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }
        if($order_sn){
            $tmp_where = ['order_sn',"like",'%'.$order_sn];
            array_push($where,$tmp_where);
        }

        if($receive_id){
            $tmp_where = ['receive_id',$receive_id];
            array_push($where,$tmp_where);
        }else{
            $tmp_where = ['receive_id',$admin_user_info['id']];
            array_push($where,$tmp_where);
        }

        if($user_type>=0){
            $tmp_where = ['widthdraw_type',$user_type];
            array_push($where,$tmp_where);
        }

        $data = DB::table('apply_widthdraw_diamond')
            ->where($where)
            ->where(function($query){
                $query->orWhere('state',3)->orWhere('state',5);
            })
            ->orderBy('create_at','desc')
            ->paginate(config('web.page_size'));

        $count = DB::table('apply_widthdraw_diamond')
            ->where([
                ['receive_id',$admin_user_info['id']],
                ['sub_at','>=',strtotime(date('Y-m-d 00:00:00'))]
            ])->where(function($query){
                $query->orWhere('state',3)->orWhere('state',5);
            })
            ->count();
        return view('admin.order.failed',['data'=>$data,'count'=>$count,'search_data'=>$search_data]);
    }
    /**
     * 2019-06-28
     * 支付宝订单记录
     */
    public function alipay_record(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $receive_id = $request->get('receive_id','');
        $state = $request->get('state','-1');
        $order_sn  = $request->get('order_sn','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_sn'=>$order_sn,
            'receive_id'=>$receive_id,
            'state'=>$state,
        ];
        $where = [];
        if($search_s){
            $tmp_where = ['create_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }
        if($order_sn){
            $tmp_where = ['order_sn',"like",'%'.$order_sn];
            array_push($where,$tmp_where);
        }

        if($receive_id){
            $tmp_where = ['receive_id',$receive_id];
            array_push($where,$tmp_where);
        }
        if($state>=0){
            $tmp_where = ['state',$state];
            array_push($where,$tmp_where);
        }
        $zfb_account_info = [];
        if($search_s && $search_e){
            $total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state','!=',6]
                ])->count();
            $succ_total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',0]
                    ])->count();
            $fail_total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',1]
                ])->count();
            $total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state','!=',6]
                ])->sum('draw_money');
            $succ_total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',0]
                ])->sum('draw_money');
            $fail_total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',1]
                ])->sum('draw_money');
            //官方支付宝
            for($i=2;$i<=4;$i++){
                $zfb_account_info['官方'.$i]['zfb_total_count'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime($search_s.' 00:00:00')],
                        ['create_at','<=',strtotime($search_e.' 23:59:59')],
                        ['state','!=',6],
                        ['ali_type',$i]
                    ])->count();
                $zfb_account_info['官方'.$i]['zfb_total_amount'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime($search_s.' 00:00:00')],
                        ['create_at','<=',strtotime($search_e.' 23:59:59')],
                        ['state','!=',6],
                        ['ali_type',$i]
                    ])->sum('draw_money');
                $zfb_account_info['官方'.$i]['zfb_succ_count'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime($search_s.' 00:00:00')],
                        ['create_at','<=',strtotime($search_e.' 23:59:59')],
                        ['state',0],
                        ['ali_type',$i]
                    ])->count();
                $zfb_account_info['官方'.$i]['zfb_succ_amount'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime($search_s.' 00:00:00')],
                        ['create_at','<=',strtotime($search_e.' 23:59:59')],
                        ['state',0],
                        ['ali_type',$i]
                    ])->sum('draw_money');

            }
        }else{
            //默认显示当日数据
            $total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state','!=',6]
                ])->count();
            $succ_total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',0]
                ])->count();
            $fail_total_num = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',1]
                ])->count();
            $total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state','!=',6]
                ])->sum('draw_money');
            $succ_total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',0]
                ])->sum('draw_money');
            $fail_total_amount = DB::table('drawalirecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',1]
                ])->sum('draw_money');
            //官方支付宝
            for($i=2;$i<=4;$i++){
                $zfb_account_info['官方'.$i]['zfb_total_count'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                        ['state','!=',6],
                        ['ali_type',$i]
                    ])->count();
                $zfb_account_info['官方'.$i]['zfb_total_amount'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                        ['state','!=',6],
                        ['ali_type',$i]
                    ])->sum('draw_money');
                $zfb_account_info['官方'.$i]['zfb_succ_count'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                        ['state',0],
                        ['ali_type',$i]
                    ])->count();
                $zfb_account_info['官方'.$i]['zfb_succ_amount'] =  DB::table('drawalirecord')
                    ->where([
                        ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                        ['state',0],
                        ['ali_type',$i]
                    ])->sum('draw_money');

            }
        }
        $data = DB::table('drawalirecord')
            ->where($where)
            ->paginate(config('web.page_size'));
        return view('admin.order.alipay_record', [
            'data'=>$data,
            'zfb_account_info'=>$zfb_account_info,
            'total_num'=>$total_num,
            'succ_total_num'=>$succ_total_num,
            'fail_total_num'=>$fail_total_num,
            'total_amount'=>$total_amount,
            'succ_total_amount'=>$succ_total_amount,
            'fail_total_amount'=>$fail_total_amount,
            'search_data'=>$search_data
            ]);
    }
    /**
     *2019-06-28
     * 银行卡订单记录
     */
    public function bank_record(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $receive_id = $request->get('receive_id','');
        $state = $request->get('state','-1');
        $order_sn  = $request->get('order_sn','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'order_sn'=>$order_sn,
            'receive_id'=>$receive_id,
            'state'=>$state,
        ];
        $where = [];
        if($search_s){
            $tmp_where = ['create_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }
        if($order_sn){
            $tmp_where = ['order_sn',"like",'%'.$order_sn];
            array_push($where,$tmp_where);
        }

        if($receive_id){
            $tmp_where = ['receive_id',$receive_id];
            array_push($where,$tmp_where);
        }
        if($state>=0){
            $tmp_where = ['state',$state];
            array_push($where,$tmp_where);
        }
        if($search_s && $search_e){
            $total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state','!=',6]
                ])->count();
            $succ_total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',0]
                ])->count();
            $fail_total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',1]
                ])->count();
            $total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state','!=',6]
                ])->sum('draw_money');
            $succ_total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',0]
                ])->sum('draw_money');
            $fail_total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime($search_s.' 00:00:00')],
                    ['create_at','<=',strtotime($search_e.' 23:59:59')],
                    ['state',1]
                ])->sum('draw_money');

            //银行卡订单总数
        }else{

            $total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state','!=',6]
                ])->count();
            $succ_total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',0]
                ])->count();
            $fail_total_num = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',1]
                ])->count();
            $total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state','!=',6]
                ])->sum('draw_money');
            $succ_total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',0]
                ])->sum('draw_money');
            $fail_total_amount = DB::table('drawbankrecord')
                ->where([
                    ['create_at','>=',strtotime(date('Y-m-d 00:00:00'))],
                    ['state',1]
                ])->sum('draw_money');
        }
        $data = DB::table('drawbankrecord')
            ->where($where)
            ->paginate(config('web.page_size'));
        return view('admin.order.bank_record',[
            'data'=>$data,
            'search_data'=>$search_data,
            'total_num'=>$total_num,
            'succ_total_num'=>$succ_total_num,
            'fail_total_num'=>$fail_total_num,
            'total_amount'=>$total_amount,
            'succ_total_amount'=>$succ_total_amount,
            'fail_total_amount'=>$fail_total_amount,
        ]);
    }
    /**
     * 2019-06-28
     * 个人出款记录
     */
    public function self_record(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $receive_id = $request->get('receive_id','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'receive_id'=>$receive_id,
        ];
        $where = [];
        if($search_s){
            $tmp_where = ['sub_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['sub_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($receive_id){
            $tmp_where = ['receive_id',$receive_id];
            array_push($where,$tmp_where);
        }
        $data = DB::table('apply_widthdraw_diamond')
            ->selectRaw(
                'sum(case when state=2 then amount else 0 end)as success_amount,
                 sum(case when state=2 and bank_name!="支付宝" then amount else 0 end)as bank_success_amount,
                sum(case when state=2 and bank_name="支付宝" then amount else 0 end)as zfb_success_amount,
                sum(amount) as total_amount,receive_id, FROM_UNIXTIME(sub_at,"%Y-%m-%d") as hand_at')
            ->where($where)
            ->orderBy('hand_at','desc')
            ->groupBy( DB::raw("FROM_UNIXTIME(`sub_at`,'%Y-%m-%d')"),"receive_id")
            ->paginate(config('web.page_size'));
        return view('admin.order.self_record',[
            'data'=>$data,
            'search_data'=>$search_data,
        ]);
    }
    /**
     * 2019-06-27
     * 获取玩家或代理信息
     * @param  user_type 0 玩家  1 代理
     */
    public function get_user_info(Request $request){

        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $user_type = intval($request->get('user_type',''));
        $user_id  = intval($request->get('user_id',''));
        if(in_array($user_type,[1,2])){
            return Response::response(-1,"用户类型错误");
        }
        $user_detail = $this->get_user_detail($user_type,$user_id);
        return Response::response(0,"success",$user_detail);
    }
    protected function get_user_detail($user_type,$user_id){
        $return_info = [];
        $return_info['user_type'] = $user_type;
        $return_info['user_id'] = $user_id;
        if($user_type == 0){
            //玩家详情
            //充值次数 充值总金额
            $return_info['pay_count'] = DB::connection('pay_master')
                ->table('payorder')
                ->where([['rid',$user_id],['state',3]])
                ->count();
            $pay_amount = DB::connection('pay_master')
                ->table('payorder')
                ->where([['rid',$user_id],['state',3]])
                ->sum('price');
            $return_info['pay_amount'] = intval($pay_amount)>0?intval($pay_amount)/100:0;
            //一周充值次数,充值总金额
            $pay_week_count = DB::connection('pay_master')->table('payorder')
                ->where([['rid',$user_id],['state',3],['createtime','>=',strtotime("-7 day")]])
                ->count();
            $pay_week_amount = DB::connection('pay_master')->table('payorder')
                ->where([['rid',$user_id],['state',3],['createtime','>=',strtotime("-7 day")]])
                ->sum('price');
            $return_info['pay_week_count'] = intval($pay_week_count);
            $return_info['pay_week_amount'] =  intval($pay_week_amount)>0?intval($pay_week_amount)/100:0;

            //总提现金额，提现次数
            $widthdraw_count = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['widthdraw_type',0],['state',2]])
                ->count();
            $widthdraw_amount = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['widthdraw_type',0],['state',2]])
                ->sum('amount');
            $return_info['widthdraw_count']= intval($widthdraw_count);
            $return_info['widthdraw_amount'] = intval($widthdraw_amount);

            //一周总提现金额,提现次数
            $widthdraw_week_count = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['widthdraw_type',0],['state',2],['create_at','>=',strtotime("-7 day")]])
                ->count();
            $widthdraw_week_amount = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['widthdraw_type',0],['state',2],['create_at','>=',strtotime("-7 day")]])
                ->sum('amount');
            $return_info['widthdraw_week_count'] = intval($widthdraw_week_count);
            $return_info['widthdraw_week_amount'] = intval($widthdraw_week_amount);

            //最后一次提现时间,金额
            $return_info['last_widthdraw_amount'] = 0;
            $return_info['last_widthdraw_time'] = '';

            $last_widthdraw_amount = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['state',2],['widthdraw_type',0]])
                ->orderBy('create_at', 'desc')
                ->first();
            if($last_widthdraw_amount){
                $return_info['last_widthdraw_amount'] = $last_widthdraw_amount->amount;
                $return_info['last_widthdraw_time'] = date("Y-m-d H:i:s",$last_widthdraw_amount->create_at);
            }

            //查看游戏库玩家信息
            $game_user_info = DB::connection("game")->table('rs_money')->where('rid',$user_id)->first();

            $return_info['current_diamond'] = isset($game_user_info->diamond)?$game_user_info->diamond/100:0;
            $return_info['current_diamond'] += isset($game_user_info->lock_diamond)?$game_user_info->lock_diamond/100:0;
            $return_info['basemoney'] = isset($game_user_info->basemoney)?$game_user_info->basemoney/100:0;
            $return_info['normal_charge'] = isset($game_user_info->normal_charge)?$game_user_info->normal_charge/100:0;
            $return_info['agency_charge'] = isset($game_user_info->agency_charge)?$game_user_info->agency_charge/100:0;
            $return_info['system_charge'] = isset($game_user_info->system_charge)?$game_user_info->system_charge/100:0;
            $return_info['total_win'] = isset($game_user_info->total_win)?$game_user_info->total_win/100:0;
            $return_info['total_lose'] = isset($game_user_info->total_lose)?$game_user_info->total_lose/100:0;
            $return_info['total_withdraw'] = isset($game_user_info->total_withdraw)?$game_user_info->total_withdraw/100:0;
            $return_info['total_servicefee'] = isset($game_user_info->total_servicefee)?$game_user_info->total_servicefee/100:0;

        }else{
            //总提现金额,提现次数
            $widthdraw_count = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['state',2],['widthdraw_type',1]])
                ->count();
            $widthdraw_amount = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['state',2],['widthdraw_type',1]])
                ->sum('amount');
            $return_info['widthdraw_count']= intval($widthdraw_count);
            $return_info['widthdraw_amount'] = intval($widthdraw_amount);
            //最后一次提现时间,金额
            $return_info['last_widthdraw_amount'] = 0;
            $return_info['last_widthdraw_time'] = '';
            $last_widthdraw_amount = DB::table('apply_widthdraw_diamond')
                ->where([['user_id',$user_id],['state',2],['widthdraw_type',1]])
                ->orderBy('create_at', 'desc')
                ->first();
            if($last_widthdraw_amount){
                $return_info['last_widthdraw_amount'] = $last_widthdraw_amount->amount;
                $return_info['last_widthdraw_time'] = date("Y-m-d H:i:s",$last_widthdraw_amount->create_at);
            }
            //当前代理信息
            $agency_info = DB::connection("mysql_web")
                ->table('tg_agency_remaid_info')
                ->where('aid',$user_id)
                ->first();
            $return_info['remaid_total'] = isset($agency_info->remaid_total)?$agency_info->remaid_total:0;
            $return_info['avaliable_widthdraw'] = $return_info['remaid_total']-isset($agency_info->widthdraw)?$agency_info->widthdraw:0;

        }
        return $return_info;
    }

    /**
     * 2019-06-27
     * 待处理订单
     */
    public function wait(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
        ];
        $admin_user_info = session()->get('user');
        $where = [['receive_id',$admin_user_info['id']],['state',1]];
        if($search_s){
            $tmp_where = ['create_at',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_at',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['user_id',intval($user_id)];
            array_push($where,$tmp_where);
        }

        $data = ApplyWidthdraw::where($where)->orderby('id','asc')->paginate(config('web.page_size'));

        $count = ApplyWidthdraw::where($where)->count();
        //是否使用第三方付款
        $bank_use_list = [
            'is_use_ai'=>1,
            'is_use_yzb'=>1,
            'is_use_kk'=>1,
        ];
        //22点-1:30禁止使用第三方付款通道
        if(date("G")>=22 || date("G")<=2){
            $bank_use_list['is_use_ai'] = $bank_use_list['is_use_yzb'] = $bank_use_list['is_use_kk'] = 0;
        }

        //获取默认出款按钮设置
        $redis = Redis::connection();
        $redis->select(12);
        $default_choose = $redis->get("withdraw_menu");

        $menu_choose_info = [
           'ali1'=>0,
           'ali2'=>0,
           'ali3'=>0,
           'bank_yzb'=>0,
           'bank_kk'=>0,
           'bank_ai'=>0,
        ];
        if($default_choose){
            $menu_list = json_decode($default_choose,true);
            foreach ($menu_list as $k=>$v){
                if($v['is_choose']==1){
                    $menu_choose_info[$v['name']] = 1;
                }
            }
        }
        return view('admin.order.wait',[
            'data'=>$data,
            'menu_info'=>$menu_choose_info,
            'bank_use_list'=>$bank_use_list,
            'count'=>$count,
            'search_data'=>$search_data,
            'user'=>$admin_user_info,
            'bank_name'=>'支付宝',
        ]);
    }
    /**
     * 2019-06-27
     * 退回订单 玩家退回游戏账户,代理退回代理后台
     *
     */
    public function return_order(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $remark = $request->get('return_reason','');

        if(!$remark){
            return Response::response(-1,"退款原因不能为空");
        }
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();

        if(!$order_info){
            return Response::response(-1,"订单已处理");
        }
        if($order_info->state != 1){
            return Response::response(-1,"订单已处理");
        }

        if($order_info['is_draw']==1){
            $state=5;
        }else{
            $state=3;
        }

        if($this->setOrderState($order_info->order_sn,1) === false){
            return Response::response(-1,"订单已处理,请联系后台管理人员");
        }
        $order_info->update_at = time();
        $order_info->state = $state;
        $order_info->remark = $remark;
        if(!$order_info->save()){
            //如果失败则 清除订单状态
            $this->clearOrderState($order_info->order_sn);
            return Response::response(-1,"处理失败,请稍后再试");
        }
        //回退金额操作
        if($order_info->widthdraw_type==0){
            //退回到游戏
            $this->returnToGm($order_info,3,$remark);
        }else{
            //退回到代理用户
            $order_record_info = DB::connection('mysql_web')
                ->table('tg_widthdraw_record')
                ->where([
                    ['aid',$order_info->user_id],
                    ['order_num',$order_info->order_sn]
                ])
                ->first();
            if($order_record_info && $order_record_info->is_draw==0){
                $this->mod_widthdraw_state($order_info,3);
                DB::connection('mysql_web')
                    ->table('tg_widthdraw_record')
                    ->where([
                        ['aid',$order_info->user_id],
                        ['order_num',$order_info->order_sn]
                    ])
                    ->update(['is_draw'=>1]);
                $order_info->is_draw = 1;
                $order_info->save();
            }else{
                $this->write_order_state_log($order_info,$state);
            }
        }

        return Response::response(0,"处理成功");
    }
    /**
     * 2019-06-27
     * 退回订单到系统,其他人可以继续接单处理
     */
    public function  return_system(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();
        if(!$order_info){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }
        if($order_info->state != 1){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }

        //修改订单为初始状态
        $order_info->update_at = time();
        $order_info->receive_id = 0;
        $order_info->state = 0;
        if(!$order_info->save()){
            return Response::response(-1,"退回失败,请稍后再试");
        }
        if($order_info->widthdraw_type == 0){
            $this->returnToGm($order_info,0,"退回订单");
        }
        $this->write_order_state_log($order_info,0,"退回订单");

        $redis = Redis::connection();
        $redis->select(1);
        $redis->del($order_info->order_sn);

        return Response::response(0,"处理成功");

    }
    /**
     * 2019-06-27
     * 支付宝手工出款
     */
    public function transfer_hand_zfb(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $admin_user_info = session()->get('user');
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();

        if(!$order_info){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }

        if($order_info->state != 1){
            return Response::response(-1,"订单已处理过");
        }

        if($this->setOrderState($order_info->order_sn,1)===false){
            return Response::response(-1,"订单已处理过");
        }

        $order_info->state = 2;
        $order_info->receive_id = $admin_user_info['id'];
        $order_info->mer_type = 1;
        $order_info->remark = "手工转款";
        $order_info->sub_at = time();
        $order_info->is_hand = 1;
        if($order_info->save()){
            //支付宝出款记录
            $add_data = [
                'user_id'=>$user_id,
                'draw_money'=>$order_info->amount,
                'order_sn'=>$order_info->order_sn,
                'state'=>0,
                'create_at'=>time(),
                'operator_id'=>$admin_user_info['id'],
                'user_type'=>$order_info->widthdraw_type,
                'ali_account'=>$order_info->bank_account,
                'realname'=>$order_info->realname,
                'ali_type'=>1,
                'remark'=>"手工转款"
            ];
            DB::table('drawalirecord')->insert($add_data);
            if($order_info->widthdraw_type==1){
                $this->mod_widthdraw_state($order_info,2);
            }else{
                $this->returnToGm($order_info,2);
            }
            return Response::response(0,"处理成功");
        }else{
            $this->clearOrderState($order_info->order_sn);
            return Response::response(-1,"处理失败");
        }

    }

    /**
     * 2019-06-27
     * 设置订单状态
     */
    protected function setOrderState($order_sn,$type){
        $redis = Redis::connection();
        $redis->select(2);
        if($redis->setnx($order_sn,$type)){
            $redis->expireat($order_sn,time()+self::$expire_time);
            return true;
        }
        return false;
    }
    /**
     * 2019-06-27
     * 清除订单状态
     */
    protected function clearOrderState($order_sn){
        $redis = Redis::connection();
        $redis->select(2);
        $redis->del($order_sn);
    }

    public function transfer_hand_bank(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $admin_user_info = session()->get('user');
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();

        if(!$order_info){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }

        if($order_info->state != 1){
            return Response::response(-1,"订单已处理过");
        }

        if($this->setOrderState($order_info->order_sn,1)===false){
            return Response::response(-1,"订单已处理过");
        }

        $order_info->state = 2;
        $order_info->receive_id = $admin_user_info['id'];
        $order_info->mer_type = 1;
        $order_info->remark = "手工转款";
        $order_info->sub_at = time();
        $order_info->is_hand = 1;
        if($order_info->save()){
            //支付宝出款记录
            $add_data = [
                'user_id'=>$user_id,
                'draw_money'=>$order_info->amount,
                'order_sn'=>$order_info->order_sn,
                'state'=>0,
                'create_at'=>time(),
                'operator_id'=>$admin_user_info['id'],
                'user_type'=>$order_info->widthdraw_type,
                'realname'=>$order_info->realname,
                'bank_account'=>$order_info->bank_account,
                'bank_name'=>$order_info->bank_name,
                'remark'=>"手工出款",
                'mer_type'=>1,
                'return_res'=>''
            ];
            DB::table('drawbankrecord')->insert($add_data);
            if($order_info->widthdraw_type==1){
                $this->mod_widthdraw_state($order_info,2);
            }else{
                $this->returnToGm($order_info,2);
            }
            return Response::response(0,"处理成功");
        }else{
            $this->clearOrderState($order_info->order_sn);
            return Response::response(-1,"处理失败");
        }
    }
    /**
     * 2019-06-27
     * 支付宝自动出款
     */
    public function transfer_zfb_auto(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $zfb_num  = intval($request->get('zfb_num',''));

        $admin_user_info = session()->get('user');
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();

        if(!$order_info){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }

        if($order_info->state != 1){
            return Response::response(-1,"订单已处理过");
        }

        if($this->setOrderState($order_info->order_sn,1)===false){
            return Response::response(-1,"订单已处理过");
        }
        //支付宝转款
        $trans_res = $this->ali_widthdraw($order_info,$zfb_num);

        //记录订单出款日志
        $this->pay_log('zfb',$order_info,$trans_res);
        if($trans_res['state']===true){
            $order_info->state = 2;
            $order_info->receive_id = $admin_user_info['id'];
            $order_info->mer_type = $zfb_num;
            $order_info->remark = config('web.zfb_num')[$zfb_num];
            $order_info->sub_at = time();
            $order_info->is_hand = 0;
            $order_info->save();
            //支付宝出款记录
            $add_data = [
                'user_id'=>$user_id,
                'draw_money'=>$order_info->amount,
                'order_sn'=>$order_info->order_sn,
                'state'=>0,
                'create_at'=>time(),
                'operator_id'=>$admin_user_info['id'],
                'user_type'=>$order_info->widthdraw_type,
                'ali_account'=>$order_info->bank_account,
                'realname'=>$order_info->realname,
                'ali_type'=>$zfb_num,
                'remark'=>config('web.zfb_num')[$zfb_num],
            ];
            DB::table('drawalirecord')->insert($add_data);
            if($order_info->widthdraw_type==1){
                $this->mod_widthdraw_state($order_info,2);
            }else{
                $this->returnToGm($order_info,2);
            }
            return Response::response(0,"处理成功");

        }else{
            $this->clearOrderState($order_info->order_sn);
            return Response::response(-1,"处理失败:".$trans_res['msg']);
        }
    }

    /**
     * 2019-06-28
     * 支付宝官方转款
     */
    protected function ali_widthdraw($order_info,$zfb_num){
        $alipay = new AlipayService(config('web.zfb_config.app_id_'.$zfb_num),config('web.zfb_config.private_key'));
        $result = $alipay->doPay($order_info->amount,$order_info->order_sn,$order_info->bank_account,$order_info->realname,"转账");
        $result = $result['alipay_fund_trans_toaccount_transfer_response'];
        $return_arr['state'] = false;
        if($result['code'] && $result['code']=='10000'){
            $return_arr['state'] = true;
            $return_arr['msg'] = $result['sub_msg'];
            return $return_arr;
        }else{
            $return_arr['msg'] =  isset($result['sub_msg'])?$result['sub_msg']:'';
            $return_arr['sub_code'] =  isset($result['sub_code'])?$result['sub_code']:'';
            return $return_arr;
        }
    }
    /**
     * 2019-06-27
     * 银行卡自动出款
     */
    public function transfer_bank_auto(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));
        $bank_num  = intval($request->get('bank_num',''));

        $admin_user_info = session()->get('user');
        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();

        if(!$order_info){
            return Response::response(-1,"订单已处理,如有疑问请联系后台");
        }


        if($order_info->state != 1){
            return Response::response(-1,"订单已处理过");
        }

        if($this->setOrderState($order_info->order_sn,1)===false){
            return Response::response(-1,"订单已处理过");
        }
        if($order_info->bank_name == "支付宝"){
            return Response::response(-1,"只能出款银行卡订单");
        }
        if($bank_num == 2){
            $trans_res = $this->yzb_widthdraw($order_info);
            //记录订单出款日志
            $this->pay_log('bank_yzb',$order_info,$trans_res);
        }else if($bank_num == 3 ){
            $bank_list = config('web.kkfu_bink_list');
            $bank_code = isset($bank_list[$order_info->bank_name])?$bank_list[$order_info['bank_name']]:'';
            if(!$bank_code){
                return Response::response(-1,"KK付不支持该银行");
            }
            $trans_res = $this->kkbank_widthdraw($order_info,$bank_code);
            //记录订单出款日志
            $this->pay_log('bank_kk',$order_info,$trans_res);
        }else if($bank_num == 4 ){
            $bank_list = config('web.aifu_bink_list');
            $bank_code = isset($bank_list[$order_info->bank_name])?$bank_list[$order_info['bank_name']]:'';
            if(!$bank_code){
                return Response::response(-1,"艾付不支持该银行");
            }
            $trans_res = $this->aibank_widthdraw($order_info,$bank_code);
            //记录订单出款日志
            $this->pay_log('bank_ai',$order_info,$trans_res);
        }


        if($trans_res['state']===true){
            $order_info->state = 6;
            $order_info->receive_id = $admin_user_info['id'];
            $order_info->mer_type = intval($bank_num);
            $order_info->remark = config('web.bank_num')[$bank_num];
            $order_info->sub_at = time();
            $order_info->is_hand = 0;
            $order_info->save();
            //支付宝出款记录
            $add_data = [
                'user_id'=>$user_id,
                'draw_money'=>$order_info->amount,
                'order_sn'=>$order_info->order_sn,
                'state'=>0,
                'create_at'=>time(),
                'operator_id'=>$admin_user_info['id'],
                'user_type'=>$order_info->widthdraw_type,
                'realname'=>$order_info->realname,
                'bank_account'=>$order_info->bank_account,
                'bank_name'=>$order_info->bank_name,
                'remark'=>config('web.bank_num')[$bank_num],
                'mer_type'=>$bank_num,
                'return_res'=>'',
                't_order_sn'=>$trans_res['order_num'],
            ];

            DB::table('drawbankrecord')->insert($add_data);
            if($order_info->widthdraw_type==1){
                $this->mod_widthdraw_state($order_info,6);
            }else{
                $this->returnToGm($order_info,6);
            }

            return Response::response(0,"处理成功");

        }else{
            //如果下单直接返回失败,则退款给用户
            $add_data['state'] = 1;
            $remark = "出款系统繁忙";
            $order_info->state = 3;
            $order_info->receive_id = $admin_user_info['id'];
            $order_info->mer_type = intval($bank_num);
            $order_info->remark = $remark;
            $order_info->sub_at = time();
            $order_info->update_at = time();
            $order_info->is_hand = 0;
            $order_info->is_draw = 1;
            $order_info->save();

            if($order_info->widthdraw_type==1){
                $this->mod_widthdraw_state($order_info,3,$remark);
            }else{
                $this->returnToGm($order_info,3,$remark);
            }

            DB::table('drawbankrecord')->insert($add_data);
            return Response::response(-1,"处理失败:已退款给用户");
        }
    }

    //易支宝出款 todo
    protected function yzb_widthdraw($order_info){

    }

    //kkbank出款 todo
    protected function kkbank_widthdraw($order_info,$bank_code){

    }

    //艾付出款
    protected function aibank_widthdraw($order_info,$bank_code){
        $return_arr['state'] = false;

        //生成符合的订单号
        $order_arr = explode('_',$order_info->order_sn);
        $order_str = $order_arr[0].'_'.$order_arr[1].'_'.$order_arr[count($order_arr)-1];
        $order_str= str_replace('_','A',$order_str);

        $request_arr = [
            'merchant_no' =>config('web.ai_app_id'),
            'order_no' =>$order_str,
            'card_no' =>$order_info->bank_account,
            'account_name' => base64_encode($order_info->realname),
            'bank_branch' => '',
            'cnaps_no' => '',
            'bank_code' => $bank_code,
            'bank_name' =>  base64_encode($order_info->bank_name),
            'amount' =>number_format($order_info['amount'],'2','.',''),
        ];

        $sign_str  = "merchant_no=".$request_arr['merchant_no']."&order_no=".$request_arr['order_no'];
        $sign_str .= "&card_no=".$request_arr['card_no']."&account_name=".$request_arr['account_name'];
        $sign_str .= "&bank_branch=".$request_arr['bank_branch']."&cnaps_no=".$request_arr['cnaps_no'] ;
        $sign_str .= "&bank_code=".$request_arr['bank_code']."&bank_name=".$request_arr['bank_name'];
        $sign_str .= "&amount=".$request_arr['amount']."&pay_pwd=".C('ai_pay_key');
        $sign_str .= '&key='.config('web.ai_app_key');
        $request_arr['sign'] = md5($sign_str);
        $res =  $this->ai_curl(config('web.ai_pay_url'),$request_arr);
        if(!$res){
            $retrun_data['msg'] = '下单失败';
            return $return_arr;
        }
        if(isset($res['result_code']) && $res['result_code']=='000000'){
            $return_arr['order_num'] = $res['order_no'];
            $return_arr['state'] = 'success';
            return $return_arr;
        }else{
            $return_arr['msg'] = "下单失败";
        }
        return $return_arr;
    }

    protected function ai_curl($url,$request_data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( $request_data ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        $response = curl_exec($curl);
        curl_close($curl);
        if($response){
            return json_decode($response, true);
        }
        return false;
    }

    //记录出款日志
    protected function pay_log($dir,$order_info,$res){
        $log_dir = './Log/'.$dir;
        if(!file_exists($log_dir)){
            mkdir($log_dir,0777,true);
        }
        $fp = fopen($log_dir.'/'.date('Y-m-d').'.txt',"a+");
        fputs($fp,date('Y-m-d H:i:s').','.$order_info->user_id.",".$order_info->order_sn.",".$res['state'].",".$res['msg']."\r\n");
        fclose($fp);
    }

    /**
     * 2019-06-27
     * 查询AI付出款结果
     */
    public function query_aifu_bank_state(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));

        $order_info = ApplyWidthdraw::where([['id',$order_id],['user_id',$user_id]])->first();
        if(!$order_info){
            return Response::response(-1,"订单不存在");
        }
        $res = $this->query_aifu_bank_order($order_info->order_sn);
        if(isset($res['result_code']) && $res['result_code']=='000000'){
            $return_arr['code'] = 0;
            if($res['result']=='S'){
                $return_arr['msg'] = "出款成功";
            }else if($res['result']=='F'){
                $return_arr['msg'] = "出款失败";
            }else{
                $return_arr['msg'] = "等待出款";
            }
            $return_arr['order_fee'] = $order_info->draw_money;
            $return_arr['pay_date'] = date("Y-m-d",$order_info->sub_at);
            echo json_encode($return_arr);exit();
        }
        $return_arr['msg'] = '支付订单不存在';
        return Response::response(0,$return_arr['msg'],$return_arr);
    }

    protected function query_aifu_bank_order($order_sn){
        $order_arr = explode('_',$order_sn);
        $order_str = $order_arr[0].'_'.$order_arr[1].'_'.$order_arr[count($order_arr)-1];
        $order_str= str_replace('_','A',$order_str);
        $merchant_no = config('web.ai_app_id');
        $merchant_key = config('web.ai_app_key');
        $sign_str = "merchant_no=" . $merchant_no . "&order_no=" . $order_str . "&key=" . $merchant_key;
        $request_data = [
            'merchant_no'=>$merchant_no,
            'order_no'=>$order_str,
            'sign'=>md5($sign_str),
        ];
        $res = $this->ai_curl(config('web.ai_query_url'),$request_data);
        return $res;
    }

    /**
     * 2019-06-27
     * 支付宝订单查询结果
     */
    public function query_ali_order(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $order_id = intval($request->get('order_id',''));
        $user_id  = intval($request->get('user_id',''));

        $order_info = DB::table('drawalirecord')
            ->where([['id',$order_id],['user_id',$user_id]])
            ->first();
        if(!$order_info){
            return Response::response(-1,"订单不存在");
        }
        $res = $this->query_ali_order($order_info);

        if($res['state']==1){
            $return_arr['code'] = 0;
            $return_arr['msg'] = $res['msg'];
            $return_arr['order_fee'] = $res['order_fee'];
            $return_arr['pay_date'] = $res['pay_date'];
            return Response::response(0,$res['msg'],$return_arr);
        }else{
            Response::response(-1,"支付宝支付订单不存在");
        }
    }
    protected function query_alid_order_act($order_info){
        $result['state'] = false;
        $aliPay = new AlipayService(config('web.zfb_config.app_id_'.$order_info->mer_type),config('web.zfb_config.private_key'));
        $result = $aliPay->doQuery($order_info['order_sn']);
        $result = $result['alipay_fund_trans_order_query_response'];
        if($result['code']==10000){
            if($result['status']=='SUCCESS'){
                $result['state'] = 1;
            }
            return $result;
        }else{
            $result['msg'] = "支付宝支付订单不存在";
            return $result;
        }
    }



 public function test(){
        echo 12321;die;
 }


}

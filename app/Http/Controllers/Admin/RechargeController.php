<?php

namespace App\Http\Controllers\Admin;

use App\Http\Models\Recharge;
use App\Http\Models\RechargeRecord;
use App\Library\Response;
use Common\Controller\MyRedis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RechargeController extends Controller
{
    //VIP充值接口
    /**
     * 2019-07-01
     * VIP充值接口
     */
    public function index(){
        $admin_user = session()->get('user');
        $account_info = Recharge::where('user_id',$admin_user['id'])->first();

        if($account_info){
            $account_info = [
                'amount'=>$account_info->amount,
                'widthdraw'=>$account_info->widthdraw,
            ];
        }else{
            $account_info = [
                'amount'=>0,
                'widthdraw'=>0,
            ];
        }
        return view('admin.recharge.index',['account_info'=>$account_info]);
    }
    public function store(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $user_id = intval($request->get('user_id',''));
        $diamond  = intval($request->get('diamond',''));
        $game_user_info = DB::connection("game")->table('rs_user')->where('rid',$user_id)->first();
        if(!$game_user_info){
            return Response::response(-1,"用户不存在,请检查用户ID是否正确");
        }
        $admin_user = session()->get('user');
        $account_info = Recharge::where('user_id',$admin_user['id'])->first();

        if(!$account_info){
            return Response::response(-1,"账户余额不足");
        }

        if($account_info->amount-$account_info->widthdraw<$diamond){
            return Response::response(-1,"账户余额不足");
        }

        $order_sn = date('mdHis_'.$user_id.'_'.$diamond.'_'. mt_rand(100000, 999999));

        $account_info->widthdraw = ($account_info->widthdraw+$diamond);
        $account_info->save();
        $this->recharge_diamond($diamond,$user_id,$order_sn,config('web.web2gm_salt'),$admin_user);
        return Response::response(0,"充值成功");
    }
    /**
     * 2019-06-13
     * 新增赠送接口
     * 赠送状态   Egivemoney_state_wait_draw=1:待领取 Egivemoney_state_had_draw=2:已领取
     *  Egivemoney_state_had_undo=3:已撤销
     * 赠送类型 Egivemoney_type_player_giving=1:玩家赠送 Egivemoney_type_vip_charge=2:vip充值
     *  Egivemoney_type_gm_recharge=3:gm补单  Egivemoney_type_gm_award=4:客服奖励
     */
    protected function recharge_diamond($diamond,$user_id,$order_sn,$salt,$admin_user){
        $msgname = 'givemoney';
        $tokentime = time();
        $sign = md5($msgname.$order_sn.$tokentime.$salt).$tokentime;
        $push_data = array(
            'msghead' => array("msgname"=>$msgname),
            'msgbody' => array(
                "rid"=>$admin_user['id'],
                "recvrid"=>$user_id,
                "give_type"=>2,
                "give_value"=>intval($diamond*100),
                "comment"=>array(
                    "tradeid"=>$order_sn,
                    "sign"=>$sign),
                "remark"=>"VIP充值"
            )
        );
        $redis = Redis::connection('game_redis');
        $redis->select(15);
        $redis->rPush('inchargemq',json_encode($push_data));

    }
    /**
     * 2019-07-01
     * 取消赠送接口
     */
    public function cancel_give_money(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $receive_user_info = session()->get('user');
        $keyid = $request->get('keyid','');
        $remark = $request->get('remark','');
        $order_info = DB::connection('game')
            ->table('rm_giverecord')
            ->where('keyid',$keyid)
            ->first();
        if(!$order_info){
            return Response::response(-1,'订单无法撤销');
        }
        if($order_info->current_state==1){

            $msgname = 'cancelgivemoney';
            $push_data = array(
                'msghead' => array("msgname"=>$msgname),
                'msgbody' => array(
                    'keyid'=>$keyid,
                    'remark'=>$remark,
                    'undorid'=>$receive_user_info['id']
                )
            );
            $redis = Redis::connection('game_redis');
            $redis->select(15);
            $redis->rPush('inchargemq',json_encode($push_data));
            return Response::response(0,'请稍后查看撤销结果');

        }else if($order_info->current_state==2){
            return Response::response(-1,'无法撤销,用户已领取');
        }else if($order_info->current_state==3){
            return Response::response(0,'已撤销');
        }else{
            return Response::response(-1,'其他状态,请联系管理员');
        }

    }
    /**
     * 2019-07-01
     * VIP充值记录
     */
    public function recharge_record(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $state = $request->get('state','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'state'=>$state,
        ];
        $where = [['give_type',2]];
        if($search_s){
            $tmp_where = ['create_time',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_time',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['recvrid',$user_id];
            array_push($where,$tmp_where);
        }
        if($state){
            $tmp_where = ['current_state',$state];
            array_push($where,$tmp_where);
        }
        $count = DB::connection('game')->table('rm_giverecord')
            ->where($where)
            ->count();
        $data = DB::connection('game')->table('rm_giverecord')
            ->where($where)
            ->orderBy('create_time','desc')
            ->paginate(config('web.page_size'));
        if($user_id){
            $today_recharge_money = DB::connection('game')->table('rm_giverecord')
                ->where([
                    ['create_time','>=',strtotime(date("Y-m-d 00:00:00"))],
                    ['give_type',2],
                    ['recvrid',$user_id]
                ])
                ->whereIn('current_state',[1,2])
                ->sum('give_value');
            $total_recharge_money = DB::connection('game')->table('rm_giverecord')
                ->where([
                    ['give_type',2],
                    ['recvrid',$user_id]
                ])
                ->whereIn('current_state',[1,2])
                ->sum('give_value');
        }else{
            $today_recharge_money = DB::connection('game')->table('rm_giverecord')
                ->where([
                    ['create_time','>=',strtotime(date("Y-m-d 00:00:00"))],
                    ['give_type',2]
                ])
                ->whereIn('current_state',[1,2])
                ->sum('give_value');
            $total_recharge_money = DB::connection('game')->table('rm_giverecord')
                ->where([
                    ['give_type',2]
                ])
                ->whereIn('current_state',[1,2])
                ->sum('give_value');
        }

        return view('admin.recharge.recharge_record',
            [
                'search_data'=>$search_data,
                'data'=>$data,
                'count'=>$count,
                'today_recharge_money'=>$today_recharge_money,
                'total_recharge_money'=>$total_recharge_money,
            ]);
    }


    /**************************************2019-08-14 老版本VIP充值***************************************/
    //充值
    public function game_recharge(Request $request){
        $admin_user = session()->get('user');
        $account_info = Recharge::where('user_id',$admin_user['id'])->first();

        if($account_info){
            $account_info = [
                'amount'=>$account_info->amount,
                'widthdraw'=>$account_info->widthdraw,
            ];
        }else{
            $account_info = [
                'amount'=>0,
                'widthdraw'=>0,
            ];
        }
        return view('admin.recharge.oldrecharge',['account_info'=>$account_info]);
    }
    public function oldstore(Request $request){
        if(!$request->ajax() ||$request->getMethod() != 'POST'){
            return Response::response(-1,"非法请求");
        }
        $user_id = intval($request->get('user_id',''));
        $diamond  = intval($request->get('diamond',''));
        $game_user_info = DB::connection("game")->table('rs_user')->where('rid',$user_id)->first();
        if(!$game_user_info){
            return Response::response(-1,"用户不存在,请检查用户ID是否正确");
        }
        $admin_user = session()->get('user');
        $account_info = Recharge::where('user_id',$admin_user['id'])->first();

        if(!$account_info){
            return Response::response(-1,"账户余额不足");
        }

        if($account_info->amount-$account_info->widthdraw<$diamond){
            return Response::response(-1,"账户余额不足");
        }

        $order_sn = date('mdHis_'.$user_id.'_'.$diamond.'_'. mt_rand(100000, 999999));

        $account_info->widthdraw = ($account_info->widthdraw+$diamond);
        $account_info->save();
        $save_data = array(
            'user_id'=>$user_id,
            'send_id'=>$admin_user['id'],
            'diamond'=>$diamond,
            'create_at'=>time(),
            'order_sn'=>$order_sn
        );
        $recharge_record = new RechargeRecord();
        $recharge_record->save($save_data);
        $this->old_recharge_diamond($diamond,$user_id,$order_sn,config('web.web2gm_salt'));
        return Response::response(0,"充值成功");
    }
    protected function old_recharge_diamond($diamond,$user_id,$order_sn,$salt){
        $msgname = 'charge_diamond';
        $tokentime = time();
        $sign = md5($msgname.$order_sn.$tokentime.$salt).$tokentime;
        $push_data = array(
            'msghead' => array("msgname"=>$msgname),
            'msgbody' => array(
                "charge_type"=>intval(1),
                "rid"=>$user_id,
                "value"=>intval($diamond*100),
                "comment"=>array(
                    "tradeid"=>$order_sn,
                    "sign"=>$sign),
                "recharge_money"=>intval($diamond*100)
            )
        );
        $redis = Redis::connection('game_redis');
        $redis->select(15);
        $redis->rPush('inchargemq',json_encode($push_data));
    }
    //充值记录
    public function old_recharge_record(Request $request){
        $search_s = $request->get('search_s','');
        $search_e = $request->get('search_e','');
        $user_id  = $request->get('user_id','');
        $send_id = $request->get('send_id','');
        $search_data = [
            'search_s'=>$search_s,
            'search_e'=>$search_e,
            'user_id'=>$user_id,
            'send_id'=>$send_id,
        ];
        $where = [];
        if($search_s){
            $tmp_where = ['create_time',">=",$search_s];
            array_push($where,$tmp_where);
        }
        if($search_e){
            $tmp_where = ['create_time',"<=",$search_e];
            array_push($where,$tmp_where);
        }
        if($user_id){
            $tmp_where = ['recvrid',$user_id];
            array_push($where,$tmp_where);
        }
        if($send_id){
            $tmp_where = ['send_id',$send_id];
            array_push($where,$tmp_where);
        }
        $count = RechargeRecord::where($where)
            ->count();
        $data =RechargeRecord::where($where)
            ->orderBy('create_time','desc')
            ->paginate(config('web.page_size'));
        if($user_id){
            $today_recharge_money =  RechargeRecord::where([
                    ['create_at','>=',strtotime(date("Y-m-d 00:00:00"))],
                ])
                ->sum('diamond');
            $total_recharge_money =  RechargeRecord::sum('diamond');
        }else{
            $today_recharge_money =  RechargeRecord::where([
                ['create_at','>=',strtotime(date("Y-m-d 00:00:00"))],
            ])
                ->sum('diamond');
            $total_recharge_money =  RechargeRecord::sum('diamond');
        }

        return view('admin.recharge.old_recharge_record',
            [
                'search_data'=>$search_data,
                'data'=>$data,
                'count'=>$count,
                'today_recharge_money'=>$today_recharge_money,
                'total_recharge_money'=>$total_recharge_money,
            ]);
    }
    //扣除金币
    public function reduce_money(Request $request){

    }
    //扣除金币记录
    public function reduce_record(Request $request){

    }

    /**************************************2019-08-14 老版本VIP充值***************************************/

}

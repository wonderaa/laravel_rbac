@extends('layouts.admin')

@section('style')
    <style>
        #page li {
            display: inline-block;
        }
        #page .active span {
            background-color: #009688;
            color: #fff;
            border: 0px;
            height: 30px;
            border-radius: 2px;
        }

        #page .disabled span {
            color: #ccc;
        }
    </style>
@endsection

@section('content')
    <table class="layui-table">
        <tr>
            <td>本日订单共有数据:{{ $count }}</td>
            <td>本日出款总额:{{ $trans_info['total_amount'] }}</td>
            <td>本日成功出款总额:{{ $trans_info['success_total_amount'] }}</td>
            <td>本日成功出款总额(银行卡):{{ $trans_info['bank_total_amount'] }}</td>
            <td>本日成功出款总额(支付宝):{{ $trans_info['zfb_total_amount'] }}</td>
        </tr>
    </table>
    <form id="search_form">
        <table class="layui-table">
            <tr>
                <td><input  name="search_s" placeholder="操作时间"  value="{{$search_data['search_s']}}" class="layui-input" id="search_s"></td>
                <td><input  name="search_e" placeholder="操作时间"  value="{{$search_data['search_e']}}" class="layui-input" id="search_e"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['user_id']}}" placeholder="游戏ID" name="user_id"></td>
                <td><input type="text" class="layui-input" value="{{$search_data['realname']}}" placeholder="收款人" name="realname"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['order_sn']}}" placeholder="订单ID" name="order_sn"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['receive_id']}}" placeholder="处理ID" name="receive_idorder_sn"></td>
                <td>
                    <select name='user_type' class="layui-select" style="float:left;width:150px;" >
                        <option value='-1' @if($search_data['user_type']==-1) selected @endif>请选择</option>
                        <option value='0'  @if($search_data['user_type']==0) selected @endif>玩家</option>
                        <option value='1'  @if($search_data['user_type']==1) selected @endif>代理</option>
                    </select>
                </td>
                <td>
                     <span class="input-group-btn">
                            <button type="submit" class="layui-btn">搜索</button>
                     </span>
                </td>
            </tr>
        </table>
    </form>

    <table class="layui-table">
        <colgroup>
            <col width="50">
        </colgroup>
        <thead>
        <tr>
            <th colspan="12">全部订单<span class="r">共有数据：<strong>{{$count}}</strong> 条</span> </th>
        </tr>
        <tr>
            <th width="100">游戏ID</th>
            <th width="100">用户类型</th>
            <th width="50">申请时间</th>
            <th width="50">接单时间</th>
            <th width="100">处理时间</th>
            <th width="100">处理状态</th>
            <th width="100">转账金额</th>
            <th width="100">订单号</th>
            <th width="100">转账方式</th>
            <th width="100">收款账号</th>
            <th width="100">收款人</th>
            <th width="100">处理账号</th>
            <th width="100">备注</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{ $val->user_id }}</td>

                            <td>
                                @if($val->widthdraw_type == '1')
                                    <span style="color: #985f0d">代理</span>
                                @else
                                    玩家
                                @endif
                            </td>

                            <td>{{ date("Y-m-d H:i:s",$val->create_at) }}</td>
                            <td>{{ date("Y-m-d H:i:s",$val->receive_at) }}</td>
                            <td>{{ date("Y-m-d H:i:s",$val->sub_at) }}</td>
                            <td>
                                @if($val->state == 0)
                                    等待接单
                                @elseif($val->state == 1)
                                    处理中
                                @elseif($val->state == 2)
                                    已发放
                                @elseif($val->state == 3 || $val->state == 5)
                                    已退回
                                @elseif($val->state == 4)
                                    已拒绝
                                @elseif($val->state == 6)
                                    处理中
                                @endif
                            </td>
                            <td>{{$val->amount}}</td>
                            <td title="{{$val->order_sn}}">{{ substr($val->order_sn,-6,6) }}</td>

                            <td>{{$val->bank_name}}</td>
                            <td> {{ $val->bank_account }}</td>
                            <td>{{$val->realname}}</td>

                            <td>{{$val->receive_id}}</td>
                            <td>{{$val->remark}}</td>
                        </tr>
                   @endforeach
            @endif
        </tbody>
    </table>
    <div id="page" class="layui-box layui-laypage layui-laypage-default">{{ $data->links() }}</div>

@endsection

@section('script')
    <script>
        layui.use('laydate', function(){
            var laydate = layui.laydate;
            laydate.render({
                elem: '#search_s'
            });
            laydate.render({
                elem: '#search_e'
            });
        });

        layui.use(['layer'], function () {
            var layer = layui.layer;
        });

    </script>
@endsection
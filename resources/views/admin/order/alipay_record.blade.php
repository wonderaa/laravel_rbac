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
    <form id="search_form">
        <table class="layui-table">
            <tr>
                <td><input  name="search_s" placeholder="操作时间"  value="{{$search_data['search_s']}}" class="layui-input" id="search_s"></td>
                <td><input  name="search_e" placeholder="操作时间"  value="{{$search_data['search_e']}}" class="layui-input" id="search_e"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['user_id']}}" placeholder="游戏ID" name="user_id"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['order_sn']}}" placeholder="订单ID" name="order_sn"></td>
                <td>
                    <select name='state' class="layui-select" style="float:left;width:150px;" >
                        <option value='-1' @if($search_data['state']==-1) selected @endif>请选择</option>
                        <option value='0'  @if($search_data['state']==0) selected @endif>成功</option>
                        <option value='1'  @if($search_data['state']==1) selected @endif>失败</option>
                        <option value='6'  @if($search_data['state']==6) selected @endif>下发中</option>
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
            <td colspan="2">当日订单总数:{{$total_num}}</td>
            <td colspan="2">当日订单总额:{{$total_amount}}</td>
            <td colspan="2">当日成功订单总数:{{$succ_total_num}}</td>
            <td colspan="2">当日成功订单总额:{{$succ_total_amount}}</td>
            <td colspan="2">当日失败订单总数:{{$fail_total_num}}</td>
            <td colspan="2">当日失败订单总额:{{$fail_total_amount}}</td>
        </tr>

        @foreach($zfb_account_info as $k=>$v)
            <tr>
                <td colspan="2">[{{$k}}]当日订单总数:{{$v['zfb_total_count']}}</td>
                <td colspan="2">[{{$k}}]当日订单总额:{{$v['zfb_total_amount']}}</td>
                <td colspan="2">[{{$k}}]当日成功订单总数:{{$v['zfb_succ_count']}}</td>
                <td colspan="2">[{{$k}}]当日失败订单总额:{{$v['zfb_succ_amount']}}</td>
                <td colspan="2">[{{$k}}]当日失败订单总数:{{($v['zfb_total_count']) - ($v['zfb_succ_count'])}}</td>
                <td colspan="2">[{{$k}}]当日失败订单总额:{{($v['zfb_total_amount']) - ($v['zfb_succ_amount'])}}</td>
            </tr>
        @endforeach
        <tr>
            <th width="100">游戏ID</th>
            <th width="100">用户类型</th>
            <th width="100">申请时间</th>
            <th width="100">处理状态</th>
            <th width="100">转账金额</th>
            <th width="100">订单号</th>
            <th width="100">转账方式</th>
            <th width="100">收款账号</th>
            <th width="100">收款人</th>
            <th width="100">处理ID</th>
            <th width="100">备注</th>
            <th width="100">操作</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>

                            <td>{{ $val->user_id }}</td>
                            <td>
                                @if($val->user_type == '1')
                                    <span style="color: #985f0d">代理</span>
                                @else
                                    玩家
                                @endif
                            </td>
                            <td>{{ date("Y-m-d H:i:s",$val->create_at) }}</td>
                            <td>
                                @if($val->state == 0)
                                    [已下发]
                                @elseif($val->state == 1)
                                    [已返还用户]
                                @elseif($val->state == 6)
                                    [出款中]
                                @endif
                            </td>
                            <td>{{ $val->draw_money }}</td>
                            <td title="{{$val->order_sn}}">{{ substr($val->order_sn,-6,6) }}</td>
                            <td>
                                @if($val->ali_type == '1')
                                    <span style="color: red">手工</span>
                                @else
                                   [官方{{$val->ali_type}}号]
                                @endif
                            </td>
                            <td> {{ $val->ali_account}}</td>
                            <td> {{$val->realname}}</td>
                            <td> {{$val->operator_id}}</td>
                            <td>{{$val->remark}}</td>
                            <td>
                                @if($val->ali_type >=2)
                                    <a href="javascript:;" onclick="query_state({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">核实</button>
                                    </a>

                                @endif
                            </td>
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

        function query_state(user_id,order_id){
            var post_url = "{{route('admin.order.reset_order_state')}}";
            var verify_code =  $("#verify_code").val();
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            if(!verify_code){
                layer.msg("验证码不能为空", {icon: 5});
                return ;
            }
            var load = layer.load();
            layer.confirm('确定重置订单状态?', {
                btn: ['是','否'] //按钮
            }, function(){
                $.ajax({
                    type: "post",
                    data: {order_id: order_id, user_id: user_id,verify_code:verify_code},
                    url: post_url,
                    dataType: 'json',
                    success: function (res) {
                        layer.close(load);
                        if(res.code==0){
                            layer.msg('重置成功', {icon: 1});
                            setTimeout(function () { window.location.href="{{route('admin.order.index')}}";},1000);
                        }else{
                            layer.msg(res.msg, {icon: 5});
                        }
                    },
                })
            },function(){
                layer.close(load);
            });
        }
    </script>
@endsection
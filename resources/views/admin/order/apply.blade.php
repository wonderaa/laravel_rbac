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
    <a href="javascript:;" onclick="receive_order();" class="layui-btn">系统接单</a>
    <form id="search_form">
        <table class="layui-table">
            <tr>
                <td><input  name="search_s" placeholder="操作时间"  value="{{$search_data['search_s']}}" class="layui-input" id="search_s"></td>
                <td><input  name="search_e" placeholder="操作时间"  value="{{$search_data['search_e']}}" class="layui-input" id="search_e"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['user_id']}}" placeholder="游戏ID" name="user_id"></td>
                <td><input type="text" class="layui-input" value="{{$search_data['realname']}}" placeholder="收款人" name="realname"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['bank_name']}}" placeholder="银行名称" name="bank_name"></td>
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
            <th colspan="8">全部订单<span class="r">共有数据：<strong>{{$count}}</strong> 条</span> </th>
            <th colspan="4">页面刷新倒计时:
                <span id="flush_seconds" style="color: red;padding: auto 10px;"></span>
            </th>
        </tr>
        <tr>
            <th width="100">申请时间</th>
            <th width="100">订单号</th>
            <th width="100">游戏ID</th>
            <th width="100">用户类型</th>
            <th width="100">转账方式</th>
            <th width="100">收款账号</th>
            <th width="100">收款人</th>
            <th width="100">转账金额</th>
            <th width="200">操作</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{ date("Y-m-d H:i:s",$val->create_at) }}</td>
                            <td>{{ $val->order_sn }}</td>
                            <td>{{ $val->user_id }}</td>
                            <td>
                                @if($val->widthdraw_type == '1')
                                    <span style="color: #985f0d">代理</span>
                                    @else
                                    玩家
                                @endif
                            </td>
                            <td>
                                @if($val->bank_name == '支付宝')
                                    <span style="color: red">支付宝</span>
                                @else
                                    {{$val->bank_name}}
                                @endif
                            </td>
                            <td>
                                {{substr_replace($val->bank_account,'****',3,-3)}}
                            </td>
                            <td>
                                ****
                            </td>
                            <td> {{ number_format($val->amount,2,'.','')}}</td>
                            <td>
                                <a href="javascript:;" onclick="hand_receive_order({{$val->id}});"><button class="layui-btn">接单</button> </a>
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
        //2019-06-26 手动接单
        function hand_receive_order(order_id){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type:'post',
                url:"{{route('admin.order.hand_receive_order')}}",
                data:{order_id:order_id},
                dataType:'json',
                success:function(res){
                    layer.close(load);
                    console.log(res)
                    if(res.code==0){
                        layer.msg(res.msg,{icon:1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg,{icon:5});
                    }
                },
                error:function(){
                    layer.close(load);
                }
            });
        }
        //2019-06-26 自动接单
        function receive_order(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type:'post',
                url:"{{route('admin.order.receive_order')}}",
                data:{},
                dataType:'json',
                success:function(res){
                    layer.close(load);
                    if(res.code==0){
                        layer.msg(res.msg,{icon:1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},100000);
                    }else{
                        layer.msg(res.msg,{icon:5});
                    }
                },
                error:function(){
                layer.close(load);
            }
            });
        }
        //自动刷新
        function flush_page() {
            var flush_time = 60;
            var resend_time =  window.setInterval(function(){
                flush_time--;
                if (flush_time > 0){
                    $("#flush_seconds").text(flush_time);
                }else {
                    window.location.reload();
                    clearInterval(resend_time);
                }
            }, 1000);
            $("#flush_seconds").text(flush_time);
        }
        flush_page();
    </script>
@endsection
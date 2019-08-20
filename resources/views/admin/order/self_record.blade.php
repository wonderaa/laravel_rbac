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
                <td><input type="text" class="layui-input"  value="{{$search_data['receive_id']}}" placeholder="操作ID" name="receive_id"></td>

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
            <th width="100">时间</th>
            <th width="100">出款ID</th>
            <th width="100">出款总额</th>
            <th width="100">成功出款总额</th>
            <th width="100">银行卡成功出款总额</th>
            <th width="100">支付宝成功出款总额</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>

                            <td>{{ $val->hand_at }}</td>

                            <td> {{$val->receive_id}}</td>
                            <td> {{$val->total_amount}}</td>
                            <td> {{$val->success_amount}}</td>
                            <td> {{$val->bank_success_amount}}</td>
                            <td> {{$val->zfb_success_amount}}</td>
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
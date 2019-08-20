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
                <td><input  name="search_s" placeholder="操作时间"  value="{{ $search_data['search_s'] }}" class="layui-input" id="search_s"></td>
                <td><input  name="search_e" placeholder="操作时间"  value="{{ $search_data['search_e'] }}" class="layui-input" id="search_e"></td>
                <td><input type="text" class="layui-input"  value="{{ $search_data['user_id'] }}" placeholder="游戏ID" name="user_id"></td>
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
            <th width="80">申请时间</th>
            <th width="50">订单号</th>
            <th width="50">游戏ID</th>
            <th width="60">用户类型</th>
            <th width="80">转账方式</th>
            <th width="150">收款账号</th>
            <th width="100">收款人</th>
            <th width="100">转账金额</th>
            <th width="200">操作</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{ date("m-d H:i",$val->create_at) }}</td>
                            <td title="{{$val->order_sn}}">{{ substr($val->order_sn,-6,6) }}</td>
                            <td>{{ $val->user_id }}</td>
                            <td>
                                @if($val->widthdraw_type == '1')
                                    <span style="color: #985f0d">代理</span>
                                    @else
                                    玩家
                                @endif
                            </td>
                            <td>
                                <button title="copy" class="layui-btn layui-btn-xs"  style="float: left" onclick="copy_text(this,{{$val->bank_name}});">C</button>
                                @if($val->bank_name == '支付宝')
                                    <span style="color: red">支付宝</span>
                                @else
                                    {{$val->bank_name}}
                                @endif
                            </td>
                            <td>
                                <button title="copy" class="layui-btn layui-btn-xs" style="float: left" onclick="copy_text(this,{{$val->bank_account}});">C</button>
                                {{ $val->bank_account }}
                            </td>
                            <td>
                                <button class="layui-btn layui-btn-xs" style="float: left" onclick="copy_text(this,{{$val->realname}});">C</button>
                                {{ $val->realname }}
                            </td>
                            <td>
                                <button class="layui-btn layui-btn-xs" style="float: left" onclick="copy_text(this,{{number_format($val->amount,2,'.','')}});">C</button>
                                {{number_format($val->amount,2,'.','')}}
                            </td>
                            <td>
                                <a href="javascript:;" onclick="query_order({{$val->user_id}},{{$val->widthdraw_type}});" data-method="offset" data-type="auto">
                                    <button class="layui-btn layui-btn-primary layui-btn-xs">稽核</button>
                                </a>

                                <a href="javascript:;" onclick="return_order({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                    <button class="layui-btn layui-btn-primary layui-btn-xs">退回订单</button>
                                </a>

                                <a href="javascript:;" onclick="return_system({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                    <button class="layui-btn layui-btn-primary layui-btn-xs">退回系统</button>
                                </a>
                                {{--zfb手动出款--}}
                                @if(($user->user_type == 1 || in_array($user->id,[10])) && trim($val->bank_name) == $bank_name)
                                    <a href="javascript:;" onclick="transfer_hand_zfb({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">手工转款</button>
                                    </a>
                                @endif
                                {{--yhk手动出款--}}
                                @if(($user->user_type == 2 || in_array($user->id,[10])) && trim($val->bank_name) != $bank_name)
                                    <a href="javascript:;" onclick="transfer_hand_bank({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">手工转款</button>
                                    </a>
                                @endif
                                {{--zfb出款--}}
                                @if(($user->user_type == 3 || in_array($user->id,[10])) && trim($val->bank_name) == $bank_name)
                                    <a href="javascript:;" onclick="transfer_zfb_auto({{$val->user_id}},{{$val->id}},2);" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">支付宝一号</button>
                                    </a>
                                    <a href="javascript:;" onclick="transfer_zfb_auto({{$val->user_id}},{{$val->id}},3);" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">支付宝二号</button>
                                    </a>
                                @endif
                                {{--yhk出款--}}
                                @if(($user->user_type == 4 || in_array($user->id,[10])) && trim($val->bank_name) != $bank_name)
                                    @if($bank_use_list['is_use_yzb'] == 1 && $menu_info['bank_yzb'] == 1)
                                        <a href="javascript:;" onclick="transfer_bank_auto({{$val->user_id}},{{$val->id}},2);" data-method="offset" data-type="auto">
                                            <button class="layui-btn layui-btn-primary layui-btn-xs">易支宝</button>
                                        </a>
                                    @endif
                                    @if($bank_use_list['is_use_kk'] == 1 && $menu_info['bank_kk'] == 1)
                                        <a href="javascript:;" onclick="transfer_bank_auto({{$val->user_id}},{{$val->id}},3);" data-method="offset" data-type="auto">
                                            <button class="layui-btn layui-btn-primary layui-btn-xs">KKBANK</button>
                                        </a>
                                    @endif
                                    @if($bank_use_list['is_use_ai'] == 1 && $menu_info['bank_ai'] == 1)
                                        <a href="javascript:;" onclick="transfer_bank_auto({{$val->user_id}},{{$val->id}},4);" data-method="offset" data-type="auto">
                                            <button class="layui-btn layui-btn-primary layui-btn-xs">艾付</button>
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                   @endforeach
            @endif
        </tbody>
    </table>
    <div id="page" class="layui-box layui-laypage layui-laypage-default">{{ $data->links() }}</div>

@endsection

<script type="text/javascript" src="/js/clipboard.min.js"></script>
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
        //查看玩家信息
        function query_order(user_id,widthdraw_type){
            var table_info = '<table class="layui-table">';
            var post_url = "{{route('admin.order.get_user_info')}}";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type:"post",
                data:{user_type:widthdraw_type,user_id:user_id},
                url:post_url,
                dataType:'json',
                success:function(res){
                    layer.close(load);
                    console.log(res)
                    if(res.code==0){
                        if(res.data.user_type==1){
                            table_info += '<tr>';
                            table_info += '<td width="100">总提现次数</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.widthdraw_count)+'</span></td>';
                            table_info += '<td width="100">总提现金额</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.widthdraw_amount)+'</span></td>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<td width="100">上次提现金额</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.last_widthdraw_amount)+'</span></td>';
                            table_info += '<td width="100">上次提现时间</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.last_widthdraw_time)+'</span></td>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<td width="100">后台总返佣金额</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.remaid_total)+'</span></td>';
                            table_info += '<td width="100">可提现金额</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.avaliable_widthdraw)+'</span></td>';
                            table_info += '</tr>';
                            table_info += '</table>';
                        }else{
                            table_info += '<tr>';
                            table_info += '<th width="100" colspan="2">ID</th>';
                            table_info += '<th width="100" colspan="2"><span>'+res.data.user_id+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<td width="100">渠道充值次数</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.pay_count)+'</span></td>';
                            table_info += '<td width="100">渠道充值金额</td>';
                            table_info += '<td width="100"><span>'+parseFloat(res.data.pay_amount)+'</span></td>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">代理充值</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.agency_charge)+'</span></th>';
                            table_info += '<th width="100">系统邮件</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.system_charge)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">7天充值次数</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.pay_week_count)+'</span></th>';
                            table_info += '<th width="100">7天充值金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.pay_week_amount)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">总提现次数</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.widthdraw_count)+'</span></th>';
                            table_info += '<th width="100">总提现金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.widthdraw_amount)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">7天提现次数</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.widthdraw_week_count)+'</span></th>';
                            table_info += '<th width="100">7天提现金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.widthdraw_week_amount)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">游戏剩余金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.current_diamond)+'</span></th>';
                            table_info += '<th width="100">底钱</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.basemoney)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">总下注金额</th>';
                            table_info += '<th width="100"><span>'+Math.floor(parseFloat(res.data.total_lose))+Math.floor(parseFloat(res.data.total_win)/0.94)+'</span></th>';
                            table_info += '<th width="100">总下注盈利</th>';
                            table_info += '<th width="100"><span>'+Math.floor((parseFloat(res.data.total_win)-parseFloat(res.data.total_lose))*100)/10+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">总赢金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.total_win)+'</span></th>';
                            table_info += '<th width="100">总输金额</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.total_lose)+'</span></th>';
                            table_info += '</tr>';

                            table_info += '<tr>';
                            table_info += '<th width="100">总提现</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.total_withdraw)+'</span></th>';
                            table_info += '<th width="100">总手续费</th>';
                            table_info += '<th width="100"><span>'+parseFloat(res.data.total_servicefee)+'</span></th>';
                            table_info += '</tr>';
                            table_info += '</table>';
                        }

                    }else{
                        layer.msg(res.msg,{icon:5});
                    }
                    layer.open({
                        type: 1
                        ,title:"玩家信息(稽核 单位:元)"
                        ,offset: 'auto'
                        ,id: 'userDetail'
                        ,content:table_info
                        ,btn:"关闭"
                        ,btnAlign:'c'
                        ,area: ['50%','70%']
                        ,yes: function(){
                            layer.closeAll();
                        }
                    });
                }
            },function(){
                layer.close(load);
            });
        }

        //退回游戏
        function return_order(user_id,order_id){

            var table_info = '<table class="layui-table">';
                table_info += '<tr>'
                table_info += '<td><input class="layui-input" name="return_reason" id="return_reason" placeholder="退回原因(必填)"></td>';
                table_info += '</tr>';
                table_info += '<tr>'
                table_info += '<td colspan="2" style="text-align: center"><button class="layui-btn layui-btn-sm" onclick="return_order_act('+user_id+','+order_id+')">退回</button></td>';
                table_info += '</tr>';

                table_info += '</table>';
                layer.open({
                     type: 1
                    ,title:"退回原因"
                    ,offset: 'auto'
                    ,id: 'returnReason'
                    ,content:table_info
                    ,scrollbar:false
                    ,area: ['30%','28%']
                    ,yes: function(){
                        layer.closeAll();
                    }
                });
        }
        function return_order_act(user_id,order_id){
            var post_url = "{{route('admin.order.return_order')}}";
            var return_reason =  $("#return_reason").val();
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();

            $.ajax({
                type: "post",
                data: {order_id: order_id, user_id: user_id,return_reason:return_reason},
                url: post_url,
                dataType: 'json',
                success: function (res) {
                    layer.close(load);
                    if(res.code==0){
                        layer.msg('处理成功', {icon: 1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            },function(){
                layer.close(load);
            });
        }

        //退回游戏
        function return_system(user_id,order_id){
            var post_url = "{{route('admin.order.return_system')}}";
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            layer.confirm('确定要退回系统?', {
                btn: ['是','否'] //按钮
            }, function(){
                $.ajax({
                    type: "post",
                    data: {order_id: order_id, user_id: user_id},
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
                    }
                })
            },function(){
                layui.close(load);
            });
        }
        //复制
        function copy_text(obj,val){
            var clipboard = new Clipboard(obj,{
                text: function() {
                    return val;
                }
            });
            clipboard.on('success', function(e) {
                layer.msg("复制成功",{icon:1});
            });
            clipboard.on('error', function(e) {
                layer.msg("复制失败",{icon:5});
            });
        }
        //手工支付宝出款
        function transfer_hand_zfb(user_id,order_id){
            var post_url = "{{route('admin.order.transfer_hand_zfb')}}";
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type: "post",
                data: {order_id: order_id, user_id: user_id},
                url: post_url,
                dataType: 'json',
                success: function (res) {
                    layer.close(load);
                    if(res.code==0){
                        layer.msg('出款成功', {icon: 1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            })
        }
        //手工支付宝出款
        function transfer_hand_bank(user_id,order_id){
            var post_url = "{{route('admin.order.transfer_hand_bank')}}";
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type: "post",
                data: {order_id: order_id, user_id: user_id},
                url: post_url,
                dataType: 'json',
                success: function (res) {
                    layer.close(load);
                    if(res.code==0){
                        layer.msg('出款成功', {icon: 1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            },function(){
                layer.close(load);
            })
        }
        //支付宝自动出款
        function transfer_zfb_auto(user_id,order_id,zfb_num){
            var post_url = "{{route('admin.order.transfer_zfb_auto')}}";
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type: "post",
                data: {order_id: order_id, user_id: user_id,zfb_num:zfb_num},
                url: post_url,
                dataType: 'json',
                success: function (res) {
                    layer.close(load);
                    if(res.code==0){
                        layer.msg('出款成功', {icon: 1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            },function(){
                layer.close(load);
            })
        }

        //银行卡自动出款
        function transfer_bank_auto(user_id,order_id,bank_num){
            var post_url = "{{route('admin.order.transfer_bank_auto')}}";
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            var load = layer.load();
            $.ajax({
                type: "post",
                data: {order_id: order_id, user_id: user_id,bank_num:bank_num},
                url: post_url,
                dataType: 'json',
                success: function (res) {
                    layer.close(load);
                    if(res.code==0){
                        layer.msg('出款成功', {icon: 1});
                        setTimeout(function () { window.location.href="{{route('admin.order.wait')}}";},1000);
                    }else{
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            },function(){
                layer.close(load);
            })
        }
    </script>
@endsection
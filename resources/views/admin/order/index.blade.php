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
                <td><input type="text" class="layui-input" value="{{$search_data['realname']}}" placeholder="收款人" name="realname"></td>
                <td><input type="text" class="layui-input"  value="{{$search_data['order_id']}}" placeholder="订单ID" name="order_id"></td>
                <td>
                    <select name='state' class="layui-select" style="float:left;width:150px;" >
                        <option value='-1' @if($search_data['state']==-1) selected @endif>请选择</option>
                        <option value='0'  @if($search_data['state']==0) selected @endif>待接单</option>
                        <option value='1'  @if($search_data['state']==1) selected @endif>处理中</option>
                        <option value='2'  @if($search_data['state']==2) selected @endif>已发放</option>
                        <option value='3'  @if($search_data['state']==3) selected @endif>已退回</option>
                        <option value='4'  @if($search_data['state']==4) selected @endif>已拒绝</option>
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
            <th colspan="12">全部订单<span class="r">共有数据：<strong>{{$count}}</strong> 条</span> </th>
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
            <th width="100">处理状态</th>
            <th width="100">备注</th>
            <th width="100">处理账号</th>
            <th width="200">操作</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{ date("Y-m-d H:i:s",$val->create_at) }}</td>
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
                                @if($val->bank_name == '支付宝')
                                    <span style="color: red">支付宝</span>
                                @else
                                    {{$val->bank_name}}
                                @endif
                            </td>
                            <td>
                                {{ substr_replace($val->bank_account,'****',3,5)}}
                            </td>
                            <td>
                                {{$val->realname}}
                            </td>
                            <td>
                                @if(in_array($user->id,[10]))
                                    {{$val->amount}}
                                @else
                                    ****
                                @endif

                            </td>
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
                            <td>
                                {{$val->remark}}
                            </td>
                            <td>
                                {{$val->receive_id}}
                            </td>
                            <td>
                                <a href="javascript:;" onclick="query_order({{$val->user_id}},{{$val->widthdraw_type}});" data-method="offset" data-type="auto">
                                    <button class="layui-btn layui-btn-primary layui-btn-xs">稽核</button> </a>

                                @if($val->state >=1 && in_array($user->id,$reset_order_id))
                                    <a href="javascript:;" onclick="reset_order_model({{$val->user_id}},{{$val->id}});" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">重置状态</button>
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
        /**
         * 查看订单
         * */
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
        function reset_order_model(user_id,order_id){
            var table_info = '<table class="layui-table">';
                table_info += '<tr>'
                table_info += '<td><input type="text" required class="layui-input" value=""  placeholder="Google验证码(必填)" id="verify_code" name="verify_code"></td>';
                table_info += '</tr>';
                table_info += '<tr>'
                table_info += '<td  style="text-align: center"><button class="layui-btn layui-btn-primary" onclick="reset_order('+user_id+','+order_id+')">保存</button></td>';
                table_info += '</tr>';
                table_info += '</table>';
                layer.open({
                    type: 1
                    ,title:"Google验证码"
                    ,offset: 'auto'
                    ,id: 'userDetail'
                    ,content:table_info
                    ,area: ['30%','25%']
                    ,yes: function(){
                        layer.closeAll();
                    }
                });
        }
        function reset_order(user_id,order_id){
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
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
                 <td>
                    <select name='state' class="layui-select" style="float:left;width:150px;" >
                        <option value='0'>请选择</option>
                        <option value='1'  @if($search_data['state']==1) selected @endif>待领取</option>
                        <option value='2'  @if($search_data['state']==2) selected @endif>已领取</option>
                        <option value='3'  @if($search_data['state']==3) selected @endif>已撤销</option>
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
            <th width="100">历史充值总额:<span style="color:#13c2c2;">{{$total_recharge_money/100}}</span></th>
            <th width="100">今日充值总额:<span style="color:#13c2c2;">{{$today_recharge_money/100}}</span></th>
        </tr>
        <tr>
            <th width="100">游戏ID</th>
            <th width="100">充值时间</th>
            <th width="100">领取时间</th>
            <th width="100">充值金额</th>
            <th width="100">状态</th>
            <th width="100">备注</th>
            <th width="100">操作</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{$val->recvrid}}</td>

                            <td>
                                @if($val->create_time > 0)
                                   {{ date("Y-m-d H:i",$val->create_time) }}
                                @endif
                            </td>
                            <td>
                                @if($val->get_time > 0)
                                    {{ date("Y-m-d H:i",$val->get_time) }}
                                @endif
                            </td>
                            <td>{{$val->give_value/100}}</td>
                            <td>
                                @if($val->current_state == 1)
                                    待领取
                                @elseif($val->current_state == 2)
                                    已领取
                                @else
                                    已撤销(撤销ID:{{$val->undorid}})
                                @endif
                            </td>
                            <td>
                                {{$val->remark}}
                            </td>
                            <td>
                                @if($val->current_state ==1)
                                    <a href="javascript:;" onclick="reset_order_model({{$val->keyid}});" data-method="offset" data-type="auto">
                                        <button class="layui-btn layui-btn-primary layui-btn-xs">撤销</button>
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

        function reset_order_model(keyid){
            var table_info = '<table class="layui-table">';
                table_info += '<tr>'
                table_info += '<td><input type="text" required class="layui-input" value=""  placeholder="撤销原因(必填)" id="remark" name="remark"></td>';
                table_info += '</tr>';
                table_info += '<tr>'
                table_info += '<td  style="text-align: center"><button class="layui-btn layui-btn-primary" onclick="reset_order('+keyid+')">保存</button></td>';
                table_info += '</tr>';
                table_info += '</table>';
                layer.open({
                    type: 1
                    ,title:"撤销订单"
                    ,offset: 'auto'
                    ,id: 'userDetail'
                    ,content:table_info
                    ,area: ['30%','25%']
                    ,yes: function(){
                        layer.closeAll();
                    }
                });
        }
        function reset_order(keyid){
            var post_url = "{{route('admin.recharge.cancel_give_money')}}";
            var remark =  $("#remark").val();
            layer.closeAll();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            if(!remark){
                layer.msg("撤销原因不能为空", {icon: 5});
                return ;
            }
            var load = layer.load();
            layer.confirm('确定撤销订单?', {
                btn: ['是','否'] //按钮
            }, function(){
                $.ajax({
                    type: "post",
                    data: {keyid: keyid,remark:remark},
                    url: post_url,
                    dataType: 'json',
                    success: function (res) {
                        layer.close(load);
                        if(res.code==0){
                            layer.msg('撤销成功', {icon: 1});
                            setTimeout(function () { window.location.href="{{route('admin.recharge.recharge_record')}}";},1000);
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
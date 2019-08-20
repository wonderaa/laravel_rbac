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
                <td><input type="text" class="layui-input"  value="{{$search_data['send_id']}}" placeholder="赠送ID" name="send_id"></td>

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
            <th width="100" colspan="2">历史充值总额:<span style="color:#13c2c2;">{{$total_recharge_money/100}}</span></th>
            <th width="100" colspan="2">今日充值总额:<span style="color:#13c2c2;">{{$today_recharge_money/100}}</span></th>
        </tr>
        <tr>
            <th width="100">游戏ID</th>
            <th width="100">充值时间</th>
            <th width="100">充值金额</th>
            <th width="100">订单号</th>
        </tr>
        </thead>
        <tbody>
            @if($data)
                   @foreach($data as $val)
                        <tr>
                            <td>{{$val->recvrid}}</td>

                            <td>
                                @if($val->create_at > 0)
                                   {{ date("Y-m-d H:i",$val->create_at) }}
                                @endif
                            </td>
                            <td>{{$val->diamond/100}}</td>
                            <td>{{$val->order_sn}}</td>

                        </tr>
                   @endforeach
            @endif
        </tbody>
    </table>
    <div id="page" class="layui-box layui-laypage layui-laypage-default">{{ $data->links() }}</div>

@endsection

@section('script')
@endsection
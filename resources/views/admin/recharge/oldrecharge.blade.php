@extends('layouts.admin')

@section('content')
    <form class="layui-form" action="" style="width: 800px;">

        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">可用余额</label>
            <label class="layui-form-label">{{$account_info['amount']-$account_info['widthdraw']}}</label>

        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">用户ID</label>
            <div class="layui-input-block">
                <input type="text" id="user_id" lay-verify="user_id" required value="" name="user_id" placeholder="用户ID" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">充值金额</label>
            <div class="layui-input-block">
                <input type="text" id="diamond" lay-verify="diamond" required value="" name="diamond" placeholder="充值金额" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                <button class="layui-btn" lay-submit lay-filter="formDemo" type="button">充值</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        layui.use(['form', 'layer'], function () {
            var form = layui.form;
            var layer = layui.layer;
            form.verify({
                user_id:function (value) {
                    if(value.length<6){
                        return '请输合法用户ID';
                    }
                },
                diamond:function (value) {
                    if(value<1){
                        return '请输入合法充值金额';
                    }
                }
            });

            //监听提交
            form.on('submit(formDemo)', function (data) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                var Rand = Math.random();
                var num = Math.round(Rand * 100);
                layer.prompt({title: '请输入金币数:'+parseFloat(data.field.diamond)+'-'+num+'=?', formType: 3}, function(pass, index) {
                    layer.close(index)
                    console.log(pass);
                    if (pass != (parseFloat(data.field.diamond)-num)) {
                        layer.msg('你输入的金额数量有误，请核实', {icon: 5});
                        return false;
                    } else {
                        // var load = layer.load();
                        $.post("{{ route('admin.recharge.oldstore') }}", data.field,function (res) {
                            // layer.close(load);
                            if (res.code == 0) {
                                layer.msg('操作成功', {
                                    offset: '15px'
                                    , icon: 1
                                    , time: 1000
                                }, function () {
                                    location.href = '{{ route('admin.recharge.index') }}';
                                });

                            } else {
                                layer.msg(res.msg, {
                                    offset: '15px'
                                    , icon: 2
                                    , time: 2000
                                });
                            }
                        });
                    }
                });
            });
        });

    </script>
@endsection
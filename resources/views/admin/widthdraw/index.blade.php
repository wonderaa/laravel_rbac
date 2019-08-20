@extends('layouts.admin')

@section('content')
    <form class="layui-form" action="" style="width: 800px;">
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">出款银行</label>
            <div class="layui-input-block">
                <select name="bank_code" class="layui-select" >
                    <option value="0">请选择</option>
                    @foreach($bank_list as $k=>$v)
                        <option value="{{$k}}" >{{$v}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">出款商户</label>
            <div class="layui-input-block">
                <select name="remit_type" required class="layui-select">
                    <option value="0">请选择</option>
                    <option value="1">川流</option>
                    <option value="2">DX银联</option>
                    <option value="3">通付宝</option>
                    <option value="4">OPC</option>
                    <option value="6">瀚银快捷</option>
                    <option value="7">付比出款</option>
                </select>
            </div>
        </div>

        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">出款金额</label>
            <div class="layui-input-block">
                <input type="text" id="amount"  required value="" name="amount" placeholder="出款金额" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="formDemo" type="button">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        layui.use(['form', 'layer'], function () {
            var form = layui.form;
            var layer = layui.layer;
            //监听提交
            form.on('submit(formDemo)', function (data) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.post("{{ route('admin.widthdraw.remit_act') }}", data.field,function (res){

                    if (res.code == 0) {
                        layer.msg('操作成功', {
                            offset: '15px'
                            , icon: 1
                            , time: 1000
                        }, function () {
                            location.href = '{{ route('admin.widthdraw.index') }}';
                        });

                    } else {
                        layer.msg(res.msg, {
                            offset: '15px'
                            , icon: 2
                            , time: 2000
                        });
                    }
                });
            });
        });

    </script>
@endsection
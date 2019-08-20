@extends('layouts.admin')

@section('content')
    <form class="layui-form" action="" style="width: 800px;">
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">银行卡</label>
            <div class="layui-input-block">
                <select name="bank_code" class="layui-select" lay-filter="search_type">
                    <option value="0">请选择</option>
                    @foreach($bank_list as $k=>$v)
                        <option value="{{$k}}" >{{$v}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">姓名</label>
            <div class="layui-input-block">
                <input type="text" id="realname"  required value="" name="realname" placeholder="姓名" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">银行卡号</label>
            <div class="layui-input-block">
                <input type="text" id="bank_account"  required value="" name="bank_account" placeholder="银行卡号" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">开户支行</label>
            <div class="layui-input-block">
                <input type="text" id="bank_branch"  required value="" name="bank_branch" placeholder="开户支行" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">开户省</label>
            <div class="layui-input-block">
                 <input type="text" id="bank_province"  required value="" name="bank_province" placeholder="开户省" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">开户市</label>
            <div class="layui-input-block">
                <input type="text" id="bank_city"  required value="" name="bank_city" placeholder="开户市" class="layui-input">
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
                // var load = layer.load();
                $.post("{{ route('admin.widthdraw.store') }}", data.field,function (res) {
                    // layer.close(load);
                    if (res.code == 0) {
                        layer.msg('操作成功', {
                            offset: '15px'
                            , icon: 1
                            , time: 1000
                        }, function () {
                            location.href = '{{ route('admin.widthdraw.bind_bank_card') }}';
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

            form.on('select(search_type)', function(data){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                var load = layer.load();
                $.post("{{ route('admin.widthdraw.get_bank_info') }}", {bank_code:data.value},function (res) {
                    layer.close(load);
                    if (res.code == 0) {
                        $("#realname").val(res.data.realname);
                        $("#bank_account").val(res.data.bank_account);
                        $("#bank_branch").val(res.data.bank_branch);
                        $("#bank_name").val(res.data.bank_name);
                        $("#bank_province").val(res.data.bank_province);
                        $("#bank_city").val(res.data.bank_city);
                        form.render('select');
                    } else {
                        $("#realname").val('');
                        $("#bank_account").val('');
                        $("#bank_branch").val('');
                        $("#bank_name").val('');
                        $("#bank_province").val('');
                        $("#bank_city").val('');
                    }
                });


            });

        });

    </script>
@endsection
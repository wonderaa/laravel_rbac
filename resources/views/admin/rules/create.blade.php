@extends('layouts.admin')

@section('content')
    <a href="{{ route('admin.rules.index') }}" class="layui-btn layui-btn-primary layui-btn-sm">返回</a>
    <hr>
    <form class="layui-form" action="" style="width: 900px;">
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">路由名称</label>
            <div class="layui-input-block">
                <input type="text" name="title" required lay-verify="required" placeholder="请输入菜单名称"
                       autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">父级路由</label>
            <div class="layui-input-block">
                <select name="pid" lay-verify="required" lay-filter="pid">
                    <option value="0">顶级路由</option>
                    @foreach($top_menu as $m)
                        <option value="{{ $m->id }}">{{ $m->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px; " id="route">
            <label class="layui-form-label">路由标识</label>
            <div class="layui-input-block">
                <select name="name">
                    <option value="">请选择路由标识</option>
                    @foreach($routes as $route)
                        <option value="{{ $route}}">{{ $route}}</option>
                    @endforeach
                </select>
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

            form.on('select(pid)', function (data) {
                var pid = data.value;
                $('#pid_roles div').hide();
                $('#pid_roles input').removeAttr('checked');
                $('#pid_' + pid).show();

                // if(pid == 0){
                //     $('#route').hide();
                // }else{
                //     $('#route').show();
                // }
                form.render();
            });

            //监听提交
            form.on('submit(formDemo)', function (data) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                var load = layer.load();
                $.post("{{ route('admin.rules.store') }}", data.field,
                    function (data) {
                        layer.close(load);
                        if (data.code === 0) {
                            layer.msg('操作成功', {
                                offset: '15px'
                                , icon: 1
                                , time: 1000
                            }, function () {
                                location.href = '{{ route('admin.rules.index') }}';
                            });

                        } else {
                            layer.msg(data.msg, {
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
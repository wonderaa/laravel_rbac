@extends('layouts.admin')

@section('content')
    <a href="{{ route('admin.rules.index') }}" class="layui-btn layui-btn-primary layui-btn-sm">返回</a>
    <hr>
    <form class="layui-form" action="" style="width: 900px;">
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">路由名称</label>
            <div class="layui-input-block">
                <input type="text" name="title" required lay-verify="required" placeholder="请输入路由名称"
                       autocomplete="off" class="layui-input"
                       @if($rules)
                       value="{{ $rules->title }}"
                        @endif >
                @if($rules)
                    <input type="hidden" name="id" value="{{ $rules->id }}">
                @endif
            </div>
        </div>
        <div class="layui-form-item" style="width: 500px;">
            <label class="layui-form-label">父级菜单</label>
            <div class="layui-input-block">
                <select name="pid" lay-verify="required" lay-filter="pid">
                    <option value="0">顶级菜单</option>
                    @foreach($top_menu as $m)
                        <option value="{{ $m->id }}"
                                @if($m->id == $rules->pid)
                                selected
                                @endif
                        >{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item" id="name" style="width: 500px;">
            <label class="layui-form-label">路由标识</label>
            <div class="layui-input-block">
                <select name="name">
                    <option value="">请选择路由标识</option>
                    @foreach($routes as $route)
                        <option value="{{ $route}}"
                                @if($route == $rules->name)
                                selected
                                @endif
                        >{{ $route}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="formDemo" type="button">立即提交</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        layui.use(['form', 'layer'], function () {
            var form = layui.form;
            var layer = layui.layer;

            @if($error)
            layer.msg('{{ $error }}', {
                offset: '15px'
                , icon: 2
                , time: 2000
            }, function () {
                location.href = '{{ route('admin.rules.index') }}';
            });
            @endif

            form.on('select(pid)', function (data) {
                var pid = data.value;
                $('#pid_roles div').hide();
                $('#pid_roles input').removeAttr('checked');
                $('#pid_' + pid).show();
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
                $.post("{{ route('admin.rules.update') }}", data.field,
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
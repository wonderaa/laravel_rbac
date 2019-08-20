@extends('layouts.admin')

@section('content')
    <a href="{{ route('admin.rules.create') }}" class="layui-btn">添加路由</a>
    <table class="layui-table layui-form" id="tree-table"></table>
@endsection

@section('script')
    <script src="/js/layui/ext/treeTable.js"></script>
    <script>
        layui.use(['layer', 'form'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var treeTable = layui.treeTable;

            var re = treeTable.render({
                elem: '#tree-table',
                data: {!! $rules !!},
                icon_key: 'title',
                is_checkbox: false,
                is_cache: false,
                end: function (e) {
                    form.render();
                },
                cols: [
                    {
                        key: 'title',
                        title: '名称',
                        width: '150px',
                        template: function (item) {
                            if (item.level == 0) {
                                return '<span style="color:#FF5722;">' + item.title + '</span>';
                            } else if (item.level == 1) {
                                return '<span style="color:green;">' + item.title + '</span>';
                            } else if (item.level == 2) {
                                return '<span style="color:#aaa;">' + item.title + '</span>';
                            }
                        }
                    },
                    {
                        title: '路由',
                        align: 'left',
                        template: function (item) {
                            if (item.name != null) {
                                return item.name;
                            } else {
                                return '';
                            }
                        }
                    },

                    {
                        key: 'update_at',
                        title: '创建时间',
                        width: '100px',
                        align: 'center'
                    },
                    {
                        title: '操作',
                        align: 'center',
                        width: '100px',
                        template: function (item) {
                            var html = '<a href="{{ route('admin.rules.edit') }}?rules_id=' + item.id + '" class="layui-btn layui-btn-xs">编辑</a>';
                            if (item.id != 1 && item.pid != 1) {
                                html += '<button class="layui-btn layui-btn-danger layui-btn-xs" type="button" onclick="del(' + item.id + ')">删除 </button>';
                            }
                            return html;
                        }
                    },
                ]
            });

        });

        function del(id) {
            layer.confirm('你确定要删除这个路由吗？', {
                title: '删除确认',
                btn: ['确定', '取消'] //按钮
            }, function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                var load = layer.load();
                $.post("{{ route('admin.rules.delete') }}", {id: id},
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
        }
    </script>
@endsection
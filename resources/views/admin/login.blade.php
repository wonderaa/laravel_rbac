<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登入</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/js/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/js/layui/css/login.css" media="all">
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <p>欢迎登陆后台管理系统</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <form action="">
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-username"
                           for="LAY-user-login-username"></label>
                    <input type="text" name="username" id="LAY-user-login-username" lay-verify="required|username"
                           placeholder="用户名" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                           for="LAY-user-login-password"></label>
                    <input type="password" name="password" id="LAY-user-login-password" lay-verify="required"
                           placeholder="密码" class="layui-input">
                </div>

                <div class="layui-form-item">
                    <button type="button" class="layui-btn layui-btn-fluid" lay-submit
                            lay-filter="LAY-user-login-submit">登 入
                    </button>
                </div>

            </form>
        </div>
    </div>


</div>

<script src="/js/layui/layui.js"></script>
<script src="/js/jquery1.12.1.js"></script>
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var form = layui.form;
        var layer = layui.layer;

        form.on('submit(LAY-user-login-submit)', function (data) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            $.post("{{ route('admin.login.post.white') }}", data.field,
                function (data) {
                    if (data.code === 0) {
                        layer.msg('登入成功', {
                            offset: '15px'
                            , icon: 1
                            , time: 1000
                        }, function () {
                            location.href = '{{ route('admin.index.white') }}';
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
</body>
</html>
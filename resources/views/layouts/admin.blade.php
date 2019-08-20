<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>管理后台</title>
    <link rel="stylesheet" href="/js/layui/css/layui.css">
    @yield('style')
</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">管理后台</div>

        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="javascript:;">
                  ID:{{ session('user')['id'] }} {{ session('user')['email'] }}
                </a>
                <dl class="layui-nav-child">
                    <dd>
                        <a href="{{ route('admin.logout.white') }}"
                           onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            退出
                        </a>

                        <form id="logout-form" action="{{ route('admin.logout.white') }}" method="POST"
                              style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </dd>
                </dl>
            </li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree" lay-filter="test">
                @foreach($menu_tree as $menu)
                    <li class="layui-nav-item
                       @if(key_exists('children',$menu) && in_array($currRouteName,array_column($menu['children'],'route')))
                            layui-nav-itemed
                        @endif
                            ">
                        @if($menu['route']=='admin.order.apply')
                             <a class="" href="{{ route($menu['route']) }}">{{ $menu['name'] }}</a>
                        @else
                            <a class="" href="javascript:;">{{ $menu['name'] }}</a>
                        @endif

                        <dl class="layui-nav-child">
                            @if(key_exists('children',$menu))
                                @foreach($menu['children'] as $child)
                                    <dd
                                            @if($currRouteName == $child['route'])
                                            class="layui-this"
                                            @endif
                                    ><a href="{{ route($child['route']) }}">{{ $child['name'] }}</a></dd>
                                @endforeach
                            @endif
                        </dl>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="layui-body">
        <!-- 内容主体区域 -->
        <div style="padding: 15px;">
            @yield('content')
        </div>
    </div>

</div>
<script src="/js/layui/layui.js"></script>
<script src="/js/jquery1.12.1.js"></script>
<script>
    //JavaScript代码区域
    layui.use('element', function () {
        var element = layui.element;

    });
</script>
@yield('script')
</body>
</html>
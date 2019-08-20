@extends('layouts.admin')

@section('content')
    <div class="layui-row layui-col-space10" style="text-align: center;margin-top: 130px;">
        <div class="layui-col-md4">

        </div>
        <div class="layui-col-md4">
            <i class="layui-icon layui-icon-password" style="font-size: 30px;"></i> <span style="font-size: 28px; font-weight: lighter; color:#666;padding-left: 10px;">您的账号已被其他用户登陆</span>
            <a href="{{ route('admin.logout.white') }}"  onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();"> <span style="font-size: 28px; font-weight: lighter; color:#01AAED;padding-left: 10px;">重新登陆</span></a>
        </div>
        <div class="layui-col-md4">
        </div>
    </div>
@endsection
<?php
/**
 * User:
 * Date: 2019/1/28 下午9:20
 */

namespace App\Validate;


use App\Http\Models\Users;
use Illuminate\Validation\Rule;

class UserUpdateValidate extends BaseValidate
{
    protected $rules = [];

    protected $message = [
        'id.required'            => 'ID参数不能为空',
        'id.numeric'             => 'ID参数错误',
        'realname.required'            => '请输入姓名',
        'username.required'            => '请输入用户名',
        'username.max'                 => '姓名最多20个字符',
        'password.between'       => '密码长度为6-20个字符',
        'password_repeat.same'   => '两次填写的密码不一致',
        'status.required'        => '请选择状态',
        'status.in'              => '状态值不正确',
        'administrator.required' => '请选择是否管理员',
        'administrator.in'       => '管理员参数不正确',
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->rules = [
            'id'              => 'required|numeric',
            'password'        => 'nullable|between:6,20',
            'password_repeat' => 'nullable|same:password',
            //'status'          => ['required', Rule::in([1, 2])],
            'administrator'   => ['required', Rule::in([1, 2])],
            'permission'           => 'sometimes'
        ];
    }

    protected function customValidate()
    {
        $roles         = $this->requestData['permission'] ?? '';
        $administrator = $this->requestData['administrator'];
        $id            = $this->requestData['id'];
        $username         = $this->requestData['username'];

        if ($administrator == Users::ADMIN_NO && !$roles) {
//            $this->validator->errors()->add('permission', '请选择所属角色');
//            return false;
        }

        if (!Users::find($id)) {
            $this->validator->errors()->add('id', 'ID参数有误');
            return false;
        }

        if (Users::where('id', '!=', $id)->where('username', '=', $username)->count() > 0) {
            $this->validator->errors()->add('name', '用户名已存在');
            return false;
        }
    }
}
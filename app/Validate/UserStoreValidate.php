<?php
/**
 * User:
 * Date: 2019/1/28 下午9:20
 */

namespace App\Validate;


use App\Http\Models\Users;
use Illuminate\Validation\Rule;

class UserStoreValidate extends BaseValidate
{
    protected $rules = [];

    protected $message = [
        'realname.required'            => '请输入姓名',
        'username.required'            => '请输入用户名',
        'username.max'                 => '姓名最多20个字符',
        'password.required'        => '请输入密码',
        'password.between'         => '密码长度为6-20个字符',
        'password_repeat.required' => '请输入确认密码',
        'password_repeat.same'     => '两次填写的密码不一致',
        'status.required'          => '请选择状态',
        'status.in'                => '状态值不正确',
        'administrator.required'   => '请选择是否管理员',
        'administrator.in'         => '管理员参数不正确',
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->rules = [
//            'username'           => 'required|username|unique:admin_users,username',
            'password'        => 'required|between:6,20',
            'password_repeat' => 'required|same:password',
            'status'          => ['required', Rule::in([1, 2])],
            'administrator'   => ['required', Rule::in([1, 2])],
            'permission'           => 'sometimes'
        ];
    }

    protected function customValidate()
    {
        $roles         = $this->requestData['permission'] ?? '';
        $administrator = $this->requestData['administrator'];

        if ($administrator == Users::ADMIN_NO && !$roles) {
         //   $this->validator->errors()->add('permission', '请选择所属角色');
           // return false;
        }
    }
}
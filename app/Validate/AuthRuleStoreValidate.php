<?php
/**
 * User:
 * Date: 2019/1/28 下午9:20
 */

namespace App\Validate;


use App\Http\Models\AuthRule;
use App\Service\RouteService;

class AuthRuleStoreValidate extends BaseValidate{
    protected $rules = [];

    protected $message = [
        'title.unique'    => '路由名称已存在',
        'title.required'  => '请输入名称',
        'title.max'       => '名称最长20个字符',
        'name.unique'    => '路由已存在',
        'name.required' => '请输入路由',
        'pid.required'   => '请选择父级菜单',
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->rules = [
            'title'  => 'required|unique:auth_rule,name|max:20',
            'name' => 'nullable',
            'pid'   => 'required',
        ];
    }

    protected function customValidate()
    {
        $pid   = $this->requestData['pid'];
        $route = $this->requestData['name'];

        if ($pid < 0) {
            $this->validator->errors()->add('pid', '父级菜单参数不正确');
            return false;
        } elseif ($pid > 0) {
            if (!AuthRule::find($pid)) {
                $this->validator->errors()->add('pid', '父级菜单不存在');
                return false;
            }
        }

        if ($pid != 0) {
            $routes = RouteService::getRoutes();
            if (!in_array($route, $routes)) {
                $this->validator->errors()->add('route', '路由标识不存在');
                return false;
            }
        }

    }
}
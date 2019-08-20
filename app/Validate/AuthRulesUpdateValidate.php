<?php
/**
 * User:
 * Date: 2019/1/28 下午9:20
 */

namespace App\Validate;


use App\Http\Models\AuthRule;
use App\Service\RouteService;

class AuthRulesUpdateValidate extends BaseValidate
{
    protected $rules = [];

    protected $message = [
        'id.required'    => 'ID参数不存在',
        'id.numeric'     => 'ID参数不正确',
        'title.required'  => '请输入名称',
        'title.max'       => '名称最长20个字符',
        'name.required' => '请输入路由',
        'pid.required'   => '请选择父级菜单',
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->rules = [
            'id'    => 'required|numeric',
            'title'  => 'required|max:20',
            'name' => 'nullable',
            'pid'   => 'required',
        ];
    }

    protected function customValidate()
    {
        $id    = $this->requestData['id'];
        $title  = $this->requestData['title'];
        $pid   = $this->requestData['pid'];
        $name = $this->requestData['name'];

        if ($pid < 0) {
            $this->validator->errors()->add('pid', '父级路由参数不正确');
            return false;
        } elseif ($pid > 0) {
            if (!AuthRule::find($pid)) {
                $this->validator->errors()->add('pid', '父级路由不存在');
                return false;
            }
        }

        if (!AuthRule::find($id)) {
            $this->validator->errors()->add('id', '路由信息不正确');
            return false;
        }

        if (AuthRule::where('id', '!=', $id)->where('title', '=', $title)->count() > 0) {
            $this->validator->errors()->add('name', '该名称已存在');
            return false;
        }

        if ($pid != 0) {
            $routes = RouteService::getRoutes();
            if (!in_array($name, $routes)) {
                $this->validator->errors()->add('route', '路由标识不存在');
                return false;
            }
        }

    }
}
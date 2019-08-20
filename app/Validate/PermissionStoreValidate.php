<?php
/**
 * User:
 * Date: 2019/1/28 下午9:20
 */

namespace App\Validate;


use App\Service\RouteService;

class PermissionStoreValidate extends BaseValidate
{
    protected $rules = [];

    protected $message = [
        'name.unique'    => '名称已存在',
        'name.required'  => '请输入名称',
        'name.max'       => '名称最长20个字符',
        'route.required' => '请选择路由',
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->rules = [
            'name'  => 'required|unique:permission,name|max:20',
            'route' => 'required',
        ];
    }

    protected function customValidate()
    {

    }
}
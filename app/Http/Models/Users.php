<?php
/**
 * User:
 * Date: 2019/5/5 下午7:58
 */

namespace App\Http\Models;


use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'admin_users';

    protected $guarded = [];

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 2;

    const ADMIN_YES = 1;
    const ADMIN_NO = 2;

    public function permission()
    {
        return $this->belongsToMany('App\Http\Models\Permission','users_permission');
    }
}
<?php

namespace App\Http\Models;

use App\Service\RouteService;
use Illuminate\Database\Eloquent\Model;

class AuthRule extends Model
{
    //
    protected $table = 'auth_rule';
    public $timestamps = false;
    protected $guarded = [];

    public static function menuTree(&$all_meuns, &$tree)
    {
        foreach($all_meuns as $key=>$menu){
            if(isset($all_meuns[$menu['pid']])){
                $all_meuns[$menu['pid']]['children'][] = &$all_meuns[$key];
            }else{
                $tree[] = &$all_meuns[$menu['id']];
            }
        }
    }

}

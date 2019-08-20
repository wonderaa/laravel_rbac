<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//以.white结尾的别名为不需要授权的路由

Route::namespace('Admin')->prefix('admin')->group(function () {
    Route::get('login', 'LoginController@index')->name('admin.login.white');
    Route::post('login', 'LoginController@login')->name('admin.login.post.white');
    Route::get('test', 'LoginController@test')->name('admin.test');

    Route::middleware(['login', 'menu'])->group(function () {
        Route::get('/', 'AdminController@index')->name('admin.index.white');
        Route::post('logout', 'LoginController@logout')->name('admin.logout.white');
        Route::get('modify_pwd', 'AdminController@modifyPwd')->name('admin.modify_pwd.white');
        Route::post('new_pwd', 'AdminController@newPwd')->name('admin.new_pwd.white');
        Route::get('forbidden', function () {return view('admin.403');})->name('admin.forbidden.white');
        Route::get('forbidden', function () {return view('admin.otherlogin');})->name('admin.forbidden.otherlogin');
        Route::middleware('auth.can')->group(function () {

            Route::get('/user', 'UserController@index')->name('admin.user.index');
            Route::get('/user/create', 'UserController@create')->name('admin.user.create');
            Route::post('/user/store', 'UserController@store')->name('admin.user.store');
            Route::post('/user/status', 'UserController@status')->name('admin.user.status');
            Route::get('/user/edit', 'UserController@edit')->name('admin.user.edit');
            Route::post('/user/update', 'UserController@update')->name('admin.user.update');
            Route::post('/user/reset', 'UserController@reset')->name('admin.user.reset');

            Route::get('/permission', 'PermissionController@index')->name('admin.permission.index');
            Route::get('/permission/create', 'PermissionController@create')->name('admin.permission.create');
            Route::post('/permission/store', 'PermissionController@store')->name('admin.permission.store');
            Route::get('/permission/edit', 'PermissionController@edit')->name('admin.permission.edit');
            Route::post('/permission/update', 'PermissionController@update')->name('admin.permission.update');
            Route::post('/permission/delete', 'PermissionController@delete')->name('admin.permission.delete');

            Route::get('/roles', 'RolesController@index')->name('admin.roles.index');
            Route::get('/roles/create', 'RolesController@create')->name('admin.roles.create');
            Route::post('/roles/store', 'RolesController@store')->name('admin.roles.store');
            Route::get('/roles/edit', 'RolesController@edit')->name('admin.roles.edit');
            Route::post('/roles/update', 'RolesController@update')->name('admin.roles.update');
            Route::post('/roles/delete', 'RolesController@delete')->name('admin.roles.delete');

            Route::get('/menu', 'MenuController@index')->name('admin.menu.index');
            Route::get('/menu/create', 'MenuController@create')->name('admin.menu.create');
            Route::post('/menu/store', 'MenuController@store')->name('admin.menu.store');
            Route::get('/menu/edit', 'MenuController@edit')->name('admin.menu.edit');
            Route::post('/menu/update', 'MenuController@update')->name('admin.menu.update');
            Route::post('/menu/delete', 'MenuController@delete')->name('admin.menu.delete');

            //添加权限列表
            Route::get('/rules','RulesController@index')->name('admin.rules.index');
            Route::get('/rules/create','RulesController@create')->name('admin.rules.create');
            Route::post('/rules/store','RulesController@store')->name('admin.rules.store');
            Route::get('/rules/edit','RulesController@edit')->name('admin.rules.edit');
            Route::post('/rules/update','RulesController@update')->name('admin.rules.update');
            Route::post('/rules/delete','RulesController@delete')->name('admin.rules.delete');
            //接单管理
            Route::get('/order/apply','OrderController@apply')->name('admin.order.apply');
            //订单管理
            //全部订单
            Route::get('/order','OrderController@index')->name('admin.order.index');
            //待处理订单
            Route::get('/order/wait','OrderController@wait')->name('admin.order.wait');
            //处理成功订单
            Route::get('/order/disposed','OrderController@disposed')->name('admin.order.disposed');
            //处理失败订单
            Route::get('/order/failed','OrderController@failed')->name('admin.order.failed');
            //支付宝订单记录
            Route::get('/order/alipay_record','OrderController@alipay_record')->name('admin.order.alipay_record');
            //银行卡订单记录
            Route::get('/order/bank_record','OrderController@bank_record')->name('admin.order.bank_record');
            //个人出款记录
            Route::get('/order/self_record','OrderController@self_record')->name('admin.order.self_record');
            Route::get('/order/test','OrderController@test')->name('admin.order.test');
            //手动接单
            Route::middleware(['throttle:10,1'])->group(function(){
                Route::post('/order/hand_receive_order','OrderController@hand_receive_order')->name('admin.order.hand_receive_order');
                Route::post('/order/receive_order','OrderController@receive_order')->name('admin.order.receive_order');
            });

            //第三方后台除款管理
            Route::get('/widthdraw/bind_bank_card','WidthdrawController@bind_bank_card')->name('admin.widthdraw.bind_bank_card');
            Route::post('/widthdraw/store','WidthdrawController@store')->name('admin.widthdraw.store');
            Route::post('/widthdraw/get_bank_info','WidthdrawController@get_bank_info')->name('admin.widthdraw.get_bank_info');
            Route::post('/widthdraw/remit_act','WidthdrawController@remit_act')->name('admin.widthdraw.remit_act');
            //出款
            Route::get('/widthdraw/index','WidthdrawController@index')->name('admin.widthdraw.index');
            Route::post('/order/reset_order_state','OrderController@reset_order_state')->name('admin.order.reset_order_state');
            Route::post('/order/get_user_info','OrderController@get_user_info')->name('admin.order.get_user_info');
            Route::post('/order/get_user_info','OrderController@get_user_info')->name('admin.order.get_user_info');
            Route::post('/order/return_order','OrderController@return_order')->name('admin.order.return_order');
            Route::post('/order/return_system','OrderController@return_system')->name('admin.order.return_system');
            Route::post('/order/transfer_hand_zfb','OrderController@transfer_hand_zfb')->name('admin.order.transfer_hand_zfb');
            Route::post('/order/transfer_hand_bank','OrderController@transfer_hand_bank')->name('admin.order.transfer_hand_bank');
            Route::post('/order/transfer_zfb_auto','OrderController@transfer_zfb_auto')->name('admin.order.transfer_zfb_auto');
            Route::post('/order/transfer_bank_auto','OrderController@transfer_bank_auto')->name('admin.order.transfer_bank_auto');
            //充值订单
            Route::get('recharge/index','RechargeController@index')->name('admin.recharge.index');
            Route::post('recharge/store','RechargeController@store')->name('admin.recharge.store');
            //充值记录
            Route::get('recharge/recharge_record','RechargeController@recharge_record')->name('admin.recharge.recharge_record');
            Route::post('recharge/cancel_give_money','RechargeController@cancel_give_money')->name('admin.recharge.cancel_give_money');
            //老板充值记录
            Route::get('recharge/game_recharge','RechargeController@game_recharge')->name('admin.recharge.game_recharge');
            Route::get('recharge/oldstore','RechargeController@oldstore')->name('admin.recharge.oldstore');
            Route::get('recharge/old_recharge_record','RechargeController@old_recharge_record')->name('admin.recharge.old_recharge_record');
            Route::get('recharge/reduce_money','RechargeController@reduce_money')->name('admin.recharge.reduce_money');
            Route::get('recharge/reduce_record','RechargeController@reduce_record')->name('admin.recharge.reduce_record');
            //老板充值记录

            //顶级菜单虚拟路由
            Route::get('menu/menu_order','MenuController@menu_order')->name('admin.menu.menu_order');
            Route::get('menu/menu_permission','MenuController@menu_permission')->name('admin.menu.menu_permission');
            Route::get('menu/menu_viprecharge','MenuController@menu_viprecharge')->name('admin.menu.menu_viprecharge');
            Route::get('menu/menu_oldviprecharge','MenuController@menu_oldviprecharge')->name('admin.menu.menu_oldviprecharge');
            Route::get('menu/menu_widthdraw','MenuController@menu_widthdraw')->name('admin.menu.menu_widthdraw');


            Route::get('forbidden', function () {return view('admin.403');})->name('admin.forbidden.white');
        });
    });
});



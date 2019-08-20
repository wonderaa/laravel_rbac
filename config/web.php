<?php
return [
    //list分页条数
    'page_size' => 20,
    //重置订单google验证key
    'google_reset_order_key'=>'JAGBJ36GSBMJ24UW',
    //支付宝出款编号
    'zfb_num'=>[
        1=>'手工转款',
        2=>'一号支付宝',
        3=>'二号支付宝',
        4=>'三号支付宝'
    ],
    'bank_num'=>[
        1=>'手工转款',
        2=>'易支宝',
        3=>'KKBANK',
        4=>'艾付'
    ],
    //支付宝账号
    'zfb_config'=>[
        'app_id_2'=>'',//@163.com
        'app_id_3'=>'', //@163.com
        'app_id_4'=>'',//@163.com
        'sign_type'=>'RSA2',
        'private_key'=>'',

    ],
    //艾付 支持银行列表
    'aifu_bink_list'=>[
        '中国工商银行'=>'ICBC',
        '中国农业银行'=>'ABC',
        '中国建设银行'=>'CCB',
        '中国银行'=>'BOC',
        '中国邮政储蓄银行'=>'PSBC',
        '中国光大银行'=>'CEB',
        '中国民生银行'=>'CMBC',
        '招商银行'=>'CMB',
        '交通银行'=>'BOCOM',
        '中信银行'=>'CNCB',
        '浦发银行'=>'SPDB',
        '广发银行'=>'GDB',
        '华夏银行'=>'HXB',
        '兴业银行'=>'CIB',
        '平安银行'=>'PAB',
    ],
    //艾付 App id
    'ai_app_id'=>'',
    'ai_app_key'=>'',
    'ai_pay_url'=>'',
    'ai_query_url'=>'',

    //KKbank 支持银行列表
    'kkfu_bink_list'=>[
        '中国农业银行'=>'ABC',
        '中国银行'=>'BOC',
        '中国建设银行'=>'CCB',
        '中国光大银行'=>'CEB',
        '兴业银行'=>'CIB',
        '中信银行'=>'ECITIC',
        '招商银行'=>'CMBCHINA',
        '中国民生银行'=>'CMBC',
        '交通银行'=>'BOCO',
        '广发银行'=>'GDB',
        '华夏银行'=>'HXB',
        '中国工商银行'=>'ICBC',
        '中国邮政储蓄银行'=>'POST',
        '平安银行'=>'PINGANBANK',
        '浦发银行'=>'SPDB',
    ],
    //VIP充值key
    'web2gm_salt'=>'web2gm_salt',
];
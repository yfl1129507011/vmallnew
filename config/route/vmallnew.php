<?php
$routes[] = array(
    'match' => "#.*#",
    'route' => array(
        'controller' => "Index",
        'action' => "index"
    ),
    'map' => array(),
);

$routes[] = array(
    'match' => "#/(?)$#",
    'route' => array(
        'controller' => "Index",
        'action' => "index"
    ),
    'map' => array(),
);
# Index控制器的路由
$routes['Index'] = array(
    'index'=>array(
        'match' => '#/index$#',
        'map' => array(),
    ),
    'message'=>array(
        'match' => '#/message/([1|2]{1})$#',
        'map' => array(
            1=>'topic',
        ),
    ),
    'read'=>array(
        'match' => '#/read$#',
        'map' => array(),
    ),
);

$routes['Public'] = array(
    'login'=>array(
        'match' => '#/login$#',
        'map' => array(),
    ),
    'logout'=>array(
        'match' => '#/logout$#',
        'map' => array(),
    ),
    'captcha'=>array(
        'match' => '#/captcha$#',
        'map' => array(),
    ),
    'tips'=>array(
        'match' => '#/tips$#',
        'map' => array(),
    ),
);

$routes['Goods'] = array(
    'product_index'=>array(
        'match' => '#/product$#',
        'map' => array(),
    ),
);


# 格式化路由信息
$conf['regex'] = [];
foreach ($routes as $_controller=>$v){
    if (is_numeric($_controller)){
        $v['route']['controller'] = APP_NAME.'_'.ucfirst($v['route']['controller']);
        $conf['regex'][] = $v;
    }else{
        if (!is_array($v)) continue;
        foreach ($v as $_action=>$_v){
            $_v['route']['controller'] = APP_NAME.'_'.ucfirst($_controller);
            $_v['route']['action'] = $_action;
            $conf['regex'][] = $_v;
        }
    }
}

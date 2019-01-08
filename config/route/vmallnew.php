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
    # 商品管理
    'product_index'=>array(
        'match' => '#/product$#',
        'map' => array(),
    ),
    'product_list'=>array(
        'match' => '#/product/list$#',
        'map' => array(),
    ),
    'product_add'=>array(
        'match' => '#/product/add$#',
        'map' => array(),
    ),
    'product_trash'=>array(
        'match' => '#/product/trash$#',
        'map' => array(),
    ),
    'product_modify'=>array(
        'match' => '#/product/modify$#',
        'map' => array(),
    ),
    'product_bulk'=>array(
        'match' => '#/product/bulk$#',
        'map' => array(),
    ),
    'product_listing'=>array(
        'match' => '#/product/listing$#',
        'map' => array(),
    ),
    'product_field'=>array(
        'match' => '#/product/field$#',
        'map' => array(),
    ),
    'product_del'=>array(
        'match' => '#/product/del$#',
        'map' => array(),
    ),
    'product_remove'=>array(
        'match' => '#/product/remove$#',
        'map' => array(),
    ),

    # 分类管理
    'cat_index'=>array(
        'match' => '#/cat$#',
        'map' => array(),
    ),
    'cat_list'=>array(
        'match' => '#/cat/list$#',
        'map' => array(),
    ),
    'cat_hot'=>array(
        'match' => '#/cat/hot$#',
        'map' => array(),
    ),
    'cat_del'=>array(
        'match' => '#/cat/del$#',
        'map' => array(),
    ),
    'cat_modify'=>array(
        'match' => '#/cat/modify$#',
        'map' => array(),
    ),
    'cat_update'=>array(
        'match' => '#/cat/update$#',
        'map' => array(),
    ),

    # 标签管理
    'tag_index'=>array(
        'match' => '#/tag$#',
        'map' => array(),
    ),
    'tag_list'=>array(
        'match' => '#/tag/list$#',
        'map' => array(),
    ),
    'tag_modify'=>array(
        'match' => '#/tag/modify$#',
        'map' => array(),
    ),
    'tag_update'=>array(
        'match' => '#/tag/update$#',
        'map' => array(),
    ),
    'tag_del'=>array(
        'match' => '#/tag/del$#',
        'map' => array(),
    ),

    # 评价管理
    'rate_index'=>array(
        'match' => '#/rate$#',
        'map' => array(),
    ),
    'rate_list'=>array(
        'match' => '#/rate/list$#',
        'map' => array(),
    ),
    'rate_del'=>array(
        'match' => '#/rate/del$#',
        'map' => array(),
    ),
    'rate_show'=>array(
        'match' => '#/rate/show$#',
        'map' => array(),
    ),
    'rate_handle'=>array(
        'match' => '#/rate/handle$#',
        'map' => array(),
    ),
    'rate_modify'=>array(
        'match' => '#/rate/modify$#',
        'map' => array(),
    ),

    # 模板管理
    'poster_index'=>array(
        'match' => '#/poster$#',
        'map' => array(),
    ),
    'poster_list'=>array(
        'match' => '#/poster/list$#',
        'map' => array(),
    ),
    'poster_del'=>array(
        'match' => '#/poster/del$#',
        'map' => array(),
    ),
    'poster_open'=>array(
        'match' => '#/poster/open$#',
        'map' => array(),
    ),
    'poster_modify'=>array(
        'match' => '#/poster/modify$#',
        'map' => array(),
    ),
    'poster_update'=>array(
        'match' => '#/poster/update$#',
        'map' => array(),
    ),

);

$routes['Trade'] = array(
    'index'=>array(
        'match' => '#/trade$#',
        'map' => array(),
    ),
    'list'=>array(
        'match' => '#/trade/list$#',
        'map' => array(),
    ),
    'detail'=>array(
        'match' => '#/trade/detail/(\d+)$#',
        'map' => array(
            1 => 'tid',
        ),
    ),
    'delivery'=>array(
        'match' => '#/trade/delivery$#',
        'map' => array(),
    ),
    'check'=>array(
        'match' => '#/trade/check#',
        'map' => array(),
    ),
    'confirm'=>array(
        'match' => '#/trade/confirm#',
        'map' => array(),
    ),
);

$routes['Sales'] = array(
    'crm_index'=>array(
        'match' => '#/crm$#',
        'map' => array(),
    ),
    'crm_list'=>array(
        'match' => '#/crm/list$#',
        'map' => array(),
    ),
    'crm_modify'=>array(
        'match' => '#/crm/modify$#',
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

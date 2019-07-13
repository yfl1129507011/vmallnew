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
    'product_update'=>array(
        'match' => '#/product/update$#',
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
    'crm_discount'=>array(
        'match' => '#/crm/discount/(\d*)$#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'crm_update'=>array(
        'match' => '#/crm/update$#',
        'map' => array(),
    ),
    'crm_del'=>array(
        'match' => '#/crm/del$#',
        'map' => array(),
    ),
    'gift_index'=>array(
        'match' => '#/gift$#',
        'map' => array(),
    ),
    'gift_list'=>array(
        'match' => '#/gift/list$#',
        'map' => array(),
    ),
    'gift_desc'=>array(
        'match' => '#/gift/desc/(\d+)$#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'card_list'=>array(
        'match' => '#/card/list$#',
        'map' => array(),
    ),
    'card_update'=>array(
        'match' => '#/card/update$#',
        'map' => array(),
    ),
    'card_del'=>array(
        'match' => '#/card/del/(\d+)#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'bless_list'=>array(
        'match' => '#/bless/list$#',
        'map' => array(),
    ),
    'bless_del'=>array(
        'match' => '#/bless/del/(\d+)#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'bless_update'=>array(
        'match' => '#/bless/update$#',
        'map' => array(),
    ),
    'distribution_index'=>array(
        'match' => '#/distribution$#',
        'map' => array(),
    ),
    'distribution_list'=>array(
        'match' => '#/distribution/list$#',
        'map' => array(),
    ),
);

$routes['Config'] = array(
    'base_index'=>array(
        'match' => '#/base$#',
        'map' => array(),
    ),
    'base_mall'=>array(
        'match' => '#/base/mall$#',
        'map' => array(),
    ),
    'base_save'=>array(
        'match' => '#/base/save$#',
        'map' => array(),
    ),
    'banner_list'=>array(
        'match' => '#/banner/list$#',
        'map' => array(),
    ),
    'banner_modify'=>array(
        'match' => '#/banner/modify$#',
        'map' => array(),
    ),
    'banner_update'=>array(
        'match' => '#/banner/update$#',
        'map' => array(),
    ),
    'banner_del'=>array(
        'match' => '#/banner/del/(\d+)#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'tpl_index'=>array(
        'match' => '#/tpl$#',
        'map' => array(),
    ),
    'tpl_list'=>array(
        'match' => '#/tpl/list$#',
        'map' => array(),
    ),
    'tpl_modify'=>array(
        'match' => '#/tpl/modify$#',
        'map' => array(),
    ),
    'tpl_update'=>array(
        'match' => '#/tpl/update$#',
        'map' => array(),
    ),
    'tpl_del'=>array(
        'match' => '#/tpl/del/(\d+)#',
        'map' => array(
            1 => 'id',
        ),
    ),
    'pay_index'=>array(
        'match' => '#/pay$#',
        'map' => array(),
    ),
    'pay_info'=>array(
        'match' => '#/pay/info$#',
        'map' => array(),
    ),
    'pay_edit'=>array(
        'match' => '#/pay/edit$#',
        'map' => array(),
    ),
);


$routes['Test'] = array(
    'index'=>array(
        'match' => '#/test$#',
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

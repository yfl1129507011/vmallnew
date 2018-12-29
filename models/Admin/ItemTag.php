<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:07
 */
class Admin_ItemTagModel extends VmallNewModel {
    protected $cls = '/item-tag';

    /**
     * @param array $params
     * @return bool
     * 获取商品标签信息
     */
    public function getTags(array $params){
        $url = $this->getUrl('',$params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }
}
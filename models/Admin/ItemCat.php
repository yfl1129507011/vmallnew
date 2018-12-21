<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/20
 * Time: 11:28
 */
class Admin_ItemCatModel extends VmallNewModel{
    protected $cls = '/item-cat';

    public function getCat(array $params){
        $url = $this->getUrl('',$params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }
}
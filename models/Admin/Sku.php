<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:31
 */
class Admin_SkuModel extends VmallNewModel {
    protected $cls = '/sku';

    /**
     * @return bool
     * 获取商品规格名称
     */
    public function getNames(){
        $url = $this->getV2Url('/name');
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $res['results'];
        }
        return false;
    }

    /**
     * @return bool
     * 获取商品规格值
     */
    public function getValues(){
        $url = $this->getV2Url('/value');
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $res['results'];
        }
        return false;
    }
}
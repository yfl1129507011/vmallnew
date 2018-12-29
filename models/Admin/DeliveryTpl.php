<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:41
 * 运费模板
 */
class Admin_DeliveryTplModel extends VmallNewModel{
    protected $cls = '/delivery';

    public function getTpls(array $params){
        $url = $this->getV2Url('', $params);
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $res['results'];
        }
        return false;
    }
}
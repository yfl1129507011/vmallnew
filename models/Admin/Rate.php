<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:31
 */
class Admin_RateModel extends VmallNewModel {
    protected $cls = '/rate';

    /**
     * @param array $params
     * @return bool
     * 获取评价信息
     */
    public function getRates(array $params){
        if (empty($params)) return false;
        $url = $this->getUrl('', $params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $data
     * @return bool
     * 删除评价
     */
    public function rateDel(array $data){
        if (empty($data)) return false;
        $url = $this->getUrl();
        $res = $this->curl($url,'delete',$data);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $data
     * @return array|bool
     * 显示或隐藏评价
     */
    public function rateShow(array $data){
        if (empty($data)) return false;
        $url = $this->getUrl('/modify-show');
        $res = $this->curl($url,'put',$data);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $data
     * @return array|bool
     * 评价处理
     */
    public function rateHandle(array $data){
        if (empty($data)) return false;
        $url = $this->getUrl();
        $res = $this->curl($url,'put',$data);
        return $this->checkApiResult($res, $url);
    }


}
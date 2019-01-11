<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:50
 */
class Admin_CrmDiscountModel extends VmallNewModel{
    protected $cls = '/crm-discount';

    /**
     * @param array $data
     * @return array
     * 格式化数据
     */
    private function formatList(array $data){
        if (empty($data) || empty($data['results']) || !is_array($data['results'])) return $data;
        $results = $data['results'];
        $_results = array();
        foreach ($results as $k=>$v){
            $levelDiscount = explode(',',$v['levelDiscount']);
            $v['levelDiscount_arr'] = array_map(function ($_v){
                return explode(':',$_v);
            },$levelDiscount);
            $_results[$k] = $v;
        }
        $_results = array_map(function ($_val){
            $levelDiscount_arr = $_val['levelDiscount_arr'];
            $discountArr = array_column($levelDiscount_arr, 2);
            $_val['discount_max'] = $levelDiscount_arr[array_search(min($discountArr),$discountArr)];
            $_val['discount_min'] = $levelDiscount_arr[array_search(max($discountArr),$discountArr)];
            return $_val;
        }, $_results);
        $data['results'] = $_results;
        return $data;
    }

    /**
     * @param array $query
     * @return array|bool
     * 获取会员折扣列表
     */
    public function getList(array $query){
        $url = $this->getV2Url('',$query);
        $res = $this->curl($url);
        if($this->checkApiResult($res,$url)){
            return $this->formatList($res);
        }
        return false;
    }

    /**
     * @param null $id
     * @param array $query
     * 获取折扣和非折扣商品信息
     * @return array|bool
     */
    public function getDiscount($id=null, array $query=array()){
        $module = empty($id)?'/items':'/'.$id.'/items';
        $url = $this->getV2Url($module, $query);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }
}
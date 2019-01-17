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

    /**
     * @param $id
     * @param array $body
     * @return array|bool
     * 编辑会员折扣
     */
    public function editDiscount($id, array $body){
        if (empty($id) || empty($body)) return false;
        $url = $this->getV2Url('/'.$id);
        $res = $this->curl($url, 'put', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $body
     * @return array|bool
     * 添加会员折扣
     */
    public function addDiscount(array $body){
        if (empty($body)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url, 'post', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $data
     * @return array
     * 处理会员折扣的添加和修改操作
     */
    public function update(array $data){
        $returnData = array();
        $returnData['code'] = 200;
        if ( empty($data) || empty($data['crm_name']) ||
            empty($data['discs']) || empty($data['levels']) ||
            empty($data['items']) ||
            count($data['discs'])!=count($data['levels']) ) {
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少必要参数';
            return $returnData;
        }
        $upData = array(); $levelDiscount = array();
        foreach ($data['discs'] as $k=>$v){
            if (floatval($v)>10 || floatval($v)<1) continue;
            $levelDiscount[] = str_replace('-',':',$data['levels'][$k]).':'.round($v/10,2);
        }
        if(empty($levelDiscount)) {
            $returnData['code'] = 402;
            $returnData['msg'] = '参数错误';
            return $returnData;
        };
        $upData['levelDiscount'] = implode(',', $levelDiscount);
        $upData['name'] = $this->formatLen($data['crm_name'], 6);
        $upData['items'] = trim($data['items']);
        $id = (int)$data['crm_id'];
        if($id){ # 更新
            $upData['id'] = $id;
            $res = $this->editDiscount($id, $upData);
        }else{ # 添加
            $res = $this->addDiscount($upData);
        }

        if($res){
            $returnData['msg'] = '操作成功';
        }else{
            $returnData['code'] = 400;
            $returnData['msg'] = '操作失败';
        }
        return $returnData;
    }

    /**
     * @param $id
     * @return array|bool
     * 删除折扣
     */
    public function delDiscount($id){
        if (empty($id)) return false;
        $url = $this->getV2Url('/'.$id);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res,$url);
    }
}
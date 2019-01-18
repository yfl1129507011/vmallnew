<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/18
 * Time: 11:38
 */
class Admin_GiftGivingModel extends VmallNewModel{
    protected $cls = '/gift-giving';
    public static $statusArr = array(
        '0'=>'待支付',
        '1'=>'待领取',
        '2'=>'已收礼',
        '-1'=>'退款',
    );

    /**
     * @param array $data
     * @return array
     * 数据格式化
     */
    public function formatList(array $data){
        if(!empty($data) && !empty($data['results'])) {
            $results = array();
            foreach ($data['results'] as $k => $v) {
                $v['status_name'] = self::$statusArr[$v['status']];
                $results[$k] = $v;
            }
            $data['results'] = $results;
        }
        return $data;
    }

    /**
     * @param array $query
     * @return array|bool
     * 获取礼品列表
     */
    public function getList(array $query){
        $url = $this->getV2Url('', $query);
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url)){
            return $this->formatList($res);
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     * 单个礼品信息
     */
    public function oneGift($id){
        if (empty($id)) return false;
        $url = $this->getV2Url('/'.$id);
        $res = $this->curl($url);
        if ($this->checkApiResult($res,$url)){
            return $res['results'];
        }
        return false;
    }

    /**
     * @param $id
     * @return array
     * 礼品信息
     */
    public function giftDesc($id){
        $res = $this->oneGift($id);
        if($res){
            $baseData = array();
            $baseData['礼品ID'] = $res['id'];
            $baseData['购买用户'] = $res['buyerName'];
            $baseData['付款时间'] = date("Y-m-d H:i:s",$res['giftTs']/1000);
            if($res['status']==-1){
                $baseData['退款金额'] = $res['payment'];
            }
            if ($res['receiveTs']){
                $baseData['领取时间'] =date("Y-m-d H:i:s",$res['receiveTs']/1000);
            }
            if ($res['refundTs']){
                $baseData['退款时间'] =date("Y-m-d H:i:s",$res['refundTs']/1000);
            }
            $res['base_info'] = array_chunk($baseData, 3, true);
        }
        return $res;
    }
}
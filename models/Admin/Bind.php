<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:50
 */
class Admin_BindModel extends VmallNewModel{
    protected $cls = '/bind';

    /**
     * @param array $query
     * @return array|bool
     * 获取绑定信息
     */
    public function getInfo(){
        $url = $this->getUrl('/pay');
        $res = $this->curl($url);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $id
     * @param array $data
     * @return array|bool
     * 编辑
     */
    public function edit(array $data){
        $returnData = $upData = array();
        $returnData['code'] = 200;
        if (empty($data) || empty($data['type']) || empty($data['api_key'])){
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少参数';
            return $returnData;
        }
        $upData['merchantId'] = trim($data['api_key']);
        $upData['payType'] = (int)$data['type'];
        if($data['type'] == 2){
            $res = $this->getInfo();
            if(!$res){
                $returnData['code'] = 402;
                $returnData['msg'] = '缺少支付配置';
                return $returnData;
            }
            $upData['wxMerchantId'] = $res['results']['wxMerchantId'];
            $upData['wxApiKey'] = $res['results']['wxApiKey'];
            $upData['wxP12Path'] = $res['results']['wxP12Path'];
            $upData['wxP12BaseUrl'] = $res['results']['wxP12BaseUrl'];
        }
        $res = $this->set($upData);
        if($res){
            $returnData['msg'] = '操作成功';
        }else{
            $returnData['code'] = 400;
            $returnData['msg'] = '操作失败';
        }
        return $returnData;
    }

    /**
     * @param array $body
     * @return array|bool
     * 绑定设置
     */
    public function set(array $body){
        if (empty($body)) return false;
        $url = $this->getUrl('/pay');
        $res = $this->curl($url, 'post', $body);
        return $this->checkApiResult($res,$url,$body);
    }

}
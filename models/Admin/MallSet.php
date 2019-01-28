<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:50
 */
class Admin_MallSetModel extends VmallNewModel{
    protected $cls = '/mall-set';

    /**
     * @return array|bool
     * 获取配置信息
     */
    public function getInfo(){
        $url = $this->getUrl();
        $res = $this->curl($url);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param array $body
     * @return array|bool
     * 保存设置
     */
    public function put(array $body){
        if (empty($body)) return false;
        $url = $this->getUrl();
        $res = $this->curl($url, 'put', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    public function save(array $data){
        $returnData = array();
        $returnData['code'] = 200;
        if ( empty($data) || empty($data['mallName']) || empty($data['phone']) ||
            (empty($data['logo_old']) && empty($data['logo_file']))) {
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少必要参数';
            return $returnData;
        }
        $logoFile = $data['logo_old'];
        if(empty($logoFile)) {
            $logoFile = $this->uploadImg($data['logo_file'], array('jpg', 'jpeg', 'png'), 500 * 1024);
            if (!$logoFile) {
                $returnArr['code'] = 402;
                $returnArr['msg'] = '图片为空或上传失败';
                return $returnArr;
            }
        }
        $upData = array();
        if($data['id']) $upData['id'] = (int)$data['id'];
        if($data['mallName']) $upData['mallName'] = trim($data['mallName']);
        $upData['picUrl'] = $logoFile;
        if($data['freeFee']) $upData['freeFee'] = (int)$data['freeFee'];
        if($data['crmFreeLevel']) {
            $upData['crmFreeLevel'] = (int)$data['crmFreeLevel'];
            $upData['crmFreeFee'] = true;
        }else{
            $upData['crmFreeFee'] = false;
        }
        if($data['descr']) $upData['descr'] = trim($data['descr']);
        if($data['phone']) $upData['phone'] = trim($data['phone']);
        if($data['service']) $upData['service'] = trim($data['service']);
        $upData['freeAll'] = false;

        $res = $this->put($upData);
        if ($res){
            $returnArr['msg'] = '操作成功';
        }else{
            $returnData['code'] = 400;
            $returnData['msg'] = '操作失败';
        }

        return $returnData;

    }

}
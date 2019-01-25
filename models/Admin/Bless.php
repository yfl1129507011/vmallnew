<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:50
 */
class Admin_BlessModel extends VmallNewModel{
    protected $cls = '/bless';

    /**
     * @param array $query
     * @return array|bool
     * 获取祝福语列表
     */
    public function getList(array $query){
        $url = $this->getV2Url('',$query);
        $res = $this->curl($url);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $id
     * @param array $body
     * @return array|bool
     * 编辑祝福语
     */
    public function edit(array $body){
        if (empty($body)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url, 'put', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $body
     * @return array|bool
     * 添加祝福语
     */
    public function add(array $body){
        if (empty($body)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url, 'post', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $data
     * @return array
     * 处理祝福语的添加和修改操作
     */
    public function update(array $data){
        $returnData = array();
        $returnData['code'] = 200;
        if ( empty($data) || empty($data['file_picUrl'])) {
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少必要参数';
            return $returnData;
        }
        $picUrl = $this->uploadImg($data['file_picUrl'], array('jpg', 'jpeg', 'png'), 500 * 1024);
        if (!$picUrl) {
            $returnArr['code'] = 402;
            $returnArr['msg'] = '图片为空或上传失败';
            return $returnArr;
        }
        $upData = array();
        $upData['picUrl'] = $picUrl;
        $id = (int)$data['card_id'];
        if($id){ # 更新
            $upData['id'] = $id;
            $res = $this->editCard($upData);
        }else{ # 添加
            $res = $this->addCard($upData);
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
     * 删除祝福语
     */
    public function del($id){
        if (empty($id)) return false;
        $url = $this->getV2Url('/'.$id);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res,$url);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/3
 * Time: 10:09
 */
class Admin_PosterModel extends VmallNewModel{
    protected $cls = '/poster';

    /**
     * @param array $query
     * @return array|bool
     * 获取模板
     */
    public function getPosters(array $query){
        $url = $this->getV2Url('', $query);
        $res = $this->curl($url);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $id
     * @return array|bool
     * 删除模板
     */
    public function posterDel($id){
        if (!intval($id)) return false;
        $url = $this->getV2Url('/'.$id);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param array $body
     * @return array|bool
     * 修改模板
     */
    public function editPoster(array $body){
        if (empty($body)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url,'put',$body);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param array $body
     * @return array|bool
     * 添加模板
     */
    public function addPoster(array $body){
        if (empty($body)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url,'post',$body);
        return $this->checkApiResult($res,$url);
    }

    public function modifyPoster(array $data){
        $returnArr = $modifyArr = array();
        $returnArr['code'] = 200;
        if(!$data || !intval($data['open']) || !in_array($data['open'],array(1,2))){
            $returnArr['code'] = 401;
            $returnArr['msg'] = '缺少必填字段';
            return $returnArr;
        }
        if(!empty($data['file_picUrl']['name'])) {
            $picUrl = $this->uploadImg($data['file_picUrl'], array('jpg', 'jpeg', 'png'), 500 * 1024);
        }else{
            $picUrl = $data['picUrl'];
        }
        if (!$picUrl) {
            $returnArr['code'] = 402;
            $returnArr['msg'] = '图片为空或上传失败';
            return $returnArr;
        }
        $modifyArr['id'] = (int)$data['id'];
        $modifyArr['open'] = ($data['open']==1)?false:true;
        $modifyArr['picUrl'] = $picUrl;
        if (empty($modifyArr['id'])){ # 添加
            $res = $this->addPoster($modifyArr);
        }else{
            $res = $this->editPoster($modifyArr);
        }
        if($res){
            $returnArr['msg'] = '操作成功';
        }else{
            $returnArr['code'] = 403;
            $returnArr['msg'] = '操作失败';
        }

        return $returnArr;
    }

}
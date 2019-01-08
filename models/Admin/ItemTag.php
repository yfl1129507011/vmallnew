<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:07
 */
class Admin_ItemTagModel extends VmallNewModel {
    protected $cls = '/item-tag';

    /**
     * @param array $params
     * @return bool
     * 获取商品标签信息
     */
    public function getTags(array $params){
        $url = $this->getUrl('',$params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param $tid
     * @return array|bool
     * 删除标签
     */
    public function delTag($tid){
        if (!intval($tid)) return false;
        $url = $this->getUrl('/'.$tid);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $params
     * @return array|bool
     * 添加标签
     */
    public function addTag(array $params){
        if (empty($params)) return false;
        $url = $this->getUrl('',$params);
        $res = $this->curl($url,'post');
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $tid
     * @param array $params
     * @return array|bool
     * 编辑标签
     */
    public function editTag($tid, array $params){
        if (!intval($tid) || empty($params)) return false;
        $url = $this->getUrl('/'.$tid, $params);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res,$url);
    }

    public function modifyTag(array $data){
        $returnArr = $modifyArr = array();
        $returnArr['code'] = 200;
        if(empty($data) || empty($data['tag'])){
            $returnArr['code'] = 401;
            $returnArr['msg'] = '缺少必填字段';
            return $returnArr;
        }
        $modifyArr['tag'] = $this->formatLen($data['tag'], 4);
        $tid = (int)$data['id'];
        if($tid){ # 编辑
            $res = $this->editTag($tid,$modifyArr);
        }else{
            $res = $this->addTag($modifyArr);
        }

        if($res){
            $returnArr['msg'] = '操作成功';
        }else{
            $returnArr['code'] = 402;
            $returnArr['msg'] = '操作失败';
        }

        return $returnArr;
    }
}
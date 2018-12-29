<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/20
 * Time: 11:28
 */
class Admin_ItemCatModel extends VmallNewModel{
    protected $cls = '/item-cat';

    /**
     * @param array $params
     * @param bool $all
     * @return array|bool
     * 获取商品分类信息
     */
    public function getCat(array $params){
        $url = $this->getUrl('',$params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param $cid
     * @param array $params
     * @return array|bool
     * 设置热推
     */
    public function hotCat($cid, array $params){
        if(empty($cid)) return false;
        $url = $this->getUrl('/'.$cid.'/hot', $params);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param $cid
     * @return array|bool
     * 删除分类
     */
    public function delCat($cid){
        if (empty($cid)) return false;
        $url = $this->getUrl('/'.$cid);
        $res = $this->curl($url, 'delete');
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $data
     * @return array
     * 添加或修改分类
     */
    public function modifyCat(array $data){
        $returnArr = $modifyArr = array();
        $returnArr['code'] = 200;
        if(empty($data) || empty($data['name']) || empty($data['icon']['name'])){
            $returnArr['code'] = 401;
            $returnArr['msg'] = '缺少必填字段';
            return $returnArr;
        }
        $icon = $this->uploadImg($data['icon'], array('jpg','jpeg','png'), 500*1024);
        if(!$icon){
            $returnArr['code'] = 402;
            $returnArr['msg'] = '图片上传失败';
            return $returnArr;
        }
        $modifyArr['icon'] = $icon;
        $modifyArr['name'] = $this->formatLen($data['name'], 9);
        $modifyArr['order'] = (int)$data['order'];
        $modifyArr['hot'] = (bool)$data['hot'];
        $modifyArr['status'] = (int)boolval($data['hot']);
        $modifyArr['parentCid'] = (int)($data['parentCid']);
        if(!empty($data['id'])){
            $modifyArr['parentCid'] = (int)($data['id']);
        }

        $url = $this->getUrl();
        $res = $this->curl($url, 'post', $modifyArr);
        if($this->checkApiResult($res, $url)){
            $returnArr['msg'] = '操作成功';
        }else{
            $returnArr['code'] = 403;
            $returnArr['msg'] = '操作失败';
        }

        return $returnArr;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:50
 */
class Admin_BannerSetModel extends VmallNewModel{
    protected $cls = '/banner';
    public static $typeArr = array(
        '1'=>'商品',
        '2'=>'分类',
        '3'=>'自定义',
    );

    public function formatInfo(array $data){
        if($data && count($data['results'])){
            $typeArr = self::$typeArr;
            $data['results'] = array_map(function ($v) use ($typeArr) {
                $v['type_name'] = $typeArr[$v['type']];
                return $v;
            }, $data['results']);
        }
        return $data;
    }

    /**
     * @return array|bool
     * 获取轮播信息
     */
    public function getInfo(){
        $url = $this->getUrl();
        $res = $this->curl($url);
        if($this->checkApiResult($res,$url)){
            return $this->formatInfo($res);
        }
        return false;
    }

    /**
     * @param array $body
     * @return array|bool
     * 保存设置
     */
    public function post(array $body){
        if (empty($body)) return false;
        $url = $this->getUrl();
        $res = $this->curl($url, 'post', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $data
     * @return array
     * 保存或添加操作
     */
    public function update(array $data){
        $returnData = array();
        $returnData['code'] = 200;
        if ( empty($data) || empty($data['cid']) ||
            (empty($data['bannerUrl_old']) && empty($data['bannerUrl_file']))) {
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少必要参数';
            return $returnData;
        }
        $bannerUrlFile = $data['bannerUrl_old'];
        if(!empty($data['bannerUrl_file'])) {
            $bannerUrlFile = $this->uploadImg($data['bannerUrl_file'], array('jpg', 'jpeg', 'png'), 500 * 1024);
            if (!$bannerUrlFile) {
                $returnArr['code'] = 402;
                $returnArr['msg'] = '图片为空或上传失败';
                return $returnArr;
            }
        }
        $upData = array();
        $upData['bannerUrl'] = $bannerUrlFile;
        $upData['cid'] = (int)$data['cid'];
        if(isset($data['id'])) $upData['id'] = (int)$data['id'];
        if(isset($data['type'])) $upData['type'] = (int)$data['type'];
        if(isset($data['numIid'])) $upData['numIid'] = (int)$data['numIid'];
        if(isset($data['url'])) $upData['url'] = trim($data['url']);

        $res = $this->post($upData);
        if ($res){
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
     * 删除
     */
    public function del($id){
        if(empty($id)) return false;
        $url = $this->getUrl('/'.$id);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res,$url);
    }

}
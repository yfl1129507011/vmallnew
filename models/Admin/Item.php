<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/20
 * Time: 10:30
 */
class Admin_ItemModel extends VmallNewModel{
    protected $cls = '/item';
    private $bulkApiType = array(
        '1'=>'/delisting-batch', # 批量下架
        '2'=>'/put-recylebin-batch', # 批量放入回收站
        '3'=>'/out-recylebin-batch', # 批量从回收站还原
        '4'=>'/delete-batch', # 批量删除
    );
    private $listingType = array(
        '1'=>'/delisting', # 商品下架
        '2'=>'/listing',  # 商品上架
    );
    private $recyleType = array(
        '1'=>'/put-recyle-bin', # 放到回收站
        '2'=>'/out-recyle-bin',  # 回收站还原
    );

    /**
     * @param array $params
     * @return array|bool
     * 获取商品列表
     */
    public function getList(array $params){
        $url = $this->getUrl('', $params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param $numIid
     * @return bool
     * 获取商品的sku
     */
    public function getSku($numIid){
        if (empty($numIid)) return false;
        $url = $this->getV2Url('/'.$numIid.'/sku');
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $res['results'];
        }
        return false;
    }

    /**
     * @param $type
     * @param array $ids
     * @return array|bool
     * 商品批量操作接口
     */
    public function itemBulk($type, array $ids){
        if (empty($type) || !array_key_exists($type, $this->bulkApiType) || empty($ids)) return false;
        $method = (4==$type)?'delete':'put';
        $url = $this->getUrl($this->bulkApiType[$type]);
        $res = $this->curl($url, $method, $ids);
        return $this->checkApiResult($res, $url);
    }

    /**
     * @param array $params
     * @return array|bool
     * 商品添加和修改
     */
    public function itemModify(array $params){
        if (empty($params)) return false;
        $url = $this->getV2Url();
        $res = $this->curl($url, 'post', $params);
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $pid 商品id
     * @param $type
     * @return array|bool
     * 商品上/下架操作
     */
    public function itemListing($pid, $type){
        if (empty($type) || !array_key_exists($type, $this->listingType) || empty($pid)) return false;
        $url = $this->getUrl('/'.$pid.$this->listingType[$type]);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param $pid
     * @param $type
     * @return array|bool
     * 回收站操作
     */
    public function recyleBin($pid, $type){
        if (empty($type) || !array_key_exists($type, $this->recyleType) || empty($pid)) return false;
        $url = $this->getUrl('/'.$pid.$this->recyleType[$type]);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res, $url);
    }

    /**
     * 商品删除操作
     * @param $pid
     * @return array|bool
     */
    public function removeItem($pid){
        if (empty($pid)) return false;
        $url = $this->getUrl('/'.$pid);
        $res = $this->curl($url,'delete');
        return $this->checkApiResult($res, $url);
    }
}
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
    # 商品添加修改时必填字段信息
    private $requireFileds = array(
        'name','cid','stock','deliveryTemplateId',
        'file'=>array('picUrl','album'),
    );
    # 图片类型
    private $imgType = array('jpg','jpeg','png');

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

    public function productModify(array $data){
        if (empty($data)) return false;
        if(!$this->checkRequiredFields($data)) return -1;  # 必填字段没有填写

        $data = $this->checkData($data);
        if(!$data) return -2; # 数据不完整

        $data = $this->checkUploadImg($data);
        if (!$data) return -3; # 上传失败
    }

    /**
     * @param array $data
     * @return bool
     * 检测必填字段
     */
    private function checkRequiredFields(array $data){
        if(!empty($data) && !empty($this->requireFileds)){
            foreach ($this->requireFileds as $k=>$v){
                if('file'==$k){
                    if (empty($data['upFile'])) return false;
                    foreach ($v as $_k=>$_v){
                        $_name = $data['upFile'][$_v]['name'];
                        if(is_array($data['upFile'][$_v]['name'])){  // 多个文件上传
                            $_name = $_name[0];
                        }
                        if(empty($_name)) return false;
                    }
                }else{
                    if(empty($data[$v])) return false;
                }
            }
            return true;
        }
        return false;
    }

    private function checkData(array $data){
        $newData = array();
        # 商品名称
        $newData['name'] = (string)$data['name'];
        if(mb_strlen($newData['name'],'utf8') > 20){
            $newData['name'] = mb_substr($newData['name'],0,20,'utf8');
        }
        # 商品类型
        $cidArr = explode('_', (string)$data['cid']);
        $newData['cid'] = (int)$cidArr[0];
        $newData['quantityUnit'] = $cidArr[1];
        # 是否推荐
        $newData['hot'] = empty($data['hot'])?false:true;
        # 商品标签
        $newData['tags'] = (int)$data['tags'];
        # 是否支持送礼
        $newData['type'] = empty($data['type'])?1:2;
        # 商品价格
        $newData['price'] = sprintf("%.2f",(float)$data['price']);
        # 商品库存
        $newData['stock'] = (int)$data['stock'];
        # 运费模板
        $newData['deliveryTemplateId'] = (int)$data['deliveryTemplateId'];

    }

    /**
     * @param array $data
     * @return array|bool
     * 图片上传检测
     */
    private function checkUploadImg(array $data){
        if (empty($data)) return false;
        if(empty($data['upFile'])) return false;
        $upFile = (array)$data['upFile'];
        foreach ($upFile as $k=>$v){
            if(is_array($v['name'])){
                $upArr = $this->formatPlusUpload($v);
                $_plusArr = array();
                foreach ($upArr as $key=>$_file){
                    $_url = $this->uploadImg($_file);
                    if($_url<0){  # 上传失败
                        Logger::error("【{$key}】图片【{$_file['name']}】上传失败code[{$_url}]");
                    }else {
                        $_plusArr[] = $_url;
                    }
                }
                if(empty($_plusArr)) return false;
                $data[$k] = $_plusArr;
            }else{
                $_url = $this->uploadImg($v);
                if($_url<0){  # 上传失败
                    Logger::error("【{$k}】图片【{$v['name']}】上传失败code[{$_url}]");
                    return false;
                }
                $data[$k] = $_url;
            }
        }
        unset($data['upFile']);
        return $data;
    }

    /**
     * @param array $upFile
     * @return array
     * 格式化多文件上传的信息
     */
    private function formatPlusUpload(array $upFile){
        $data = array();
        if (empty($upFile) || !is_array($upFile['name'])) return $data;
        foreach ($upFile['name'] as $i=>$j) {
            if (empty($j)) break;
            $tmp = array();
            foreach ($upFile as $k => $v) {
                $tmp[$k] = $v[$i];
            }
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * @param array $upFile
     * @return int
     * 图片上传
     */
    private function __uploadImg(array $upFile){
        if(empty($upFile) || empty($upFile['name']) || $upFile['error']>0) return -1;
        $_type = $upFile['type'];
        $sizeLimit = 500*1024;
        $ext = substr($_type, strrpos($_type,'/')+1);
        $_size = $upFile['size'];
        if (!in_array($ext, $this->imgType) || $_size>$sizeLimit) return -2;

        $dir = '/vmallnew/item/'.SELLER_ID. '/'.time().$ext;
        $_FILES['file'] = $upFile;
        $upload = new WL_FileUploader($this->imgType, $sizeLimit);
        $res = $upload->handleUploadOSS(Config::get('WeLife.oss.global'), $dir, false ,Config::get('WeLife.oss.global.buckets.wlpublicmedias'));
        if (!empty($res['errcode']) || !empty($res['result']['url'])) {
            return -3; # 上传失败
        }
        return $res['result']['url'];
    }



}
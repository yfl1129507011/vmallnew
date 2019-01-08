<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/18
 * Time: 11:38
 */
class VmallNewModel {
    private $modules = "admin/";
    protected $baseUrl = '';
    protected $apiUri = '';
    protected $cls = '';
    protected $sellId = '';
    public $head = array(
        "Content-type: application/json;charset='utf-8'",
        "Accept: application/json",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
    );

    /**
     * VmallNewModel constructor.
     * @param null $sellId
     */
    public function __construct($sellId=null)
    {
        $this->apiUri = Config::get('Vmall.shopbridge.vmall_api_url');
        $this->sellId = $sellId?$sellId:SELLER_ID;
        $this->baseUrl = $this->apiUri.$this->modules.$this->sellId;
        $this->sellId = $sellId;
    }

    /**
     * 获取接口地址信息
     * @param string $module
     * @param array $params
     * @return string
     */
    protected function getUrl($module='', $params=array()){
        $url = $this->baseUrl.$this->cls.$module;
        if(!empty($params)) {
            $query = http_build_query($params, '', '&');
            $url .= '?' . $query;
        }
        return $url;
    }

    /**
     * @param string $module
     * @param array $params
     * @return string
     * 获取V2的接口地址
     */
    protected function getV2Url($module='', $params=array()){
        $url = $this->baseUrl.'/v2'.$this->cls.$module;
        if(!empty($params)) {
            $query = http_build_query($params, '', '&');
            $url .= '?' . $query;
        }
        return $url;
    }

    /**
     * @param $result
     * @param $url
     * @param array $reqData
     * @return array|bool
     * 检测数据接口的结果
     */
    protected function checkApiResult($result, $url, array $reqData=array()){
        if($result) {
            if ($result['success'] == true || $result['code'] == 200) {
                return $result;
            } else {
                $errMsg = "[{$url}]请求错误信息：" . $result['message'];
                if($reqData){
                    $errMsg .= '请求数据：'.var_export($reqData,true);
                }
                Logger::error($errMsg);
            }
        }
        return false;
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @param array $head
     * @return mixed
     */
    protected function curl($url,$method='get',$params=array(),$head=array()){
        $curl = curl_init();  # 初始化url句柄
        curl_setopt($curl, CURLOPT_URL, $url); # 设置请求地址
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); # 设置curl_exce作为变量存储，而不是直接打印
        # 不验证SSL
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $header = $head;
        if (empty($header)){
            $header = array("Content-Type:application/json;charset=utf-8");
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);  # 设置请求头信息
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);  # 设置连接超时时间

        $method = strtoupper($method);
        switch ($method){
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                if (is_array($params)){
                    $params = json_encode($params, 320);
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params, 320));
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params, 320));
                break;
        }
        $data = curl_exec($curl);
        $error = curl_errno($curl);
        //$status = curl_getinfo($curl, CURLINFO_HTTP_CODE); # 获取返回状态值
        curl_close($curl);
        if ($error){
            Logger::error("接口地址[{$url}]的错误码：".$error);
            return false;
        }
        $res = json_decode($data, true);
        return $res;
    }

    /**
     * @param array $upFile
     * @param float|int $sizeLimit 大小限制（单位字节B）
     * @param array $type  类型限制
     * @return int
     * 图片上传
     */
    protected function uploadImg(array $upFile, array $type, $sizeLimit=10485760){
        if(empty($upFile) || empty($upFile['name']) || $upFile['error']>0) return false;
        $dir = '/vmallnew/'.get_class($this).'/'.SELLER_ID. '/'.time().'/';
        $_FILES['file'] = $upFile;
        $upload = new WL_FileUploader($type, $sizeLimit);
        $res = $upload->handleUploadOSS(Config::get('WeLife.oss.global'), $dir, false ,Config::get('WeLife.oss.global.buckets.wlpublicmedias'));
        if (!empty($res['errcode']) || empty($res['result']['url'])) {
            return false; # 上传失败
        }
        return $res['result']['url'];
    }

    /**
     * @param $data
     * @param $len
     * @return string
     * 格式化字符长度
     */
    public function formatLen($data, $len){
        $data = (string)$data;
        if(empty($data) || empty($len)) return $data;
        if(mb_strlen($data,'utf8') > $len){
            $data = mb_substr($data,0,$len,'utf8');
        }
        return $data;
    }

}
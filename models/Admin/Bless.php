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
    public function getList(array $query=array()){
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
        if ( empty($data) || empty($data['bless'])) {
            $returnData['code'] = 401;
            $returnData['msg'] = '缺少必要参数';
            return $returnData;
        }
        $upData = array();
        $upData['bless'] = $data['bless'];
        $id = (int)$data['bless_id'];
        if($id){ # 更新
            $upData['id'] = $id;
            $res = $this->edit($upData);
        }else{ # 添加
            $res = $this->getList();
            if($res && $res['count']>=20){
                $returnData['code'] = 402;
                $returnData['msg'] = '系统最多仅允许添加20条！';
                return $returnData;
            }
            $res = $this->add($upData);
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
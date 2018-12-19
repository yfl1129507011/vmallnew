<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/18
 * Time: 14:18
 */
class Admin_MessageModel extends VmallNewModel{
    protected $cls = '/message';

    /**
     * 获取未读消息数
     */
    public function getUnreadCnt(){
        $url = $this->getUrl('/unread-count');
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $res['results'];
        }
        return false;
    }

    /**
     * @param array $params
     * @return array|bool
     * 消息类型topic(trade:订单,item:商品)
     */
    public function getMessage(array $params){
        $url = $this->getUrl('',$params);
        $res = $this->curl($url);
        return $this->checkApiResult($res, $url);
    }

    /**
     * 标记已读信息
     * @param $msgId
     * @return array|bool
     */
    public function readMsg($msgId){
        if (empty($msgId)) return false;
        $url = $this->getUrl('/'.$msgId);
        $res = $this->curl($url,'post');
        return $this->checkApiResult($res,$url);
    }

}
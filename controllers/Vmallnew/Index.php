<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/12
 * Time: 16:22
 */
class Vmallnew_IndexController extends Vmallnew_BaseController {

    public function indexAction(){
        $this->display();
    }

    public function messageAction(){
        $arr = array('1'=>'trade','2'=>'item');
        $topic = $this->getRequest()->getParam('topic');
        $curPage = $this->_request->get('page', 1);
        $conditions['topic'] = $arr[$topic];
        $conditions['curPage'] = $curPage;
        $message = new Admin_MessageModel();
        $res = $message->getMessage($conditions);
        if (!$res) exit('数据获取失败！');
        $results = $res['results'];
        if(1==$topic) {
            $results = array_map(function ($v) {
                if (!empty($v)) {
                    $v['message'] = str_replace('（用户名） ', '', $v['message']);
                    $v['message'] = str_replace('（用户名）', '', $v['message']);
                    $v['gmtCreated'] = $v['gmtCreated'] / 1000;
                }
                return $v;
            }, (array)$results);
        }
        $pageOptions = array(
            'perPage'=>10,
            'currentPage'=>$curPage,
            'curPageClass' => 'active',
            'totalItems' => $res['count']
        );
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        //$this->_view->assign('pagecount', $res['pageCount']);
        $this->_view->assign('results', $results);
        $this->display();
    }

    public function readAction(){
        $retData = array();
        $retData['code'] = 200;
        $msgId = intval($this->_request->getPost('msgId'));
        if(!$msgId){
            $retData['code'] = 401;
            $retData['msg'] = '参数错误';
            $this->ajaxReturn($retData);
        }
        $message = new Admin_MessageModel();
        if (false === $message->readMsg($msgId)){
            $retData['code'] = 402;
            $retData['msg'] = '标记已读信息失败';
            $this->ajaxReturn($retData);
        }

        $this->ajaxReturn($retData);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/3
 * Time: 16:30
 */
class Vmallnew_TradeController extends Vmallnew_BaseController{
    private $tradeClient = null;

    public function init()
    {
        parent::init();
        $this->tradeClient = new Admin_TradeModel('3405474861');
    }

    public function indexAction(){
        $orderStatus = array(
            '1' => '待发货',
            '3' => '待收货',
            '100' => '退款/售后待审核',
            '101' => '待买家退货',
            '102' => '待确认收货',
            '103' => '待退款',
            '104' => '已退款',
            '105' => '拒绝退款',
            '106' => '退款关闭',
        );
        $tradeSum = $this->tradeClient->getTradeSum();
        $this->_view->assign('tradeSum',$tradeSum);
        $this->_view->assign('orderStatus',$orderStatus);
        $this->display();
    }

    # 订单列表
    public function listAction(){
        $curPage = (int)$this->_request->get('page',1);
        $tid = trim($this->_request->getPost('tid'));
        $status = (int)$this->_request->getRequest('status');
        $buyerName = trim($this->_request->getPost('buyerName'));
        $export = trim($this->_request->getRequest('export'));

        $data = array();
        if ($export){  # 数据导出
            $conditions['pageSize'] = 9999;
        }else {
            $data['curPage'] = $curPage;
        }
        if($tid) $data['tid'] = $tid;
        if($status) $data['status'] = $status;
        if($buyerName) $data['buyerName'] = $buyerName;

        $res = $this->tradeClient->getTrade($data);
        if(!$res) exit('数据获取失败');

        $results = $res['results'];

        if(!empty($export)){ # 数据导出
            $this->tradeClient->exportTrade($results);exit();
        }

        $pageOptions = array(
            'perPage' => 10,
            'currentPage' => $curPage,
            'curPageClass' => 'active',
            'totalItems' => $res['count']
        );
        $this->_view->assign('results',$results);
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->display();
    }

    # 订单详情
    public function detailAction(){
        $tid = $this->getRequest()->getParam('tid');
        if($tid){
            $res = $this->tradeClient->getDetailInfo($tid);
            //echo '<pre>';print_r($res);die;
            if(!$res) exit('获取数据失败');
            $this->_view->assign('data', $res);
            $this->display();
        }
    }

}
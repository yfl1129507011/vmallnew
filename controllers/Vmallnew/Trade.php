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
            $this->_view->assign('logisticsCompanyInfo', $this->tradeClient->logisticsCompanyInfo);
            $this->_view->assign('data', $res);
            $this->display();
        }
    }

    # 订单发货
    public function deliveryAction(){
        $tid = trim($this->_request->getPost('tid'));
        $logistics = trim($this->_request->getPost('logistics'));
        $invoiceNo = trim($this->_request->getPost('invoiceNo'));
        $returnData = array();
        $returnData['code'] = 200;
        if($tid && $logistics && $invoiceNo) {
            $logisticsInfo = array();
            $logistics = explode('-', $logistics);
            $logisticsInfo['invoiceNo'] = $invoiceNo;
            $logisticsInfo['logisticsCompany'] = $logistics[0];
            $logisticsInfo['logisticsCompanyCn'] = $logistics[1];
            $res = $this->tradeClient->tradeDelivery($tid,$logisticsInfo);
            if($res){
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnData);
    }

    # 订单退款/售后审核
    public function checkAction(){
        $data = $this->_request->getRequest();
        $res = $this->tradeClient->tradeCheck($data);
        $this->ajaxReturn($res);
    }

    # 售后确认收到退货
    public function confirmAction(){
        $tid = trim($this->_request->getPost('tid'));
        $refundAmount = (float)$this->_request->getPost('refundAmount');
        $refundAmountCheck = (float)$this->_request->getPost('refundAmountCheck');
        $aggreeRefund = (int)$this->_request->getPost('aggreeRefund');
        $returnData = $data = array();
        $returnData['code'] = 200;
        if ($tid && $refundAmount){
            if ($refundAmount>$refundAmountCheck){
                $returnData['code'] = 401;
                $returnData['msg'] = '退款金额有误';
            }
            $data['refundAmount'] = sprintf('%2f',$refundAmount);
            if($aggreeRefund == 1){
                $data['aggreeRefund'] = true;
                $res = $this->tradeClient->tradeRefund($tid, $data);
            }else {
                $res = $this->tradeClient->returnGoodConfirm($tid, $data);
            }
            if($res){
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnData);
    }

}
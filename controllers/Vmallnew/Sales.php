<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:25
 */
class Vmallnew_SalesController extends Vmallnew_BaseController{

### 会员折扣 START ###
    public function crm_indexAction(){
        $this->display();
    }

    # 会员折扣列表
    public function crm_listAction(){
        $curPage = (int)$this->_request->get('page',1);
        $id = (int)$this->_request->getPost('id');
        $name = trim($this->_request->getPost('name'));

        $data = array();
        $data['curPage'] = $curPage;
        if($id) $data['id'] = $id;
        if($name) $data['name'] = $name;

        $crmDiscount = new Admin_CrmDiscountModel();
        $res = $crmDiscount->getList($data);
        if(!$res) exit('数据获取失败');

        $results = $res['results'];
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

    # 会员折扣修改和添加页面展示
    public function crm_modifyAction(){
        $disId = (int)$this->_request->getRequest('id');
        if($disId) {
            // 获取折扣信息
            $crmDiscount = new Admin_CrmDiscountModel();
            $res = $crmDiscount->getList(array('id'=>$disId));
            if(!$res) exit('数据获取失败');

            $this->_view->assign('data', $res['results'][0]);
            $this->_view->assign('disId', $disId);
        }

        // 获取会员规则
        $cardCategoryClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.User.CardCategory'));
        $cardRuleClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.User.CardRule'));
        # 返回商家所有会员卡类型
        $result = $cardCategoryClient->sync('listCategories', array('bid'=>SELLER_ID, 'ccAttribute'=>1));
        $CardRules = $cardRuleClient->sync('listCardRules', array(SELLER_ID));
        $cardCats = array();
        if ($CardRules) {
            foreach ($CardRules as $k => $v) {
                $cardCats[$v['ccid']] = $result[$v['ccid']]['ccName'];
            }
        }
        $this->_view->assign('cardCats',$cardCats);

        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('pageSize' => 9999));
        if($catRes){ # 商品分类信息
            $catRes = array_column($catRes['results'], 'name', 'id');
            $this->_view->assign('cats',$catRes);
        }

        $this->display();
    }

    # 折扣商品信息
    public function crm_discountAction(){
        $curPage = (int)$this->_request->get('page',1);
        $id = $this->getRequest()->getParam('id');
        $this->_view->assign('_id',$id);
        $name = trim($this->_request->getPost('name'));
        $numIid = (int)$this->_request->getPost('nid');
        $catId = (int)$this->_request->getPost('cid');

        $condition = array();
        if($name) $condition['name'] = $name;
        if($numIid) $condition['numIid'] = $numIid;
        if($catId) $condition['catId'] = $catId;

        $crmDiscount = new Admin_CrmDiscountModel();
        $res = $crmDiscount->getDiscount($id,$condition);
        if(!$res) exit('数据获取失败');

        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('pageSize' => 9999));
        $catRes = array_column($catRes['results'], 'name', 'id');
        $this->_view->assign('cats',$catRes);

        $results = $res['results'];
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

    # 会员折扣添加或更新
    public function crm_updateAction(){
        $postData = $this->_request->getPost();
        $crmDiscount = new Admin_CrmDiscountModel();
        $res = $crmDiscount->update($postData);
        $this->ajaxReturn($res);
    }

    # 删除会员折扣
    public function crm_delAction(){
        $id = (int)$this->_request->getRequest('id');
        if($id){
            $crmDiscount = new Admin_CrmDiscountModel();
            $res = $crmDiscount->delDiscount($id);
            $returnData = array();
            if($res){
                $returnData['code'] = 200;
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
            $this->ajaxReturn($returnData);
        }
    }
### 会员折扣 END ###

### 礼品中心 START ###
    public function gift_indexAction(){
        $this->_view->assign('statusArr', Admin_GiftGivingModel::$statusArr);
        $this->display();
    }

    # 赠送礼品列表
    public function gift_listAction(){
        $curPage = (int)$this->_request->get('page',1);
        $status = $this->_request->getPost('status', '');
        $id = (int)$this->_request->getPost('id');
        $buyerName = trim($this->_request->getPost('buyerName'));
        $receiverName = trim($this->_request->getPost('receiverName'));
        $itemName = trim($this->_request->getPost('itemName'));

        $queryData = array();
        $queryData['curPage'] = $curPage;
        if($status!=='') $queryData['status'] = intval($status);
        if($id) $queryData['id'] = $id;
        if($buyerName) $queryData['buyerName'] = $buyerName;
        if($receiverName) $queryData['receiverName'] = $receiverName;
        if($itemName) $queryData['itemName'] = $itemName;

        $gift = new Admin_GiftGivingModel(3405474861);
        $res = $gift->getList($queryData);
        if(!$res) exit('数据获取失败');

        $results = $res['results'];
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

    # 礼品详情
    public function gift_descAction(){
        $id = $this->getRequest()->getParam('id');
        if($id){
            $gift = new Admin_GiftGivingModel(3405474861);
            $res = $gift->giftDesc($id);
            if(!$res) exit('数据获取失败');
            # echo '<pre>';print_r($res);die;
            if ($res['status'] == 2){ # 已收礼，获取订单信息
                $trade = new Admin_TradeModel(3405474861);
                $tradeInfo = $trade->getTrade(array('tid'=>$res['tid']));
                # echo '<pre>';print_r($tradeInfo);die;
                if($tradeInfo){
                    $_tradeInfo = $tradeInfo['results'][0];
                    $this->_view->assign('tradeInfo',$_tradeInfo);
                }
            }

            /*$skuArr = array();
            $item = new Admin_ItemModel(3405474861);
            $itemSku = $item->getSku($res['numIid']);
            # echo '<pre>';print_r($itemSku);die;
            if ($itemSku){
                foreach ($itemSku as $k=>$v){
                    if ($v['id'] == $res['skuId']){
                        $skuArr = $v;
                    }
                }
            }else{
                $itemRes = $item->getDetail($res['numIid']);
                if($itemRes){
                    $skuArr = $itemRes['results'];
                }
            }
            # echo '<pre>';print_r($skuArr);die;
            $this->_view->assign('skuArr',$skuArr);*/

            $this->_view->assign('data',$res);
            $this->display();
        }
    }

    # 贺卡列表
    public function card_listAction(){
        $queryData = array();
        $queryData['pageSize'] = 20;  # 贺卡最多20张
        $greetingCard = new Admin_GreetingCardModel();
        $res = $greetingCard->getList($queryData);
        if(!$res) exit('数据获取失败');

        $this->_view->assign('results', $res['results']);
        $this->display();
    }

    # 处理贺卡的添加或更新操作
    public function card_updateAction(){
        $data = $this->_request->getPost();
        if($_FILES['picUrl']) {
            $data['file_picUrl'] = $_FILES['picUrl'];
        }
        $greetingCard = new Admin_GreetingCardModel();
        $res = $greetingCard->update($data);
        $this->ajaxReturn($res);
    }

    # 删除贺卡
    public function card_delAction(){
        $cardId = (int)$this->getRequest()->getParam('id');
        $returnData = array();
        $returnData['code'] = 200;
        if($cardId){
            $greetingCard = new Admin_GreetingCardModel();
            $res = $greetingCard->delCard($cardId);
            if($res){
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnData);
    }

    # 祝福语列表
    public function bless_listAction(){
        $curPage = (int)$this->_request->get('page',1);
        $bless = new Admin_BlessModel();
        $res = $bless->getList(array('curPage'=>$curPage));
        if(!$res) exit('获取数据失败');

        $results = $res['results'];
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

    # 删除祝福语
    public function bless_delAction(){
        $blessId = $this->getRequest()->getParam('id');
        if($blessId){
            $returnData = array();
            $bless = new Admin_BlessModel();
            $res = $bless->del($blessId);
            if ($res){
                $returnData['code'] = 200;
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
            $this->ajaxReturn($returnData);
        }
    }

    # 处理祝福语添加或编辑
    public function bless_updateAction(){
        $data = $this->_request->getPost();
        if($data){
            $bless = new Admin_BlessModel();
            $res = $bless->update($data);
            $this->ajaxReturn($res);
        }
    }
### 礼品中心 END ###

}
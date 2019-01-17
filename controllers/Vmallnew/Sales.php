<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/8
 * Time: 14:25
 */
class Vmallnew_SalesController extends Vmallnew_BaseController{
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

}
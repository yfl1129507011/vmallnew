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

    public function crm_modifyAction(){
        $this->display();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/19
 * Time: 17:25
 */
class Vmallnew_GoodsController extends Vmallnew_BaseController{

    public function product_indexAction(){
        $this->display();
    }

    # 商品列表
    public function product_listAction(){
        $curPage = $this->_request->get('page', 1);
        $id = trim($this->_request->getRequest('id'));
        $name = trim($this->_request->getRequest('name'));
        $cid = trim($this->_request->getRequest('cid'));
        $tid = trim($this->_request->getRequest('typeid'));

        $conditions = array();
        $conditions['inRecyleBin'] = 0;
        $conditions['curPage'] = $curPage;
        if (!empty($id)) $conditions['numIid'] = $id;
        if (!empty($name)) $conditions['name'] = $name;
        if (!empty($cid)) $conditions['cid'] = $cid;
        if (!empty($tid)) $conditions['type'] = $tid;

        $item = new Admin_ItemModel();
        $res = $item->getList($conditions);
        if (!$res) exit('数据获取失败！');

        $results = array_map(function ($_v) use($item){
            if(!empty($item->getSku($_v['id']))){
                $_v['sku'] = true;
            }
            return $_v;
        },$res['results']);
        $pageOptions = Array(
            'perPage' => 10,
            'currentPage' => $curPage,
            'curPageClass' => 'active',
            'totalItems' => $res['count']
        );
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->_view->assign('results',$results);

        // 获取商品分类
        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('pageSize' => 9999));
        //$this->ajaxReturn($catRes);
        if(!empty($catRes['results'])){
            $cats = array_column($catRes['results'], 'name', 'id');
            $this->_view->assign('cats',$cats);
        }
        $this->display();
    }

    # 商品批量操作
    public function product_bulkAction(){
        $type = (int)$this->_request->getPost('type');
        $ids = $this->_request->getPost('ids');
        $returnArr = array();
        $returnArr['code'] = 200;
        if ($type && !empty($ids)) {
            $item = new Admin_ItemModel();
            $res = $item->itemBulk($type, array('ids' => $ids));
            if (!$res) {
                $returnArr['code'] = 400;
                $returnArr['msg'] = '操作失败';
            } else {
                $returnArr['msg'] = '操作成功';
            }
        }
        $this->ajaxReturn($returnArr);
    }

    # 商品上/下架操作
    public function product_listingAction(){
        $pid = (int)$this->_request->getPost('pid');
        $type = (int)$this->_request->getPost('type');
        $returnArr = array();
        $returnArr['code'] = 200;
        if($pid && $type){
            $item = new Admin_ItemModel();
            $res = $item->itemListing($pid,$type);
            if (!$res) {
                $returnArr['code'] = 400;
                $returnArr['msg'] = '操作失败';
            } else {
                $returnArr['msg'] = '操作成功';
            }
        }
        $this->ajaxReturn($returnArr);
    }

    # 商品推荐操作
    public function product_hotAction(){
        $pid = (int)$this->_request->getPost('pid');
        $type = (int)$this->_request->getPost('type');
        $returnArr = array();
        $returnArr['code'] = 200;
        if ($pid && $type && in_array($type, array(1,2))){
            $item = new Admin_ItemModel();
            $data = $item->getList(array('numIid' => $pid));
            if($data) {
                $params = $data['results'][0];
                if (!array_key_exists('deliveryTemplateId',$params)){
                    $returnArr['code'] = 400;
                    $returnArr['msg'] = '操作失败,没有设置运费模板，请编辑商品添加后再试';
                    $this->ajaxReturn($returnArr);
                }
                $params['hot'] = ($type==1)?false:true;
                $res = $item->itemModify($params);
                if (!$res) {
                    $returnArr['code'] = 401;
                    $returnArr['msg'] = '操作失败';
                } else {
                    $returnArr['msg'] = '操作成功';
                }
            }else{
                $returnArr['code'] = 402;
                $returnArr['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnArr);
    }

    # 商品放到回收站
    public function product_delAction(){
        $pid = (int)$this->_request->getRequest('id');
        $type = (int)$this->_request->getRequest('type', 1);
        $returnArr = array();
        $returnArr['code'] = 200;
        if ($pid) {
            $item = new Admin_ItemModel();
            $res = $item->recyleBin($pid, $type);
            if ($res) {
                $returnArr['msg'] = '操作成功';
            } else {
                $returnArr['code'] = 401;
                $returnArr['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnArr);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/19
 * Time: 17:25
 */
class Vmallnew_GoodsController extends Vmallnew_BaseController{

    /**
     * 向页面传递商品分类信息
     * @param bool $all
     */
    protected function assignProductCat($all=false){
        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('pageSize' => 9999));
        $catRes = $catRes['results'];
        if(!$all && !empty($catRes)){
            $catRes = array_column($catRes, 'name', 'id');
        }
        $this->_view->assign('cats',$catRes);
    }

    /**
     * 向页面传递商品标签信息
     */
    protected function assignProductTag(){
        $itemTag = new Admin_ItemTagModel();
        $res = $itemTag->getTags(array('pageSize' => 9999));
        $this->_view->assign('tags', $res['results']);
    }

    /**
     * 传递规格相关信息
     */
    protected function assignSkuInfo(){
        $sku = new Admin_SkuModel();
        $nameRes = $sku->getNames();
        $valRes = $sku->getValues();
        $skuNames = $skuVals = array();
        if($nameRes){
            foreach ($nameRes as $k=>$v){
                if(empty($v['name'])) continue;
                $skuNames[] = $v;
            }
        }
        if($valRes){
            foreach ($valRes as $_k=>$_v){
                if (empty($_v['name'])) continue;
                $skuVals[$_v['id']] = $_v['name'];
            }
        }
        $this->_view->assign('skuNames', $skuNames);
        $this->_view->assign('skuVals', $skuVals);
    }

    /**
     * 传递运费模板相关信息
     */
    protected function assignProductTpl(){
        $tpl = new Admin_DeliveryTplModel();
        $res = $tpl->getTpls(array('pageSize' => 9999));
        $this->_view->assign('tpls', $res);
    }

### 商品管理 START ###
    public function product_indexAction(){
        # 传递商品分类信息
        $this->assignProductCat();
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
            'totalItems' => (int)$res['count']
        );
        # 传递商品分类信息
        $this->assignProductCat();
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->_view->assign('results',$results);

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
        $pid = (int)$this->_request->getPost('opt_id');
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

    #单个商品字段更新[目前支持推荐、排序、库存]
    public function product_fieldAction(){
        $pid = (int)$this->_request->getPost('opt_id');
        $field = (string)$this->_request->getPost('field');
        $type = (int)$this->_request->getPost('type');
        $returnArr = array();
        $returnArr['code'] = 200;
        if ($pid && $type && $field && in_array($field, array('hot','order','stock'))){
            $item = new Admin_ItemModel();
            $data = $item->getList(array('numIid' => $pid));
            if($data) {
                $params = $data['results'][0];
                if (!array_key_exists('deliveryTemplateId',$params)){
                    $returnArr['code'] = 400;
                    $returnArr['msg'] = '操作失败,没有设置运费模板，请编辑商品添加后再试';
                    $this->ajaxReturn($returnArr);
                }
                if($field == 'hot') {
                    $params['hot'] = ($type == 1) ? false : true;
                }elseif ($field == 'order'){
                    $params['order'] = $type;
                    $params['useCrmPoints'] = false;
                }elseif ($field == 'stock'){
                    $params['stock'] = $type;
                }
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

    #商品编辑
    public function product_modifyAction(){
        $this->assignProductCat(true);  # 商品分类
        $this->assignProductTag(); # 商品标签
        $this->assignSkuInfo(); # 规格信息
        $this->assignProductTpl(); # 运费模板

        $this->display();
    }

    #商品添加或更新
    public function product_addAction(){
        if($this->_request->isPost()){
            $postData = $this->_request->getPost();
            $postData['upFile'] = $_FILES;

        }
    }

    #商品回收站列表
    public function product_trashAction(){
        $curPage = $this->_request->get('page', 1);
        $id = trim($this->_request->getRequest('id'));
        $name = trim($this->_request->getRequest('name'));
        $cid = trim($this->_request->getRequest('cid'));

        $conditions = array();
        $conditions['inRecyleBin'] = 1;
        $conditions['curPage'] = $curPage;
        if (!empty($id)) $conditions['numIid'] = $id;
        if (!empty($name)) $conditions['name'] = $name;
        if (!empty($cid)) $conditions['cid'] = $cid;

        $item = new Admin_ItemModel();
        $res = $item->getList($conditions);
        if (!$res) exit('数据获取失败！');
        $pageOptions = Array(
            'perPage' => 10,
            'currentPage' => $curPage,
            'curPageClass' => 'active',
            'totalItems' => $res['count']
        );
        # 传递商品分类信息
        $this->assignProductCat();
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->_view->assign('results',$res['results']);

        $this->display();
    }

    #商品删除
    public function product_removeAction(){
        $pid = (int)$this->_request()->getRequest('id');
        $returnArr = array();
        $returnArr['code'] = 200;
        if ($pid){
            $item = new Admin_ItemModel();
            $res = $item->removeItem($pid);
            if ($res) {
                $returnArr['msg'] = '操作成功';
            } else {
                $returnArr['code'] = 401;
                $returnArr['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnArr);
    }
### 商品管理 END ###

### 分类管理 START ###
    public function cat_indexAction(){
        $this->display();
    }

    # 分类列表
    public function cat_listAction(){
        $curPage = (int)$this->_request->get('page', 1);
        $conditions['curPage'] = $curPage;

        $itemCat = new Admin_ItemCatModel();
        $res = $itemCat->getCat($conditions);
        if (!$res) exit('数据获取失败！');

        $pageOptions = Array(
            'perPage' => 10,
            'currentPage' => $curPage,
            'curPageClass' => 'active',
            'totalItems' => (int)$res['count']
        );

        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->_view->assign('results', $res['results']);
        $this->display();
    }

    # 添加分类到热推
    public function cat_hotAction(){
        $cid = (int)$this->_request->getPost('opt_id');
        $isHot = (int)$this->_request->getPost('type');
        $returnArr = array();
        $returnArr['code'] = 200;
        if($cid && in_array($isHot,array(1,2))){
            $itemCat = new Admin_ItemCatModel();
            $data = array();
            $data['isHot'] = ($isHot==1)?false:true;
            $res = $itemCat->hotCat($cid, $data);
            if ($res) {
                $returnArr['msg'] = '操作成功';
            } else {
                $returnArr['code'] = 401;
                $returnArr['msg'] = '操作失败';
            }
        }
        $this->ajaxReturn($returnArr);
    }

    # 分类删除
    public function cat_delAction(){
        $cid = (int)$this->_request->getRequest('id');
        $returnArr = array();
        $returnArr['code'] = 200;
        if($cid) {
            $item = new Admin_ItemModel();
            $res = $item->getList(array('cid' => $cid));
            if(!$res){
                $returnArr['code'] = 401;
                $returnArr['msg'] = '分类下的商品信息获取失败';
                $this->ajaxReturn($returnArr);
            }
            if($res['count']>0){
                $returnArr['code'] = 402;
                $returnArr['msg'] = '该分类下存在有商品，不能删除该分类。';
                $this->ajaxReturn($returnArr);
            }
            $cat = new Admin_ItemCatModel();
            $res = $cat->delCat($cid);
            if(!$res){
                $returnArr['code'] = 403;
                $returnArr['msg'] = '操作失败';
            }else{
                $returnArr['msg'] = '操作成功';
            }
        }
        $this->ajaxReturn($returnArr);
    }

    # 分类编辑和添加处理接口
    public function cat_updateAction(){
        $data = $this->_request->getPost();
        $data['icon'] = $_FILES['icon'];
        $itemCat = new Admin_ItemCatModel();
        $res = $itemCat->modifyCat($data);
        $this->ajaxReturn($res);
    }

    # 分类编辑或添加展示
    public function cat_modifyAction(){
        $this->display();
    }
### 分类管理 END ###
}
<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/28
 * Time: 16:11
 */
class Vmallnew_ConfigController extends Vmallnew_BaseController{

### 基础设置 START ###
    public function base_indexAction(){
        $this->display();
    }

    # 基础设置
    public function base_mallAction(){
        // 获取会员规则
        $cardCategoryClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.User.CardCategory'));
        $cardRuleClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.User.CardRule'));
        # 返回商家所有会员卡类型
        $result = $cardCategoryClient->sync('listCategories', array('bid'=>SELLER_ID, 'ccAttribute'=>1));
        $CardRules = $cardRuleClient->sync('listCardRules', array(SELLER_ID));
        $cardCats = array();
        if ($CardRules) {
            foreach ($CardRules as $k => $v) {
                #$cardCats[$v['ccid']] = $result[$v['ccid']]['ccName'];
                $cardCats[$k+1] = $result[$v['ccid']]['ccName'];
            }
        }
        $this->_view->assign('cardCats',$cardCats);

        $mallSet = new Admin_MallSetModel();
        $res = $mallSet->getInfo();
        if (!$res) exit('获取数据失败');

        $this->_view->assign('data', $res['results']);
        $this->display();
    }

    # 保存设置
    public function base_saveAction(){
        $data = $this->_request->getPost();
        if(!empty($_FILES['logo']['name'])) $data['logo_file'] = $_FILES['logo'];
        if($data){
            $mallSet = new Admin_MallSetModel();
            $res = $mallSet->save($data);
            $this->ajaxReturn($res);
        }
    }

    # 轮播设置
    public function banner_listAction(){
        $banner = new Admin_BannerSetModel();
        $res = $banner->getInfo();
        if(!$res) exit('获取数据失败');

        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('parentCid' => 0));
        $catArr = array_column($catRes['results'],'name','id');
        $this->_view->assign('catArr', $catArr);

        $total = count($res['results']);
        $this->_view->assign('total', $total);
        $this->_view->assign('results', $res['results']);
        $this->display();
    }

    # 添加或修改轮播设置页面
    public function banner_modifyAction(){
        $data = $this->_request->getRequest();
        if($data) $this->_view->assign('data',$data);

        $itemCat = new Admin_ItemCatModel();
        $catRes = $itemCat->getCat(array('parentCid' => 0));
        $catArr = array_column($catRes['results'],'name','id');
        $this->_view->assign('Cats', $catArr);

        $this->_view->assign('typeArr', Admin_BannerSetModel::$typeArr);
        $this->display();
    }

    # 处理广告添加或修改
    public function banner_updateAction(){
        $data = $this->_request->getPost();
        if (!empty($_FILES['bannerUrl']['name'])) $data['bannerUrl_file'] = $_FILES['bannerUrl'];
        if($data){
            $banner = new Admin_BannerSetModel();
            $res = $banner->update($data);
            $this->ajaxReturn($res);
        }
    }

    # 删除广告
    public function banner_delAction(){
        $id = $this->getRequest()->getParam('id');
        if ($id){
            $banner = new Admin_BannerSetModel();
            $res = $banner->del($id);
            $returnData = array();
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
### 基础设置 END ###

### 运费模板 START ###
    public function tpl_indexAction(){
        $this->display();
    }

    # 模板列表
    public function tpl_listAction(){
        $curPage = $this->_request->get('page', 1);
        $tpl = new Admin_DeliveryTplModel();
        $res = $tpl->getTpls(array('curPage'=>$curPage), false);
        if (!$res) exit('获取数据失败');
        #echo '<pre>';print_r($res);die;
        $pageOptions = Array(
            'perPage' => 10,
            'currentPage' => $curPage,
            'curPageClass' => 'active',
            'totalItems' => $res['count']
        );
        $this->_view->assign('page', Pager::makeLinks($pageOptions));
        $this->_view->assign('results', $res['results']);
        $this->display();
    }

    private function formatTplData(array $data){
        if($data){
            if (!empty($data['templateDests'])){
                $city = Config::get(APP_NAME.'.citydata.city');
                $templateDests = explode(';',$data['templateDests']);
                $areas_e = $areas_c = array();
                if($templateDests){
                    foreach ($templateDests as $k=>$v){
                        if(empty($v)) continue;
                        $areas_e[] = str_replace(',','-',$v);
                        $cityArr = array_map(function($vv) use ($city){
                            return $city[$vv];
                        }, explode(',',$v));
                        $areas_c[] = implode('-',$cityArr);
                    }
                }
                $data['areas_e'] = $areas_e;
                $data['areas_c'] = $areas_c;
            }
            $data['templateStartStandards'] = explode(';',$data['templateStartStandards']);
            $data['templateStartFees'] = explode(';',$data['templateStartFees']);
            $data['templateAddStandards'] = explode(';',$data['templateAddStandards']);
            $data['templateAddFees'] = explode(';',$data['templateAddFees']);
        }
        return $data;
    }

    # 模板添加或编辑页面展示
    public function tpl_modifyAction(){
        $data = $this->_request->getRequest();
        if($data){
            $data = $this->formatTplData($data);
            $this->_view->assign('data',$data);
        }
        $this->display();
    }

    # 模板添加或编辑处理
    public function tpl_updateAction(){
        $data = $this->_request->getPost();
        if ($data){
            $tpl = new Admin_DeliveryTplModel();
            $res = $tpl->update($data);
            $this->ajaxReturn($res);
        }
    }

    # 模板删除
    public function tpl_delAction(){
        $id = $this->getRequest()->getParam('id');
        if($id){
            $tpl = new Admin_DeliveryTplModel();
            $returnData = array();
            $returnData['code'] = 200;
            $res = $tpl->del($id);
            if($res){
                $returnData['msg'] = '操作成功';
            }else{
                #$returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
            $this->ajaxReturn($returnData);
        }
    }
### 运费模板 END ###

### 支付设置 START ###
    public function pay_indexAction(){
        $this->display();
    }

    # 支付设置页面
    public function pay_infoAction(){
        $bind = new Admin_BindModel();
        # $res = $bind->getInfo();
        $res = false;
        if($res){
            $this->_view->assign('results', $res['results']);
        }
        $this->display();
    }

    # 编辑支付设置
    public function pay_editAction(){
        $data = $this->_request->getPost();
        if($data){
            $bind = new Admin_BindModel();
            $res = $bind->edit($data);
            $this->ajaxReturn($res);
        }
    }
### 支付设置 END ###

}
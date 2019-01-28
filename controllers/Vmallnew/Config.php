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
        if($_FILES['logo']) $data['logo_file'] = $_FILES['logo'];
        if($data){
            $mallSet = new Admin_MallSetModel();
            $res = $mallSet->save($data);
            $this->ajaxReturn($res);
        }
    }

### 基础设置 END ###
}
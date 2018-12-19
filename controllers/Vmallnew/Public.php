<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/12
 * Time: 17:17
 */
class Vmallnew_PublicController extends Vmallnew_BaseController{

    public function init(){
        # 如果不需要检测登录状态，则必须覆盖父类的初始化方法
    }

    public function loginAction(){
        if ($this->isLogin()){
            $this->redirect('/');
        }
        if($this->_request->isPost()){
            $returnData = array();
            $returnData['code'] = 200;
            $returnData['return_url'] = '/';
            $postData = $this->getRequest()->getPost();
            $expired = isset($postData['autologin'])?'+30 days':'+1 day';
            if (!$this->_checkCaptcha($postData['captcha'])){
                $returnData['code'] = 401;
                $returnData['msg'] = '验证码错误';
                $this->ajaxReturn($returnData);
            }

            try{
                $condition = array();
                $condition['mStatus'] = array('!='=>9);
                $username = trim($postData['username']);
                if (empty($username) || empty($postData['password'])){
                    $returnData['code'] = 410;
                    $returnData['msg'] = '账号或密码不能为空！';
                    $this->ajaxReturn($returnData);
                }
                if(is_numeric($username) && strlen($username)==10){
                    $condition['mid'] = $username;
                }else{
                    $condition['mAliasName'] = $username;
                }
                $managerClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.Manager.Manager'));
                $paramsArr = array(
                    'conditions'=>$condition,
                    'pageOptions'=>array(),
                    'order'=>array(),
                    'fields'=>array('mid','mAliasName','bid')
                );
                $manager = $managerClient->sync('listByConditions', $paramsArr);
                //$this->ajaxReturn($manager);
                if (empty($manager['data'])){
                    $returnData['code'] = 402;
                    $returnData['msg'] = '该账号不存在或已被禁用';
                    $this->ajaxReturn($returnData);
                }
                $manager = reset($manager['data']);
                $loginData = array();
                $loginData['mid'] = $manager['mid'];
                $loginData['password'] = $postData['password'];
                $loginData['ip'] = WL_Helper_ClientGetObj::getIP();
                $loginData['loginWay'] = $this->_loginWay;
                $loginData['sid'] = 0;
                $loginData['bid'] = $manager['bid'];
                $loginData['os'] = WL_Helper_ClientGetObj::getOS();
                $loginData['browser'] = $_SERVER['HTTP_USER_AGENT'];
                $loginData['expired'] = $expired;
                //$loginData['fingerprint'] = '';
                $managers = $managerClient->sync('login', $loginData);
                //$this->ajaxReturn($managers);
                if (empty($managers)){
                    $returnData['code'] = 403;
                    $returnData['msg'] = '帐号或密码不正确!';
                    $this->ajaxReturn($returnData);
                }
                if (!empty($managers['multi']) || !empty($managers['message'])){
                    /*if (!empty($managers['message']) && count($managers['multi']<1)){
                        $returnData['code'] = 404;
                        $returnData['msg'] = $managers['message'];
                        $this->ajaxReturn($returnData);
                    }
                    $managerMulti = array();
                    $managers = $managers['multi'];
                    foreach ($managers as $bid=>$manager){
                        if ($manager['mRoleId'] == Consts::get('Manager', 'Manager_src_Entity_Manager', 'ROLE_SHOP_CASHIER')
                         || $this->_checkPermission($manager['mRoleId'], $manager['mIsRegion'])){
                            unset($managers[$bid]);continue;
                        }
                        $this->_cookieExpired = strtotime($expired);

                        $bizClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.Shop.Biz'));
                        $bizInfo = $bizClient->sync('findOneByBid', array($bid));
                        if (empty($bizInfo) || $bizInfo['bIsOffline'] ||  # 商家已在 BOSS 后台下线
                           $bizInfo['bCrmVersion'] == Consts::get('Shop', 'Shop_src_Entity_Biz', 'VERSION_STARTUPS')
                            # 检测是否是创业版本
                        ){
                            unset($managers[$bid]);continue;
                        }

                        unset($manager['multi']['mmPassword']);
                        $managerMulti[$bid] = $manager['multi'];
                    }

                    // 多账户数据
                    $manager = reset($managers);
                    $mid = $manager['mid'];
                    $this->_setLogin($loginData['bid'], $mid, $manager);
                    $this->_setCookie('manager_autologin', $postData['autologin']);
                    $this->_setCookie('manager_authorize_notice_flag', true);
                    if (1==count($managerMulti)){
                        $this->ajaxReturn($returnData);
                    }else{
                        $this->_setCookie('manager_multi', json_encode($managerMulti));
                        $returnData['return_url'] = '/multi';
                        $this->ajaxReturn($returnData);
                    }*/
                }else{
                    if(false === $this->_checkLoginRole($managers['mRoleId'])){
                        $returnData['code'] = 405;
                        $returnData['msg'] = '您没有权限登陆';
                        $this->ajaxReturn($returnData);
                    }
                    $this->_cookieExpired = strtotime($expired);
                    $bizClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.Shop.Biz'));
                    $bizParams = array(
                        'bid'=>$loginData['bid'],
                        'fields'=>array('bIsOffline','bCrmVersion','bBrandName'),
                        'onlyBiz'=>true
                    );
                    $bizInfo = $bizClient->sync('findOneByBid', $bizParams);
                    //$this->ajaxReturn($bizInfo);
                    if (!empty($bizInfo['bIsOffline'])){  # 商家已在 BOSS 后台下线
                        $returnData['code'] = 406;
                        $returnData['msg'] = '服务已被禁用，需开通请联系服务商';
                        $this->ajaxReturn($returnData);
                    }else{
                        # 检测是否是创业版本
                        $versionType = Consts::get('Shop', 'Shop_src_Entity_Biz', 'VERSION_STARTUPS');
                        if (empty($bizInfo) || $bizInfo['bCrmVersion'] == $versionType){
                            $returnData['code'] = 406;
                            $returnData['msg'] = '创业版暂无法使用此功能';
                            $this->ajaxReturn($returnData);
                        }else{
                            $this->_setLogin($loginData['bid'],$managers['mid'],$managers);
                            $this->_setCookie('manager_biz_name', $bizInfo['bBrandName']);
                            $this->_setCookie('manager_multi', '');
                            $this->_setCookie('manager_autologin', $postData['autologin']);
                            $this->_setCookie('manager_authorize_notice_flag', true);
                            $this->ajaxReturn($returnData);
                        }
                    }
                }
            }catch (Exception $e){
                Logger::error($e->getMessage());
                $returnData['code'] = 400;
                $returnData['msg'] = $e->getMessage();
                $this->ajaxReturn($returnData);
            }
        }

        # 加载登录界面
        try {
            $this->display('login');
        }catch (Exception $e){
            Logger::error($e->getMessage());
        }
    }

    /**
     * @param $mRoleId
     * @return bool
     */
    private function _checkLoginRole($mRoleId) {
        //只有超管可登录
        $mCashRoleId = Consts::get('Manager', 'Manager_src_Entity_Manager', 'ROLE_ADMINISTRATOR');
        if ($mRoleId > $mCashRoleId ) {
            return false;
        }
        return true;
    }

    /**
     * 设置登录状态
     * @param $bid
     * @param $mid
     * @param $manager
     * @return bool
     * @throws Exception
     */
    private function _setLogin($bid,$mid,$manager){
        $loginInfo = array();
        $loginInfo['mid'] = $mid;
        $loginInfo['loginWay'] = $this->_loginWay;

        $managerLoginLogClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.Manager.ManagerLoginLog'));
        $data = $managerLoginLogClient->sync('get', $loginInfo);
        $loginInfo['sid'] = '';
        $loginInfo['bid'] = $bid;
        $loginInfo['ip'] = WL_Helper_ClientGetObj::getIP();
        $loginInfo['os'] = WL_Helper_ClientGetObj::getOS();
        $loginInfo['browser'] = WL_Helper_ClientGetObj::getBrowser();
        $loginInfo['expired'] = date('Y-m-d H:i:s', $this->_cookieExpired ? intval($this->_cookieExpired) : (time() + 86400));
        if (empty($data)){
            $managerLoginLogClient->sync('add',$loginInfo);
        }else{
            $loginInfo['updateSid'] = true;
            $loginInfo['loginFailTimes'] = 0;
            $loginInfo['loginFailTime'] = date('Y-m-d H:i:s', Date('U'));
            $managerLoginLogClient->sync('update',$loginInfo);
        }
        $sid = json_decode($manager['mShops'],true);
        $sid = $sid['all']==1 ? 0 : $sid['list'][0];
        $this->_setCookie('manager_mName', $manager['mName']);
        $this->_setCookie('manager_bid', $bid);
        $this->_setCookie('manager_mid', $mid);
        $this->_setCookie('manager_sid', $sid);
        $this->_setCookie('manager_sign', $this->_generateSign($bid, $mid));
        $this->_setCookie('manager_authorize_notice_flag', true);

        return true;
    }

    public function logoutAction(){
        $this->_cookieExpired = strtotime('-1 day');
        $this->_setCookie('manager_mid', '');
        $this->_setCookie('manager_sid', '');
        $this->_setCookie('manager_bid', '');
        $this->_setCookie('manager_sign', '');
        $this->_setCookie('manager_multi', '');
        $this->_setCookie('manager_mName', '');
        $this->_setCookie('manager_biz_name', '');
        $this->redirect("/login");
    }

    public function captchaAction() {
        Yaf_Dispatcher::getInstance()->disableView();
        $captcha = Image_Captcha::getInstance();
        $textStr = APP_NAME.'_captcha_text';
        $this->_setCookie($textStr, Util_String::encrypt(strtolower($captcha->getText())));
        echo $captcha->createImage();
    }

    /**
     * 验证码检测
     * @param $str
     * @return bool
     */
    private function _checkCaptcha($str){
        if(empty($str)) return false;
        $textStr = APP_NAME.'_captcha_text';
        $text = Util_String::decrypt($this->_request->getCookie($textStr));
        $this->_setCookie($textStr,'');
        return $text==strtolower($str);
    }

    /**
     * @param null $mRoleId
     * @param null $mIsRegion
     * @return bool
     */
    private function _checkPermission($mRoleId=null, $mIsRegion=null){
        try{
            if (empty($mRoleId)){
                $manager = $this->_getCurrentManager();
                $mRoleId = &$manager['mRoleId'];
                $mIsRegion = &$manager['mIsRegion'];
            }
            $result = false;
            if($this->_getLoginInfo('bid')){
                $result = $this->checkPermission($mRoleId,$this->_request->getRequestUri(),$mIsRegion);
            }elseif(!empty($mRoleId)){
                $result = $this->checkPermissionNew($mRoleId,$this->_request->getRequestUri(),$mIsRegion);
            }
            return $result;
        } catch (Exception $e){
            Logger::error($e);
            return false;
        }
    }

    /**
     * @param $roleId
     * @param $uri
     * @param $isRegion
     * @return bool
     * @throws WeLife_src_Rpc_YarClient_Exception_Manager
     */
    private function checkPermission($roleId,$uri,$isRegion){
        if (in_array($uri, array('/', '/logout'))) return true;
        $authorities = WeLife_src_Rpc_YarClient_ManageAuthority::getResourceByRole($this->_getLoginInfo('bid'), $roleId, $isRegion);
        $resources = WeLife_src_Rpc_YarClient_ManageResource::listResources(array_column($authorities,'maResourceId'));
        foreach ($resources as $resource) {
            if ($this->_checkUri($uri, $resource['mrUrl']) || preg_match('/^' . str_replace('/', '\/', $resource['mrUrl']) . '$/', $uri)) {
                return $this->_checkIsAllow($roleId, array($roleId));
            }
        }
        return false;
    }

    /**
     * @param $roleId
     * @param $uri
     * @param $isRegion
     * @return bool
     * @throws WeLife_src_Rpc_YarClient_Exception_Manager
     */
    private function checkPermissionNew($roleId, $uri, $isRegion) {
        if (in_array($uri, array('/', '/logout'))) return true;
        $isValid=false;
        //系统万能权限的url
        $systemRights= Config::get("WeLife.manage.systemRights");
        if ($systemRights) {
            $systemUrls = array_column($systemRights, 'url');
            foreach($systemUrls as $resource){
                if ($this->_checkUri($uri, $resource) || preg_match('/^' . str_replace('/', '\/', $resource) . '$/', $uri)) {
                    $isValid=true;
                    break;
                }
            }
        }
        if($isValid===false){
            if($roleId!=3){
                $isRegion=0;
            }
            $authorities = WeLife_src_Rpc_YarClient_ManageAuthority::getResourceByRoleNew($this->_getLoginInfo('bid'), $roleId, $isRegion);
            $resources = WeLife_src_Rpc_YarClient_ManageResource::listResourcesNew(array_column($authorities,'m1rrResourceId'));
            foreach ($resources as $resource) {
                if ($this->_checkUri($uri, $resource['mrUrl']) || preg_match('/^' . str_replace('/', '\/', $resource['mrUrl']) . '$/', $uri)) {
                    return $this->_checkIsAllow($roleId, array($roleId));
                }
            }
        }else{
            return true;
        }
        return false;
    }

    /**
     * @param $uri
     * @param $url
     * @return bool
     */
    private function _checkUri($uri, $url) {
        if (empty($url)) return false;
        $uri = rtrim($uri, '/');
        $newUrl = explode('?', $url)[0];
        //如果uri 地址为/controller/action 或  /controller/action/都要匹配
        if ($uri == $newUrl || $uri . '/' == $newUrl) {
            return true;
        }
        return false;
    }

    /**
     * @param $currentRoleId
     * @param $roleIds
     * @return bool
     */
    private function _checkIsAllow($currentRoleId, $roleIds) {
        if (empty($roleIds)) {
            $roleIds = Config::get('WeLife.manage.default_roles');
        }
        return in_array($currentRoleId, $roleIds);
    }

    //超级管理员和管理员多个时展示
    public function multiAction() {
        try {
            if (!$this->_checkSign()) {
                return $this->redirect('/logout');
            }

            $multiManagerList = json_decode($this->_request->getCookie('manager_multi'), TRUE);

            $type = $this->_request->get('type');
            if ($type == 1) {
                $bid = $this->_request->get('bid');
                $mid = $this->_request->get('mid');
                if ($bid && $mid) {


                    if (empty($multiManagerList)) {
                        return $this->_errorMessage('切换失败！');
                    }
                    $bids = array_keys($multiManagerList);
                    $mids = array_column($multiManagerList, 'mid');


                    if (empty($bids) || empty($mids) || !in_array($bid, $bids) || !in_array($mid, $mids)) {

                        return $this->_errorMessage('切换失败！');
                    }

                    $autologin = json_decode($this->_request->getCookie('manager_autologin'), TRUE);
                    $expired = $autologin ? '+30 days' : '+1 day';
                    $this->_cookieExpired = strtotime($expired);

                    $yarModel = new WeLife_models_Yar();
                    $manager = $yarModel->yarServer('Manager.Manager', 'getInfo', array($mid));
                    $this->_setLogin($bid, $mid, $manager);

                    $this->_setCookie('manager_multi', json_encode($multiManagerList));
                    return $this->redirect($this->_urlPrefix . '/home');
                }

            } else {
                $industry = Config::get('WeLife.partner.Biz.industry');
                $multiManagerListSorted = array();

                foreach ($multiManagerList as $bid => $val) {
                    $mid = $val['mid'];
                    $val['bid'] = $bid;
                    $roleInfo = $this->getRoleInfo($mid);
                    $val['roleName'] = $roleInfo['m1rRoleName'];
                    $multiManagerListSorted[$mid] = $val;
                }
                sort($multiManagerListSorted);

                $this->_view->assign('industry', $industry);
                $this->_view->assign('multiManagerList', $multiManagerListSorted);

            }
            echo $this->_view->render('vmall/index/multi.phtml');
            return false;
        }catch (Exception $e){
            Logger::error($e->getMessage());
            return $this->_errorMessage($e->getMessage(),'/index');
        }
    }


    public function tipsAction(){
        $this->display();
    }
}
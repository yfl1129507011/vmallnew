<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/12
 * Time: 16:29
 */
class Vmallnew_BaseController extends Yaf_Controller_Abstract {
    protected $_loginWay = 'manage';
    protected $_cookieExpired = '';

    public function init(){
        if (!$this->isLogin()){
            if($this->_request->isXmlHttpRequest()){
                $this->redirect('/tips');
            }else {
                $this->redirect('/login');
            }
        }
        $this->_view->assign('req_uri', $this->_request->getRequestUri());

        $this->_view->assign('ctrl_name', strtolower(strstr($this->_request->getControllerName(),'_')));
        $action_name = strtolower($this->_request->getActionName());
        if (strpos($action_name,'_') !== false){
            $action_name = strstr($action_name,'_',true);
        }
        $this->_view->assign('action_name', $action_name);

        $this->_view->assign('managerName', $this->_getLoginInfo('mName'));
        $this->_view->assign('bBrandName', $this->_getLoginInfo('biz_name'));

        // 提取未读消息数
        $message = new Admin_MessageModel();
        $msgCnt = $message->getUnreadCnt();
        $this->_view->assign('_msgCnt', $msgCnt);
    }

    /**
     * 封装模板展示方法
     * @param string $view_path
     * @param array|NULL $tpl_vars
     */
    protected function display($view_path='', array $tpl_vars=NULL){
        if(strpos($view_path, '/') === false) {
            $ctrlName = $this->_request->getControllerName();
            $viewRoot = implode('/', array_map('lcfirst', explode('_', $ctrlName)));
            $viewExt = Yaf_Registry::get('application')->application->view->ext;
            if (empty($view_path)){
                $actName = strtolower($this->_request->getActionName());
                $view_path = $viewRoot.'/'.$actName.'.'.$viewExt;
            }else{
                $view_path = $viewRoot.'/'.$view_path.'.'.$viewExt;
            }
        }
        $this->_view->display($view_path, $tpl_vars);
        exit();
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option  320 = JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
     * 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data, $type = 'json', $json_option = 320)
    {
        //Yaf_Dispatcher::getInstance()->disableView();
        switch (strtoupper($type)) {
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSON':
            default:
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $json_option));
        }
    }

    /**
     * 设置Cookie信息
     * @param $name
     * @param $value
     */
    protected function _setCookie($name, $value) {
        $path = Config::get('WeLife.ptlogin.domain.path');
        $domain = Config::get('WeLife.ptlogin.domain.domain');
        //var_dump($this->_cookieExpired);die;
        if ($this->_cookieExpired) {
            setcookie($name, $value, $this->_cookieExpired, $path, $domain);
        } else {
            setcookie($name, $value, strtotime('+1 hour'), $path, $domain);
        }
    }

    protected function _getCurrentManager(){
        static $manager;
        if (empty($manager)){
            try{
                $managerClient = new WL_Rpc_YarClient(Config::get('WeLife.rpc.yarServerUrl.Manager.Manager'));
                $manager = $managerClient->sync('getInfo', array($this->_getLoginInfo('mid')));
            } catch (Exception $e){
                Logger::error($e);
                return false;
            }
        }
        return $manager;
    }

    protected function _getLoginInfo($key){
        if (empty($key)){
            $info = null;
        }else{
            $info = $this->_request->getCookie('manager_'.$key);
        }
        return $info;
    }

    protected function _generateSign($bid,$mid){
        return md5(Config::get('WeLife.welife.manage_login_key').$bid.$mid);
    }

    /**
     * 检测是否登录
     */
    protected function isLogin() {
        $bid = $this->_getLoginInfo('bid');
        $mid = $this->_getLoginInfo('mid');
        //$manager = $this->_getCurrentManager();
        if (empty($bid) || empty($mid)){
            return false;
        }
        $checkSign = $this->_generateSign($bid, $mid) == $this->_request->getCookie('manager_sign');
        if($checkSign){
            define('SELLER_ID', $bid);
            return true;
        }
        return false;
    }
}
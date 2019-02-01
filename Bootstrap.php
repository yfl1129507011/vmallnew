<?php
class Bootstrap extends Yaf_Bootstrap_Abstract {
    private $_config;

    # 注册配置信息
    public function _initBootstrap(){
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('application', $this->_config);
    }

    public function _initModules(Yaf_Dispatcher $dispatcher){

    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher){
        $dispatcher->registerPlugin(new TestPlugin());
    }

    // 注册本地类前缀
    public function _initLoader() {
        // 指定本地加载目录：App/src/, _global_library 由application.ini指定
        Yaf_Loader::getInstance(ROOT_PATH)->registerLocalNameSpace(
            array(
                'Activity','Plugin','Employee','Message','Shop',
                'Stat','User','WeLife','Coupon','Manager','SMS',
                'Trade','WeChat','GrpcClient','AliUser',APP_NAME
            )
        );
        Bootstrap_Env::set('app_name', APP_NAME);
    }

    # 添加路由协议
    public function _initRoute(Yaf_Dispatcher $dispatcher){
        if (!defined('ROUTE_FILE')) return false;
        $routes = Config::get(APP_NAME.'.route.'.ROUTE_FILE.'.regex');
        if(is_array($routes)){
            foreach ($routes as $k=>$v){
                $dispatcher->getRouter()->addRoute(
                    $k,new Yaf_Route_Regex($v['match'],$v['route'],$v['map'])
                );
            }
        }
    }
}

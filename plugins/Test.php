<?php

class TestPlugin extends Yaf_Plugin_Abstract {
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        //Logger::debug("routerStartUp");
    }
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/
        //Logger::debug("routerShutdown");
    }
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        //Logger::debug("dispatchLoopStartup");
    }
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        //Logger::debug("preDispatch");
        if (!WL_Rpc_YarClient::$_controller) {
            WL_Rpc_YarClient::$_controller = $request->getControllerName();
        }
        if (!WL_Rpc_YarClient::$_action) {
            WL_Rpc_YarClient::$_action = $request->getActionName();
        }

    }
    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        //Logger::debug("postDispatch");
    }
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* final hoook
         *                in this hook user can do loging or implement layout */
        if (getenv('APPENV') == 'beta') {
            $filename = '/data/rpc-count/' . WL_Rpc_YarClient::$_controller . '-' . WL_Rpc_YarClient::$_action . '-' . WL_Rpc_YarClient::$_syncCount . '.log';
            file_put_contents($filename, 'view time: ' . date('Y-m-d H:i:s') . "\n" , FILE_APPEND);
            foreach (WL_Rpc_YarClient::$_sync as $v) {
                file_put_contents($filename, "\t" . $v . "\n" , FILE_APPEND);
            }

        }
        //Logger::debug("dispatchLoopShutdown");
    }
}

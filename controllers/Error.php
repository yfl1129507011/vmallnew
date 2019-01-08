<?php

class ErrorController extends Yaf_Controller_Abstract {
    public function errorAction($exception){
        Yaf_Dispatcher::getInstance()->disableView();
        var_dump($exception->getMessage());
    }
}
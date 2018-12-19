<?php
error_reporting(E_ALL^E_STRICT^E_NOTICE);
define("DS", '/');
define("APP_PATH", realpath(dirname(dirname(__FILE__))).DS);
define("APP_NAME", basename(APP_PATH));
define("ROOT_PATH", realpath(dirname(dirname(dirname(__FILE__)))).DS);
$app = new Yaf_Application(APP_PATH."config/application.ini");  # 加载Yaf和用户共用的配置空间
try{
    define('ROUTE_FILE', strtolower(APP_NAME));
    $app->bootstrap();  # 加载APP_PATH.'Bootstrap.php'类文件并自动运行前缀为_init的方法
    Benchmark::mark(ROUTE_FILE.'_request_begin'); # 标记运行时间
    $app->run();
} catch (Exception $e){
    //Logger::error($e);
    echo $e->getMessage();die;
}


<?php
try {
    header("Content-Type: text/html;charset=utf-8");
    // 定义应用程序目录
    define('ROOT_PATH', __DIR__);
    define('APP_PATH', realpath('..') . '/../');

    //调用配置文件
    $config = include(APP_PATH . 'app/config/config.php');

    // 初始化各种自动装载器
    require_once APP_PATH . '/app/config/loader.php';

    // 注入各种服务
    require APP_PATH . '/app/config/services.php';

    // 处理请求
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

} catch(\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
}

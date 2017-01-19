<?php

// 调用 composer 的 autoload.php
include_once APP_PATH . '/app/vendor/autoload.php';

// Register an autoloader
$loader = new \Phalcon\Loader();
	
// Phalcon类装载查找路径
$loader->registerDirs(array(
		APP_PATH . $config->application->controllersDir,
		APP_PATH . $config->application->modelsDir,
		APP_PATH . $config->application->viewsDir,
		APP_PATH . $config->application->pluginsDir,
		APP_PATH . $config->application->libraryDir,
))->register();

// 注册Thrift Service类自动加载
$thriftLoader = new \Thrift\ClassLoader\ThriftClassLoader();
$thriftLoader->registerNamespace('Services',  APP_PATH . '/app/microservices');
$thriftLoader->registerDefinition('Services', APP_PATH . '/app/microservices');
$thriftLoader->register();


<?php

$di = new \Phalcon\DI\FactoryDefault();

// 配置文件注入
$di->set('config', function() use ($config){
	return $config;
});


// 会员系统微服务注入
$di->setShared('member_service', function() use ($config) {
	try{
		$memberservice_host = $config->microservice->memberservice_hosts[rand(0, count($config->microservice->memberservice_hosts) - 1)];

		$socket_member = new \Thrift\Transport\TSocket($memberservice_host->host, $memberservice_host->port);
		$transport_member = new \Thrift\Transport\TFramedTransport($socket_member);
		$protocol_member = new \Thrift\Protocol\TBinaryProtocol($transport_member);
		$transport_member->open();
		$member_service = new \Services\Member\MemberClient($protocol_member);
		return $member_service;
	} catch(Exception $e) {
		echo "memberService初始化失败，错误码：".$e->getCode() .",错误原因：".$e->getMessage();
		error_log("memberService初始化失败，错误码：".$e->getCode() .",错误原因：".$e->getMessage());
		exit();
	}
});


// ilisten系统微服务注入
$di->setShared('ilisten_service', function() use ($config) {
	try{
		$ilistenservice_host = $config->microservice->ilistenservice_hosts[rand(0, count($config->microservice->ilistenservice_hosts) - 1)];

		$socket_ilisten = new \Thrift\Transport\TSocket($ilistenservice_host->host, $ilistenservice_host->port);
		$transport_ilisten = new \Thrift\Transport\TFramedTransport($socket_ilisten);
		$protocol_ilisten = new \Thrift\Protocol\TBinaryProtocol($transport_ilisten);
		$transport_ilisten->open();
		$ilisten_service = new \Services\ilisten\ilistenClient($protocol_ilisten);
		return $ilisten_service;

	} catch(Exception $e) {
		echo "ilistenService初始化失败，错误码：".$e->getCode() .",错误原因：".$e->getMessage();
		error_log("ilistenService初始化失败，错误码：".$e->getCode() .",错误原因：".$e->getMessage());
		exit();
	}
});


$di->setShared('message_service', function() use ($config) {
	try{
		$messageservice_host = $config->microservice->messageservice_hosts[rand(0, count($config->microservice->messageservice_hosts) - 1)];

		$socket_message = new \Thrift\Transport\TSocket($messageservice_host->host, $messageservice_host->port);
		$transport_message = new \Thrift\Transport\TFramedTransport($socket_message);
		$protocol_message = new \Thrift\Protocol\TBinaryProtocol($transport_message);
		$transport_message->open();
		$message_service = new \Services\Message\MessageClient($protocol_message);
		return $message_service;

	} catch(Exception $e) {
		echo "messageService初始化失败，错误码：".$e->getCode() .",错误原因：".$e->getMessage();
	}
});


$di->set('db', function() use ($config){

    $dbMain = $config->database_main->hosts[rand(0, count($config->database_main->hosts) - 1)];
	$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
			"host" => $dbMain->host,
            "port" => $dbMain->port,
			"username" => $config->database_main->username,
			"password" => $config->database_main->password,
			"dbname" => $config->database_main->name,
			"charset" => $config->database_main->charset,
	));
	
	return $connection;
});



// View 注入
$di->set('view', function() use ($config, $di) {
	$view = new \Phalcon\Mvc\View();
	$view->setViewsDir(APP_PATH . $config->application->viewsDir);
	$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
	$volt->setOptions(
		array(
			'compiledPath'  => APP_PATH . $config->volt->cachePath,
		)
	);

	/**
	 * Register Volt
	 */
	$view->registerEngines(array('.htm' => $volt));
	return $view;
});




// url注册
$di->set('url', function(){
	$url = new Phalcon\Mvc\Url();
	$url->setBaseUri('/');		//默认index
	return $url;
});




// 调度器注册
$di->set('dispatcher', function() use ($di) {

    $eventsManager = $di->getShared('eventsManager');
    $security = new Security($di);
    $eventsManager->attach('dispatch', $security);

    //调度器
    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});

// cookes
$di->set('cookies', function(){
    $cookies = new Phalcon\Http\Response\Cookies();
    //不加密
    $cookies->useEncryption(false);
    return $cookies;
});



//redis
$di->setShared('redis', function() use ($config){

	$redisHost = $config->redis->host;
	$redis = new Redis();
	$redis->connect($redisHost->host, $redisHost->port);
	return $redis;

});



// ACL 列表注入
$di->set('acl', function() use ($config) {
	$acl = new \Phalcon\Acl\Adapter\Memory();
	//默认操作为拒绝
	$acl->setDefaultAction(\Phalcon\Acl::DENY);

	//创建访问者权限
	$roleGuests = new \Phalcon\Acl\Role('Guests');
	$roleAdmins = new Phalcon\Acl\Role('Admins');

	$acl->addRole($roleGuests);
	$acl->addRole($roleAdmins);

	//添加资源
	$acl->addResource(new Phalcon\Acl\Resource('index'), array('index','goodsList','history','myHistory','light'));
	$acl->addResource(new Phalcon\Acl\Resource('main'), array('index'));

	//权限下的Controller
 	$acl->allow('Guests', 'index', '*');
//	$acl->allow('Guests', 'main', '*');
	//权限下的Controller
	$acl->allow('Admins', 'index', '*');
	$acl->allow('Admins', 'main', '*');


	return $acl;
});


$di->set('router', function () {
	$router = new \Phalcon\Mvc\Router();
	$router->add('/2016Draw/:controller/:action/:params', array(
			'controller' => 1,
			'action' => 2,
			'params' => 3,
	));

	$router->add('/2016Draw/:controller/', array(
			'controller' => 1,
			'action' => 'index',
	));

	$router->add('/2016Draw/', array(
			'controller' => 'index',
			'action' => 'index',
	));

	return $router;
});

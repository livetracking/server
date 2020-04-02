<?php
// DIC configuration

$container = $app->getContainer();

// Custom not found handler (404)
// curl -i "http://localhost:8080/404"
$container['notFoundHandler'] = function ($container) {
	return function ($request, $response) use ($container) {
		return $container['response']
			->withJson(array('error' => 'NOT_FOUND'), 404);
	};
};

// Custom not allowed handler (405)
$container['notAllowedHandler'] = function ($container) {
	return function ($request, $response, $methods) use ($container) {
		return $container['response']
			->withStatus(405)
			->withHeader('Allow', implode(', ', $methods))
			->withHeader('Content-type', 'text/html')
			->withJson(array('error' => 'METHOD_NOT_ALLOWED'));
	};
};

// Custom error handler (500) if displayErrorDetails != true
if (!$container->get('settings')['displayErrorDetails']) {
	$container['errorHandler'] = function ($container) {
		return function ($request, $response, $exception) use ($container) {
			return $container['response']
				->withJson(array('error' => 'UNKNOWN_ERROR'), 500);
		};
	};
}

// The slim/twig-view component
// https://packagist.org/packages/slim/twig-view
$container['view'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	
	$view = new \Slim\Views\Twig($settings['template_path'], [
		'cache' => false
	]);
	
	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
	
	return $view;
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

// PDO database library
// https://github.com/FaaPz/PDO
$container['db'] = function ($c) {
	$settings = $c->get('settings')['db'];
	$pdo = new \FaaPz\PDO\Database($settings['dsn']);
	return $pdo;
};

// InfluxDB client library
// https://github.com/influxdata/influxdb-php
$container['influxdb'] = function ($c) {
	$settings = $c->get('settings')['influxdb'];
	$client = new InfluxDB\Client(
		$settings['hostname'],
		$settings['port'],
		$settings['username'],
		$settings['password'],
		false, // ssl
		false, // verifySSL
		3); // 3 sec. timeout
	return $client;
};
// Retention Policy
$container['rp'] = function ($c) {
	// keep data 8h
	$rp = new \InfluxDB\Database\RetentionPolicy('livetracking', '8h', 1, true);
	return $rp;
};

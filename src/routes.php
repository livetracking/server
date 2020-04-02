<?php

/***
 * Setting up CORS
 * https://www.slimframework.com/docs/cookbook/enable-cors.html
 * Note to myself...
 * InfluxDB has an automatic CORS header:
 *   Access-Control-Allow-Headers: Accept, Accept-Encoding, Authorization, Content-Length, Content-Type, X-CSRF-Token, X-HTTP-Method-Override
 *   Access-Control-Allow-Methods: DELETE, GET, OPTIONS, POST, PUT
 *   Access-Control-Allow-Origin: http://localhost:8080
 *   Access-Control-Expose-Headers: Date, X-InfluxDB-Version
 */
$app->options('/{routes:.+}', function ($request, $response, $args) {
	return $response;
});
$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Routes

// Index
$app->get('/', function ($request, $response, $args) {
	return $this->view->render($response, 'index.html');
});

// Ping
$app->get('/ping', function ($request, $response, $args) {
	$error = '';
	// Try to connect with InfluxDB
	try {
		$root_database = $this->influxdb->selectDB('root');
		$result = $root_database->listRetentionPolicies();
	} catch (Exception $e) {
		$error = $e;
	}
	// Return status
	if ($error) {
		return $response->withJson(array('error' => 'DB'), 500);
	} else {
		return $response->withJson(array('status' => 'OK'), 200);
	}
});

// Return current time in seconds, milliseconds and nanoseconds
$app->get('/time', function ($request, $response, $args) {
	// Current Unix timestamp
	$seconds = time();
	// Current Unix timestamp with microseconds
	$milliseconds = microtime(true)*1000;
	// Nanosecond precision
	// This is default as per InfluxDB standard
	$nanoseconds = $milliseconds*1000000;
	// Return
	return $response->withJson(array(
		'seconds' => $seconds,
		'milliseconds' => intval($milliseconds),
		'nanoseconds' => intval($nanoseconds)
	), 200);
});

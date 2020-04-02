<?php
return [
	'settings' => [
		// TODO Set to false in production
		'displayErrorDetails' => true, 
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		
		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],
		
		// Database
		'db' => [
			'dsn' => 'sqlite:' . __DIR__ . '/../database/users.sqlite',
		],
		
		// InfluxDB
		'influxdb' => [
			// TODO Set hostname, username and passwort for InfuxDB
			// Use Bash script database/influxdb.sh to create user
			'hostname' => '127.0.0.1',
			'port' => '8086',
			'username' => 'ADMIN-USERNAME',
			'password' => 'ADMIN-PASSWORD',
		],
		
		// Monolog settings
		'logger' => [
			'name' => 'livetracking',
			'path' => __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
	],
];

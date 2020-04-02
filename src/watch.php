<?php
/**
 * Watch
 *
 */

$app->get('/watch/{username}', function ($request, $response, $args) {
	if ($args) {
		$req_username = get_req_value($args, 'username');
		$username = check_username($req_username);
	}
	
	$influxdb_url = '';
	$http_host = $_SERVER['HTTP_HOST'];
	if ($http_host == '127.0.0.1:8080') {
		$influxdb_url = 'http://127.0.0.1:8086';
	}
	
	if ($username) {
		$user['username'] = $username;
		return $this->view->render($response,
			'watch.html',
			array(
				'influxdb_url' => $influxdb_url,
				'user' => $user
			)
		);
	} else {
		return $response->withStatus(404)->write(
			'<h1>User Not Found</h1>'
		);
	}
	
})->setName('watch');
<?php
/**
 * Register
 *
 * curl --data "username=TEST" \
 *      --data "password=test1234" http://localhost:8080/register
 */
$app->post('/register', function ($request, $response, $args) {
	// POST variables
	$req_post = $request->getParsedBody();
	$req_username = get_req_value($req_post, 'username');
	$req_password = get_req_value($req_post, 'password');
	
	// Validate
	$errors = array();
	if (check_username($req_username)) {
		$selectUser = $this->db->select()
			->from('users')
			->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $req_username ) )
			->limit( new FaaPz\PDO\Clause\Limit( 1,0 ) );
		$data = $selectUser->execute()->fetch();
		if ($data) {
			array_push($errors,"USERNAME_DOUBLE");
		}
	} else {
		array_push($errors,"INVALIDATE_USERNAME");
	}
	if (!check_password($req_password)) {
		array_push($errors,"INVALIDATE_PASSWORD");
	}
	
	// Save and return
	if ($errors) {
		return $response->withJson(array('errors' => $errors), 400);
	} else {
		// Create new InfluxDB database for user with a retention policy
		$user_database = $this->influxdb->selectDB($req_username);
		$user_database->create($this->rp);
		// Add a new user without privileges
		$this->influxdb->admin->createUser($req_username, $req_password);
		// Give user all privileges on database
		$this->influxdb->admin->grant(\InfluxDB\Client\Admin::PRIVILEGE_ALL, $req_username, $req_username);
		// Grant read permissions to user 'public'
		$this->influxdb->admin->grant(\InfluxDB\Client\Admin::PRIVILEGE_READ, 'public', $req_username);
		// Hash password
		$password_hash = password_hash($req_password, PASSWORD_DEFAULT);
		// Save new user
		$insertStatement = $this->db->insert(array(
			"username"      => $req_username,
			"password_hash" => $password_hash
		))->into('users');
		// Get new user ID
		$newUserId = $insertStatement->execute();
		if ($newUserId > 0) {
			// Return with user data
			return $response->withJson(array(
				'status' => 'OK',
				'id' => $newUserId,
				'username' => $req_username), 200);
		} else {
			return $response->withJson(array('error' => 'DB'), 500);
		}
	}
});

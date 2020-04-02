<?php

/**
 * Get users
 *
 * curl "http://localhost:8080/users"
 */
$app->get('/users', function ($request, $response, $args) {	
	// Validate
	$selectUsersData = array();
	$selectUsers = $this->db->select()
		->from('users')
		->limit( new FaaPz\PDO\Clause\Limit( 500,0 ) );
	$selectUsersData = $selectUsers->execute()->fetchAll();
	
	// Remove private data
	if ($selectUsersData) {
		foreach($selectUsersData as $userDataKey => $userDataParam) {
			unset($selectUsersData[$userDataKey]['password_hash']);
		}
	}
	
	// Return
	return $response->withJson(array('users' => $selectUsersData), 200);
});

/**
 * Show user
 *
 * curl -u test:test1234 "http://localhost:8080/user"
 * curl -u test:test1234 "http://localhost:8080/user/nils"
 */
$app->get('/user[/{username}]', function ($request, $response, $args) {
	// Default own username
	$auth_username = $_SERVER['PHP_AUTH_USER'];
	$req_username = $auth_username;
	// Override username
	if ($args) {
		$req_username = get_req_value($args, 'username');
	}
	
	// Validate
	$errors = array();
	if (check_username($req_username)) {
		$selectUser = $this->db->select()
			->from('users')
			->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $req_username ) )
			->limit( new FaaPz\PDO\Clause\Limit( 1,0 ) );
		$selectUserData = $selectUser->execute()->fetch();
		if ($selectUserData) {
			// Remove password_hash
			unset($selectUserData['password_hash']);
		} else {
			array_push($errors,"USERNAME_NOT_FOUND");
		}
	} else {
		array_push($errors,"INVALIDATE_USERNAME");
	}
	
	// Return
	if ($errors) {
		return $response->withJson(array('errors' => $errors), 400);
	} else {
		return $response->withJson(array('user' => $selectUserData), 200);
	}
});

/**
 * Delete the user and all its data
 *
 * curl -u test:test1234 -X DELETE \
 *     --data "password=test1234" \
 *     "http://localhost:8080/user"
 */
$app->delete('/user', function ($request, $response, $args) {
	$auth_username = $_SERVER['PHP_AUTH_USER'];
	// POST variables
	$req_post = $request->getParsedBody();
	$req_password = get_req_value($req_post, 'password');
	
	// Validate
	$errors = array();
	if (!check_password($req_password)) {
		array_push($errors,"INVALIDATE_PASSWORD");
	}
	
	// Save and return
	if ($errors) {
		return $response->withJson(array('errors' => $errors), 400);
	} else {
		$selectUser = $this->db->select()
			->from('users')
			->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $auth_username ) )
			->limit( new FaaPz\PDO\Clause\Limit( 1,0 ) );
		$selectUserData = $selectUser->execute()->fetch();
		if (password_verify($req_password, $selectUserData['password_hash'])) {
			// Delete user from InfluxDB
			$this->influxdb->admin->dropUser($auth_username);
			// Delete database
			$user_database = $this->influxdb->selectDB($auth_username);
			$user_database->drop();
			// Delete user
			$deleteStatement = $this->db->delete()
				->from('users')
				->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $auth_username ) );
			$affectedRows = $deleteStatement->execute();
			if ($affectedRows) {
				return $response->withJson(array('status' => 'OK'), 200);
			} else {
				return $response->withJson(array('error' => 'DB'), 500);
			}
		} else {
			return $response->withJson(array('error' => 'FORBIDDEN'), 403);
		}
	}
});

/**
 * Change password
 *
 * curl -u blafa:test1234 \
 *     --data "password=test1234" \
 *     --data "new_password=TEST1234" \
 *     "http://localhost:8080/user/change-password"
 */
$app->post('/user/change-password', function ($request, $response, $args) {
	$auth_username = $_SERVER['PHP_AUTH_USER'];
	// POST variables
	$req_post = $request->getParsedBody();
	$req_password     = get_req_value($req_post, 'password');
	$req_new_password = get_req_value($req_post, 'new_password');
	
	// Validate
	$errors = array();
	if (!check_password($req_password)) {
		array_push($errors,"INVALIDATE_PASSWORD");
	}
	if (!check_password($req_new_password)) {
		array_push($errors,"INVALIDATE_NEW_PASSWORD");
	}
	
	// Save and return
	if ($errors) {
		return $response->withJson(array('errors' => $errors), 400);
	} else {
		$selectUser = $this->db->select()
			->from('users')
			->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $auth_username ) )
			->limit( new FaaPz\PDO\Clause\Limit( 1,0 ) );
		$selectUserData = $selectUser->execute()->fetch();
		if (password_verify($req_password, $selectUserData['password_hash'])) {
			// Change password for user on InfluxDB
			$this->influxdb->admin->changeUserPassword($auth_username, $req_new_password);
			// Hash new password
			$password_hash = password_hash($req_new_password, PASSWORD_DEFAULT);
			$updateStatement = $this->db->update(array('password_hash' => $password_hash))
				->table('users')
				->where( new FaaPz\PDO\Clause\Conditional( "username", "=",  $auth_username ) );
			$affectedRows = $updateStatement->execute();
			if ($affectedRows) {
				return $response->withJson(array('status' => 'OK'), 200);
			} else {
				return $response->withJson(array('error' => 'DB'), 500);
			}
		} else {
			return $response->withJson(array('error' => 'FORBIDDEN'), 403);
		}
	}
});

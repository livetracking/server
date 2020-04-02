<?php

/**
 * Create SQL for default users with random password.
 *
 * $ php default_users.php | sqlite3 users.sqlite
 *
 */

$users = array('root', 'public', 'admin', 'administrator', 'livetracking');

echo "BEGIN TRANSACTION;\n";
foreach($users as $username) {
	// Generate new password
	$password = '';
	$size = mt_rand(50, 60);
	$characters = array_merge(range('a','z'), range('0','9'), array('_', '-', '!'));
	$max = count($characters) - 1;
	for ($i = 0; $i < $size; $i++) {
		$rand = mt_rand(0, $max);
		$password .= $characters[$rand];
	}
	// Hash new password
	$password_hash = password_hash($password, PASSWORD_DEFAULT);
	printf("INSERT INTO `users` (username,password_hash) VALUES ('%s','%s');\n", $username, $password_hash);
}
echo "COMMIT;\n";
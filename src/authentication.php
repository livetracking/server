<?php
/***
 * HTTP Basic Authentication
 * https://github.com/tuupola/slim-basic-auth
 *
 */
use Tuupola\Middleware\HttpBasicAuthentication\PdoAuthenticator;

// PDO authenticator
$settings = $container->get('settings')['db'];
$authenticator_pdo = new PDO($settings['dsn']);

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
	"path" => ["/auth", "/user"],
	"realm" => "Protected",
	"authenticator" => new PdoAuthenticator([
		"pdo" => $authenticator_pdo,
		"table" => "users",
		"user" => "username",
		"hash" => "password_hash"
	]),
	"callback" => function ($request, $response, $arguments) {
		// Fix for PHP header bug: https://github.com/tuupola/slim-basic-auth/issues/33
		$_SERVER['PHP_AUTH_USER'] = $arguments['user'];
		$_SERVER['PHP_AUTH_PW']   = $arguments['password'];
	},
	"error" => function ($response, $arguments) {
		return $response->withJson(array('error' => 'AUTHENTICATION_FAILED'), 403);
	}
]));

// Check HTTP Basic Authentication
$app->get('/auth', function ($request, $response, $args) {
	$auth_username = $_SERVER['PHP_AUTH_USER'];
	// Return
	return $response->withJson(array(
		'username' => $auth_username,
		'status' => 'OK'
	), 200);
});

<?php

/**
 * Get variable from array
 * like from $req->getParsedBody() or $req->getQueryParams()
 * and return if found
 */
function get_req_value($req, $name)
{
	if ($req && $name) {
		foreach($req as $key => $param){
			if ($key == $name) {
				if ($name == 'username') {
					return strtolower($param);
				} else {
					return $param;
				}
				break;
			}
		}
	}
}

/**
 * Validate username and return if true
 * Username must start with characters.
 *
 */
function check_username($username)
{
	// http://www.phpliveregex.com/
	if (preg_match('/^[A-Z][A-Z\d_]{3,19}$/i', $username)) {
		// Disable username '_internal'
		if ($username == '_internal') {
			return false;
		// Disable username 'password'
		} elseif ($username == 'password') {
			return false;
		} else {
			return strtolower($username);
		}
	} else {
		return false;
	}
}

/**
 * Validate password and return if true
 *
 */
function check_password($password)
{
	if (preg_match('/^[^\'\;\"]{8,100}$/', $password)) {
		return $password;
	} else {
		return false;
	}
}

<?php
/**
* Copyright (C) 2017-2020 Nils Knieling
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
* As a special exception, the copyright holders give permission to link the
* code of portions of this program with the OpenSSL library under certain
* conditions as described in each individual source file and distribute
* linked combinations including the program with the OpenSSL library. You
* must comply with the GNU Affero General Public License in all respects for
* all of the code used other than as permitted herein. If you modify file(s)
* with this exception, you may extend this exception to your version of the
* file(s), but you are not obligated to do so. If you do not wish to do so,
* delete this exception statement from your version. If you delete this
* exception statement from all source files in the program, then also delete
* it in the license file.
*/

if (PHP_SAPI == 'cli-server') {
	// To help the built-in PHP dev server, check if the request was actually for
	// something which should probably be served as a static file
	$url  = parse_url($_SERVER['REQUEST_URI']);
	$file = __DIR__ . $url['path'];
	if (is_file($file)) {
		return false;
	}
}

require __DIR__ . '/../vendor/autoload.php';

//session_start();

// Instantiate the app
require __DIR__ . '/../src/helpers.php';
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Set up authentication
require __DIR__ . '/../src/authentication.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Default routes
require __DIR__ . '/../src/routes.php';

// Register (/register),
require __DIR__ . '/../src/register.php';

// User (/user)
require __DIR__ . '/../src/user.php';

// Watch (/watch)
require __DIR__ . '/../src/watch.php';

// Run app
$app->run();

<?php
header("HTTP/1.1 404 Not Found");
header('Content-Type: application/json');
echo json_encode(array('error' => 'NOT_FOUND'));
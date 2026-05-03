<?php
header('Content-Type: text/plain; charset=UTF-8');

$uploads_dir = '/var/www/html/uploads';
$name = isset($_GET['name']) ? trim($_GET['name']) : '';

if ($name === '' || strpos($name, '/') !== false || strpos($name, "\0") !== false) {
    http_response_code(400);
    echo "invalid play file\n";
    exit;
}

$path = realpath($uploads_dir . '/' . $name);
$base = realpath($uploads_dir);

if ($path === false || $base === false) {
    http_response_code(404);
    echo "not found\n";
    exit;
}

$prefix = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (strpos($path, $prefix) !== 0 || !is_file($path) || !is_readable($path)) {
    http_response_code(404);
    echo "not found\n";
    exit;
}

readfile($path);

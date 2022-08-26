<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/basic_functions.php';

$name = trim(strip_tags($_POST['name']));

$results = mongo_find('filters', ['name'=> $name], ['typeMap' => ['array'=>'array', 'document'=>'array', 'root'=>'array']]);

if (count($results) < 1) {
    header("HTTP/1.0 404 Not Found");
    echo "Filter not found";
    exit();
}

$result = $results[0];

$_SESSION['last_filter_name'] = $result['name'];

unset($result['name']);

if (isset($result['from'])) {
    $result['from'] = $result['from']->toDateTime()->getTimestamp();
}

if (isset($result['to'])) {
    $result['to'] = $result['to']->toDateTime()->getTimestamp();
}

$_SESSION['filter'] = $result;

$db_filter = get_current_filter($result);

$_SESSION['db_filter'] = $db_filter;

$current_count = mongo_count('images', $db_filter);

$_SESSION['current_count'] = $current_count;

$_SESSION['max_page'] = ceil($current_count / 50);

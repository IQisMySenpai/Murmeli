<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/basic_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

$filters = input_to_filter($_POST);

$_SESSION['last_filter_name'] = '';

$_SESSION['filter'] = $filters;

$db_filter = get_current_filter($_SESSION['filter']);

$_SESSION['db_filter'] = $db_filter;

$current_count = mongo_count('images', $db_filter);

$_SESSION['current_count'] = $current_count;

$_SESSION['max_page'] = ceil($current_count / 50);

<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/basic_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

$filters = input_to_filter($_POST['inputs']);

if (isset($filters['from'])) {
    $filters['from'] = new MongoDB\BSON\UTCDateTime($filters['from']*1000);
}

if (isset($filters['to'])) {
    $filters['to'] = new MongoDB\BSON\UTCDateTime($filters['to']*1000);
}

$name = trim(strip_tags($_POST['name']));

$filters['name'] = $name;

$_SESSION['last_filter_name'] = $name;

if (mongo_count('filters', ['name'=> $name]) > 0) {
    mongo_delete_many('filters', ['name'=> $name]);
}

mongo_insert_one('filters', $filters);

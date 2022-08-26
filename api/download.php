<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

$id = (string) strip_tags(trim($_GET['id']));

$json = mongo_find('images', ['_id' => new MongoDB\BSON\ObjectId($id)], ['typeMap' => ['array'=>'array', 'document'=>'array', 'root'=>'array']]);

$json = $json[0] ?? [];

$webID = $json['webID'];

$json = json_encode($json, JSON_PRETTY_PRINT);

$tmpName = $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $webID . '.json';

file_put_contents($tmpName, $json);

header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header(sprintf('Content-Disposition: attachment; filename=%s.json', $webID));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tmpName));

ob_clean();
flush();
readfile($tmpName);

unlink($tmpName);
<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/basic_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

if (!isset($_SESSION['max_page'])) {
    $_SESSION['db_filter'] = ['error_code'=> ['$exists' => false]];

    $_SESSION['current_count'] = mongo_count('images', $_SESSION['db_filter']);

    $_SESSION['max_page'] = ceil($_SESSION['current_count'] / 50);
}

$page = intval(trim(strip_tags($_GET['page'] ?? 1)));

$results = mongo_find('images', $_SESSION['db_filter'], ['sort'=> ['date' => 1, 'webID'=> 1], 'skip'=> (50*($page - 1)),'limit'=> 50,'typeMap' => ['array'=>'array', 'document'=>'array', 'root'=>'array']]);

$images = '<div class="images">';

foreach ($results as $result) {
    if (($result['error_code'] ?? 0) > 0) {
        $images .= sprintf('<div id="%s" class="image_wrapper"><a href="/img?id=%s"><div class="no_image">Error %s, while crawling</div></a></div>', (string) $result['_id'], (string) $result['_id'], $result['error_code']);
    } else {
        if (isset($result['src'])) {
            $images .= sprintf('<div id="%s" class="image_wrapper"><a href="/img?id=%s"><img onerror="error_on_load(this);" src="%s" class="image"></a></div>', (string) $result['_id'], (string) $result['_id'], $result['src']);
        } else {
            $images .= sprintf('<div id="%s" class="image_wrapper"><a href="/img?id=%s"><div class="no_image">Image src couldn\'t be found</div></a></div>', (string) $result['_id'], (string) $result['_id']);
        }
    }
}

$images .= '</div>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Murmeli</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#000000">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <meta name="msapplication-TileColor" content="#b91d47">
    <meta name="msapplication-config" content="/images/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" type="text/css" href="/stylesheets/widescreen.css">
    <link href="/fontawesome/css/all.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="/script/main.js"></script>
</head>
<body>
<div class="nav">
    <img class="logo" src="/images/logo.png">
    <a class="nav_item current" href="/">
        Home
    </a>
    <a class="nav_item" href="/filter">
        Filter
    </a>
</div>
<main>
    <?php
    echo $images;

    echo generate_pagination($page, $_SESSION['max_page']);
    ?>
</main>
</body>
</html>

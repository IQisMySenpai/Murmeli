<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

$f = $_SESSION['filter'] ?? [];

function get_options ($default) {
    $options = sprintf('<option value="eq"%s>=</option>', $default === 'eq' ? ' selected' : '');
    $options .= sprintf('<option value="gt"%s>></option>', $default === 'gt' ? ' selected' : '');
    $options .= sprintf('<option value="lt"%s><</option>', $default === 'lt' ? ' selected' : '');
    $options .= sprintf('<option value="gte"%s>≥</option>', $default === 'gte' ? ' selected' : '');
    $options .= sprintf('<option value="lte"%s>≤</option>', $default === 'lte' ? ' selected' : '');
    return $options;
}

$results = mongo_find('filters', [], ['typeMap' => ['array'=>'array', 'document'=>'array', 'root'=>'array']]);

$filters = '';
foreach ($results as $result) {
    $filters .= sprintf('<option value="%s"%s>%s</option>', $result['name'], (($result['name'] === ($_SESSION['last_filter_name'] ?? '')) ? ' selected' : '') , $result['name']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#000000">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <meta name="msapplication-TileColor" content="#b91d47">
    <meta name="msapplication-config" content="/images/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <title>Murmeli - Filter</title>
    <link href="/fontawesome/css/all.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/stylesheets/widescreen.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="/script/main.js"></script>
</head>
<body>
<div class="nav">
    <img class="logo" src="/images/logo.png">
    <a class="nav_item" href="/">
        Home
    </a>
    <a class="nav_item current" href="/filter">
        Filter
    </a>
</div>
<main>
    <div class="buttons">
        <div class="filter_controls">
            <div class="select_wrap">
                <select class="filter_load">
                    <option value="" selected disabled></option>
                    <?php echo $filters; ?>
                </select>
                <i class="fas fa-chevron-down"></i>
            </div>
            <button class="filter_ctl" onclick="load_filter()">
                Load
            </button>
        </div>
        <div class="filter_controls">
            <input type="text" class="filter_save">
            <button class="filter_ctl save" onclick="save_filter()">
                Save
            </button>
        </div>
    </div>
    <div class="filters">
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Web ID:
            </div></div>
            <div class="filter_wrapper">
                <input id="webID" class="short_text" type="text" value="<?php echo $f['webID'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Search (using MongoDB Search):
            </div></div>
            <div class="filter_wrapper">
                <input id="search" class="long_text" type="text" value="<?php echo $f['search'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Regex Search:
            </div></div>
            <div class="filter_wrapper">
                <input id="regex" class="short_text" type="text" value="<?php echo $f['regex'] ?? '';?>">
                <input id="regex_mod" class="short_text" type="text" value="<?php echo $f['regex_mod'] ?? 'i';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                From (dd.mm.yyyy hh:mm:ss):
            </div></div>
            <div class="filter_wrapper">
                <input id="from" class="short_text" type="text" value="<?php echo (isset($f['from']) ? date('d.m.Y H:i:s',$f['from']) : '');?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                To (dd.mm.yyyy hh:mm:ss):
            </div></div>
            <div class="filter_wrapper">
                <input id="to" class="short_text" type="text" value="<?php echo (isset($f['to']) ? date('d.m.Y H:i:s',$f['to']) : '');?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Number of Faces:
            </div></div>
            <div class="filter_wrapper">
                <div class="select_wrap">
                    <select id="face_count_mod">
                        <?php echo get_options($f['face_count_mod'] ?? 'eq');?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <input id="face_count" class="short_text" type="text" value="<?php echo $f['face_count'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Nudity Score:
            </div></div>
            <div class="filter_wrapper">
                <div class="select_wrap">
                    <select id="nudity_mod">
                        <?php echo get_options($f['nudity_mod'] ?? 'lt');?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <input id="nudity" class="short_text" type="text" value="<?php echo $f['nudity'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Predicted Language (seperated by semicolon):
            </div></div>
            <div class="filter_wrapper">
                <input id="lang" class="long_text" type="text">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Size (in bytes):
            </div></div>
            <div class="filter_wrapper">
                <div class="select_wrap">
                    <select id="size_mod">
                        <?php echo get_options($f['size_mod'] ?? 'gt');?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <input id="size" class="short_text" type="text" value="<?php echo $f['size'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Height (in Pixel):
            </div></div>
            <div class="filter_wrapper">
                <div class="select_wrap">
                    <select id="height_mod">
                        <?php echo get_options($f['height_mod'] ?? 'eq');?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <input id="height" class="short_text" type="text" value="<?php echo $f['height'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Width (in Pixel):
            </div></div>
            <div class="filter_wrapper">
                <div class="select_wrap">
                    <select id="width_mod">
                        <?php echo get_options($f['width_mod'] ?? 'eq');?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <input id="width" class="short_text" type="text" value="<?php echo $f['width'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Image Hosting Server:
            </div></div>
            <div class="filter_wrapper">
                <input id="image_host" class="short_text" type="text" value="<?php echo $f['image_host'] ?? '';?>">
            </div>
        </div>
        <div class="filter">
            <div class="filter_wrapper"><div class="name">
                Error Code (ALL, OK, ERR or a number):
            </div></div>
            <div class="filter_wrapper">
                <input id="error_code" class="short_text" type="text" value="<?php echo $f['error_code'] ?? 'OK';?>">
            </div>
        </div>
    </div>
</main>
<button class="submit" onclick="filter();">Filter</button>
</body>
</html>
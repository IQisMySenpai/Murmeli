<?php
    session_start();

    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/lib_mongo.php';

    $id = (string) trim(strip_tags($_GET['id'] ?? ''));
    if ($id === '') {
        header('Location: /');
        exit();
    }

    $results = mongo_find('images', ['_id' => new MongoDB\BSON\ObjectId($id)], ['typeMap' => ['array'=>'array', 'document'=>'array', 'root'=>'array']]);

    if (count($results) < 1) {
        header('Location: /');
        exit();
    }

    $result = $results[0];

    if (!isset($result['error_code'])) {
        $result['error_code'] = 0;
    }

    $js = '';
    if (isset($result['faces'])) {
        $result['face_count'] = count($result['faces']);
        $result['show_faces'] = 1;

        $js_faces = [];
        foreach ($result['faces'] as $face) {
            $js_faces[] = '{' . str_replace('=', ': ', http_build_query($face, '', ', ')) . '}';
        }
        $js = sprintf('<script>load_faces([%s], %s, %s);</script>', implode(', ', $js_faces), $result['height'], $result['width']);
    }

    $keys = [
        '_id',
        'webID',
        'error_code',
        'src',
        'size',
        'width',
        'height',
        'text',
        'lang',
        'show_faces',
        'faces',
        'face_count',
        'nudity',
        'date',
        'retries',
        'message'
    ];

    if (($result['error_code'] ?? 0) > 0) {
        $showcase = sprintf('<div class="showcase not_found">Error %s, while crawling</div>', $result['error_code']);
    } else {
        if (isset($result['src'])) {
            $showcase = sprintf('<div class="showcase"><img src="%s" onerror="not_found(this);"></div>', $result['src']);
        } else {
            $showcase = '<div class="showcase not_found">Image src couldn\'t be found</div>';
            $js = '';
        }
    }

    $attributes = '<div class="attributes_n_buttons"><div class="attributes">';

    foreach ($keys as $key) {
        if (isset($result[$key])) {
            $attributes .= '<div class="attribute"><div class="attribute_title">';
            $attributes .= sprintf('%s:', $key);
            $attributes .= '</div><div class="attribute_value">';
            switch ($key) {
                case 'date':
                    $date = $result[$key]->toDateTime();
                    $attributes .= $date->format('d.m.Y h:i:s');
                    break;
                case 'show_faces':
                    $attributes .= '<label class="switch"><input type="checkbox" onchange="toggle_faces();"><span class="slider"></span></label>';
                    break;
                case 'faces':
                    foreach ($result['faces'] as $face) {
                        $attributes .= str_replace('=', ': ', http_build_query($face, '', ', ')) . '<br>';
                    }
                    break;
                case 'width':
                case 'height':
                    $attributes .= sprintf('%s px', $result[$key]);
                    break;
                case 'src':
                    $attributes .= sprintf('<a class="attribute_src" href="%s" target="_blank">%s</a>', $result[$key], $result[$key]);
                    break;
                case 'size':
                    $attributes .= sprintf('%.2f KB', $result[$key] / 1000);
                    break;
                default:
                    $attributes .= (string) $result[$key];
            }
            $attributes .= '</div></div>';
        }
    }
    $attributes .= '</div>';
    $attributes .= '<div class="buttons">';
    $attributes .= '<button class="sub_button" onclick="download_data();">Download Data <i class="fas fa-download"></i></button></div>';
    $attributes .= '</div>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Murmeli - Image</title>
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
    <a class="nav_item" href="/">
        Home
    </a>
    <a class="nav_item" href="/filter">
        Filter
    </a>
</div>
<main id="<?php echo $id; ?>">
    <div class="image_showcase">
        <?php
        echo $showcase . $attributes;
        ?>
    </div>
</main>
<?php
echo $js;
?>
</body>
</html>

<?php
function generate_pagination ($current_page = 1, $last_page = 10) {
    $pages = '<div class="pagination">';

    $from = 1;
    $to = $last_page;

    if ($last_page > 5) {
        $from = $current_page - 2;
        $to = $current_page + 2;
        if ($from < 1) {
            $calc = 1 - $from;
            $from += $calc;
            $to += $calc;
        } elseif($to > $last_page) {
            $calc = $to - $last_page;
            $from -= $calc;
            $to -= $calc;
        }
    }
    if ($from > 1) {
        $pages .= '<a class="page" href="/?page=1"><i class="fas fa-angle-double-left"></i></a>';
        $pages .= sprintf('<a class="page" href="/?page=%s"><i class="fas fa-angle-left"></i></a>', $current_page - 1);
    }

    for ($i = $from; $i <= $to; $i++) {
        if ($i == $current_page) {
            $pages .= sprintf('<a class="page active" href="/?page=%s">%s</a>', $i, $i);
        } else {
            $pages .= sprintf('<a class="page" href="/?page=%s">%s</a>', $i, $i);
        }
    }

    if ($to < $last_page) {
        $pages .= sprintf('<a class="page" href="/?page=%s"><i class="fas fa-angle-right"></i></a>', $current_page + 1);
        $pages .= sprintf('<a class="page" href="/?page=%s"><i class="fas fa-angle-double-right"></i></a>', $last_page);;
    }

    $pages .= '</div>';

    return $pages;
}

function input_info() {
    return [
        'error_code'=> ['type' => 'error', 'default'=> 'OK'],
        'face_count'=> ['type' => 'int'],
        'face_count_mod'=> ['type' => 'equal', 'default'=> 'eq'],
        'from'=> ['type' => 'date'],
        'height'=> ['type' => 'int'],
        'height_mod'=> ['type' => 'equal', 'default'=> 'eq'],
        'image_host'=> ['type' => 'string'],
        'search'=> ['type' => 'string'],
        'lang'=> ['type' => 'keywords'],
        'nudity'=> ['type' => 'float'],
        'nudity_mod'=> ['type' => 'equal', 'default'=> 'lt'],
        'regex'=> ['type' => 'string'],
        'regex_mod'=> ['type' => 'string'],
        'size'=> ['type' => 'int'],
        'size_mod'=> ['type' => 'equal', 'default'=> 'gt'],
        'to'=> ['type' => 'date'],
        'webID'=> ['type' => 'string'],
        'width'=> ['type' => 'int'],
        'width_mod'=> ['type' => 'equal', 'default'=> 'eq']
    ];
}

function get_current_filter($filters) {
    $f = ['error_code'=> ['$exists' => false]];

    foreach ($filters as $filter => $value) {
        switch ($filter) {
            case 'webID':
                $f['webID'] = $value;
                break;
            case 'search':
                $f['$text'] = ['$search'=> $value];
                break;
            case 'regex':
                $f['text'] = ['$regex' => $value, '$options'=>($filters['regex_mod'] ?? 'i')];
                break;
            case 'image_host':
                $f['src'] = ['$regex' => sprintf('.*%s.*', $value), '$options'=>'i'];
                break;
            case 'error_code':
                switch (strtolower($value)) {
                    case 'all':
                        break;
                    case 'ok':
                        $f['error_code'] = ['$exists' => false];
                        break;
                    case 'err':
                    case 'error':
                        $f['error_code'] = ['$exists' => true];
                        break;
                    default:
                        $f['error_code'] = intval($value);
                }
                break;
            case 'face_count':
            case 'height':
            case 'width':
            case 'size':
            case 'nudity':
                $f[$filter] = [sprintf('$%s', $filters[sprintf('%s_mod', $filter)])=> $value];
                break;
            case 'from':
            case 'to':
                if (!isset($f['date'])) {
                    $f['date'] = [];
                }
                $f['date'][($filter == 'to' ? '$lt' : '$gte')] = new MongoDB\BSON\UTCDateTime($value * 1000);
                break;
            case 'lang':
                $f['lang'] = ['$in'=> $value];
                break;
            default:
        }
    }

    return $f;
}

function input_to_filter ($inputs) {
    $filters = [];

    foreach (input_info() as $input => $info) {
        if (isset($inputs[$input])) {
            $attr = strip_tags(trim($inputs[$input]));
            switch ($info['type']) {
                case 'int':
                    $filters[$input] = intval($attr);
                    break;
                case 'float':
                    $filters[$input] = floatval($attr);
                    break;
                case 'keywords':
                    $filters[$input] = explode(';', $_POST[$input]);
                    break;
                case 'date':
                    $filters[$input] = strtotime($attr);
                    break;
                default:
                    $filters[$input] = $attr;
            }
        }
    }
    return $filters;
}
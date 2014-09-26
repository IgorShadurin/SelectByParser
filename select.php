<?php header('Access-Control-Allow-Origin: *'); ?>
<?php

include('simple_html_dom.php');
error_reporting(E_ALL ^ E_NOTICE);

function getData($html)
{
    $banks = $html->find('#curr_table tbody tr');
    $i = 0;
    $result = [];
    $currentBank = '';
    foreach ($banks as $bank) {
        if (isset($bank->attr['class'])) {
            if ($bank->attr['class'] == 'expand-child') {
                continue;
            }

            echo $bank->attr['class'] . ' | ';
        }

        foreach ($bank->find('td') as $name) {
            if (!$name->innertext) {
                continue;
            }

            //var_dump($name);
            if (isset($name->children[0]->tag) && $name->children[0]->tag == 'a') {
                $currentBank = strip_tags($name->innertext);
                $i = 0;
                continue;
            }

            $i++;
            $val = '';
            switch ($i) {
                case 1:
                    $val = 'USD_SELL';
                    break;
                case 2:
                    $val = 'USD_BUY';
                    break;
                case 3:
                    $val = 'EUR_SELL';
                    break;
                case 4:
                    $val = 'EUR_BUY';
                    break;
                case 5:
                    $val = 'RUR_SELL';
                    break;
                case 6:
                    $val = 'RUR_BUY';
                    break;
            }

            if (!$val) {
                continue;
            }

            // 1 - usd sell
            // 2 - usd buy
            // 3 - eur sell
            // 4 - eur buy
            // 5 - rur sell
            // 6 - rur buy

            //echo $name->innertext . '<br><br>' . "\r\n\r\n";
            $result[$currentBank][$val] = $name->innertext;
        }
    }
	
    return json_encode($result);
}

if (file_exists('select.by.html') && filemtime('select.by.html') + 10 * 60 > time()) {
    $json = file_get_contents('select.by.html');
} else {
    $html = file_get_html('http://select.by/kurs/');
    file_put_contents('select.by.html_', $html);
    $html=file_get_html('select.by.html_');
    $json = getData($html);
    file_put_contents('select.by.html', $json);
}

echo $json;

<?php

$baseLink = 'https://dbistest3.amocrm.ru';
$options = 'USER_LOGIN=dbistest2@test.com&USER_HASH=6acf309ffaa8da6e7171601a260083c672293471';

$partLink = '/api/v2/leads?';

$link = $baseLink . $partLink . $options;

function getInfo($link)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $out = curl_exec($curl);

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $code = (int)$code;

    $errors = [
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    ];

    try {
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $e) {
        die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
    }

    $result = json_decode($out, true);
    $result = $result['_embedded']['items'];

    $result['errors'] = curl_getinfo($curl);

    curl_close($curl);

    return $result;
}


$dealAll = getInfo($link);


echo "<div style='width: 100%; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto;'>";
if (!empty($dealAll)) {
    foreach ($dealAll as $item) {
        if (!empty($item['contacts']) && !empty($item['company'])) {
            echo "<div style='padding: 2rem 1rem; margin-bottom: 2rem; background-color: #e9ecef; border-radius: 0.3rem;'>";
            echo "<h2>{$item['name']}</h2>";
            echo "<h3>{$item['company']['name']}</h3>";

            $partLink = $item['contacts']['_links']['self']['href'];
            $link = $baseLink . $partLink . '&USER_LOGIN=dbistest2@test.com&USER_HASH=6acf309ffaa8da6e7171601a260083c672293471';

            $contacts = getInfo($link);

            foreach ($contacts as $contact) {
                echo "<h3>".$contact['name']."</h3>";
            }
            echo "</div>";
        } else {
            continue;
        }
    }
} else {
    echo "<h3>$dealAll[errors]</h3>";
}

echo "</div>";


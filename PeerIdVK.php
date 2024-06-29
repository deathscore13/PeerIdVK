<?php

/**
 * PeerIdVK
 * 
 * Инструмент для поиска peer_id чатов сообщества в VK для PHP 8.0.0+
 * https://github.com/deathscore13/PeerIdVK
 */

const ACCESS_TOKEN = 'ключ';    // ключ доступа к сообщениям сообщества
const MAX_COUNTS = 20;          // количество id для брута от 2000000001 до 2000000000 + MAX_COUNTS

const API_VERSION = '5.131';
const API_URL = 'https://api.vk.com/method/';
const API_METHOD = 'messages.getConversationsById';

set_time_limit(0);

$maxCounts = $argv[1] ?? $_GET['MAX_COUNTS'] ?? MAX_COUNTS;
if ($maxCounts < 1)
{
    echo('MAX_COUNTS < 1 ('.$maxCounts.')');
    exit();
}

$curl = curl_init(API_URL.API_METHOD);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);

/**
 * Стоковая функция из VMPHP
 * https://github.com/deathscore13/VMPHP
 */
function findBinary(): ?string
{
    static $binary = null;

    if ($binary === null)
        $binary = @file_exists(PHP_BINARY) ? PHP_BINARY : PHP_BINDIR.'/php';

    if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server')
        return $binary;

    $len = strlen($binary);
    $pos = strrpos($binary, '-cgi');
    if (($pos && $len === ($pos + 4)) ||
       (($pos = strrpos($binary, '-fpm')) && $len === ($pos + 4)))
    {
        $bin = substr($binary, 0, $pos);
    }

    $cli = ($bin ?? $binary).'-cli';
    if (@file_exists($cli))
        return $cli;

    if (isset($bin) && @file_exists($bin))
        return $bin;

    return @file_exists($binary) ? $binary : null;
}

$delim = (findBinary() === PHP_BINARY) ? PHP_EOL : '<br>';

$count = 0;
$found = false;
while (++$count <= $maxCounts)
{
    $peerId = 2000000000 + $count;

    curl_setopt($curl, CURLOPT_POSTFIELDS, [
        'access_token' => ACCESS_TOKEN,
        'v' => API_VERSION,
        'peer_ids' => $peerId
    ]);

    $data = json_decode(curl_exec($curl), true);
    if (isset($data['response']['items'][0]))
    {
        $found = true;
        echo($peerId.' = '.$data['response']['items'][0]['chat_settings']['title'].$delim);
    }
}

if (!$found)
    echo('Not found. Try increasing MAX_COUNTS');

curl_close($curl);

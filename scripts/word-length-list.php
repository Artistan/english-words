#!/usr/local/bin/php
<?php

if(empty($argv[1])) {
    echo "provide number\n\n";
    exit;
}
$numb = ($argv[1]) * 1;

$fp = fopen(__DIR__ . '/../words.txt', 'r');
$fpw = fopen(__DIR__ . '/words-' . $numb . '.txt','w');

if ($fp) {
    while (($buffer = fgets($fp, 4096)) !== false) {
        if(strlen(trim($buffer)) == $numb) {
            fwrite($fpw,strtolower($buffer));
        }
    }
    if (!feof($fp)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($fp);
}
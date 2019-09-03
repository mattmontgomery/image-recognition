<?php

require("vendor/autoload.php");

use ImageRecognition\Recognizer;
use ImageRecognition\AwsClient;

$r = new Recognizer(new AwsClient('default'));

$imagePath = __DIR__ . "/test-images/treadmill.jpg";

if ($argv[1] ?? false) {
    $filename = md5($argv[1]);
    $imagePath =__DIR__ . "/test-images/{$filename}";
    file_put_contents($imagePath, fopen($argv[1], 'r'));
}

$result = $r->detect($imagePath);

print_r($r->debugOutput($result));


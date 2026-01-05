<?php
$logPath = 'storage/logs/laravel.log';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    echo substr($content, -3000);
} else {
    echo "Log not found";
}

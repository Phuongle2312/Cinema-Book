<?php
$logPath = 'storage/logs/laravel.log';
if (!file_exists($logPath)) exit("Log not found");
$lines = file($logPath);
$lines = array_reverse($lines);
foreach ($lines as $line) {
    if (strpos($line, 'local.ERROR') !== false) {
        // Find the start of this error (it might be multi-line if stack trace is present)
        // Actually Laravel log errors are usually one line for the message and then stack trace
        echo $line;
        break;
    }
}

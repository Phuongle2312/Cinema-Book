<?php
$content = file_get_contents('seeder_error.txt');
if (preg_match('/Integrity constraint violation: (\d+)/', $content, $matches)) {
    echo "CODE: " . $matches[1] . "\n";
}
if (preg_match('/violation: \d+ (.*?)\(/', $content, $matches)) {
    echo "MSG: " . $matches[1] . "\n";
} else {
    // Try to capture whatever follows the code
    $start = strpos($content, "violation: 104");
    if ($start !== false) {
        echo "RAW: " . substr($content, $start, 100);
    }
}

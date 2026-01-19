<?php
$content = file_get_contents('seeder_error.txt');
$pos = strpos($content, "SQLSTATE");
if ($pos !== false) {
    echo substr($content, $pos, 200);
} else {
    echo "No SQLSTATE found. Content: " . substr($content, 0, 200);
}

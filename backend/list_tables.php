<?php
$tables = DB::select('SHOW TABLES');
echo "Tables in DB:\n";
foreach ($tables as $table) {
    echo array_values((array)$table)[0] . "\n";
}

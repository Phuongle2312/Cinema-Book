<?php

/**
 * Script xuất cấu trúc và dữ liệu database ra file SQL
 * Chạy: php export_database.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel app
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Lấy thông tin database từ .env
$database = env('DB_DATABASE', 'cinema_booking');
$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '3306');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

echo "=== Bắt đầu xuất database: {$database} ===\n\n";

// Tạo output file
$outputFile = __DIR__.'/database/cinema_booking_export_'.date('Y-m-d_His').'.sql';
$handle = fopen($outputFile, 'w');

// Header
fwrite($handle, "-- Cinema Booking System - Database Export\n");
fwrite($handle, '-- Generated: '.date('Y-m-d H:i:s')."\n");
fwrite($handle, "-- Database: {$database}\n");
fwrite($handle, "-- ================================================\n\n");

fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n\n");

// Create database
fwrite($handle, "-- ================================================\n");
fwrite($handle, "-- Database: `{$database}`\n");
fwrite($handle, "-- ================================================\n\n");
fwrite($handle, "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n");
fwrite($handle, "USE `{$database}`;\n\n");

// Lấy danh sách bảng
$tables = DB::select('SHOW TABLES');
$tableKey = "Tables_in_{$database}";

echo 'Tìm thấy '.count($tables)." bảng\n\n";

foreach ($tables as $table) {
    $tableName = $table->$tableKey;

    echo "Xuất bảng: {$tableName}\n";

    fwrite($handle, "-- ================================================\n");
    fwrite($handle, "-- Table: `{$tableName}`\n");
    fwrite($handle, "-- ================================================\n\n");

    // Drop table
    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n\n");

    // Create table
    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
    $createSql = $createTable[0]->{'Create Table'};
    fwrite($handle, $createSql.";\n\n");

    // Get data
    $rows = DB::table($tableName)->get();

    if ($rows->count() > 0) {
        echo "  → {$rows->count()} dòng dữ liệu\n";

        fwrite($handle, "-- Data for table `{$tableName}`\n\n");

        foreach ($rows as $row) {
            $columns = array_keys((array) $row);
            $values = array_values((array) $row);

            // Escape values
            $escapedValues = array_map(function ($value) {
                if (is_null($value)) {
                    return 'NULL';
                } elseif (is_numeric($value)) {
                    return $value;
                } else {
                    return "'".addslashes($value)."'";
                }
            }, $values);

            $columnList = '`'.implode('`, `', $columns).'`';
            $valueList = implode(', ', $escapedValues);

            fwrite($handle, "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n");
        }

        fwrite($handle, "\n");
    } else {
        echo "  → Không có dữ liệu\n";
    }

    fwrite($handle, "\n");
}

// Footer
fwrite($handle, "-- ================================================\n");
fwrite($handle, "-- End of export\n");
fwrite($handle, "-- ================================================\n");

fclose($handle);

echo "\n=== HOÀN TẤT ===\n";
echo "File SQL đã được xuất tại: {$outputFile}\n";
echo 'Kích thước: '.round(filesize($outputFile) / 1024, 2)." KB\n";

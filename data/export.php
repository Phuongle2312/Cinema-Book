<?php
/**
 * Script xuất database - Hỗ trợ cả TABLE và VIEW
 */

require __DIR__ . '/../backend/vendor/autoload.php';

$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$database = env('DB_DATABASE', 'cinema_booking');
$timestamp = date('Y-m-d_His');

echo "=================================================\n";
echo "       XUẤT DATABASE - CINEMA BOOKING           \n";
echo "=================================================\n\n";

// 1. Kiểm tra kết nối
echo "1. Kiểm tra kết nối...\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Kết nối OK: {$database}\n\n";
} catch (\Exception $e) {
    die("   ✗ LỖI: " . $e->getMessage() . "\n");
}

// 2. Lấy danh sách bảng
$tables = DB::select('SHOW FULL TABLES');
$tableKey = "Tables_in_{$database}";
$typeKey = "Table_type";

$tableList = [];
$viewList = [];

foreach ($tables as $t) {
    $name = $t->$tableKey;
    $type = $t->$typeKey;

    if ($type === 'BASE TABLE') {
        $tableList[] = $name;
    } else {
        $viewList[] = $name;
    }
}

echo "2. Tìm thấy " . count($tableList) . " bảng, " . count($viewList) . " view\n\n";

// 3. Thống kê
echo "3. Thống kê dữ liệu:\n";
echo str_repeat("-", 50) . "\n";
printf("%-25s %15s %10s\n", "Tên", "Số dòng", "Loại");
echo str_repeat("-", 50) . "\n";

$stats = [];

// Tables
foreach ($tableList as $table) {
    $count = DB::table($table)->count();
    printf("%-25s %15s %10s\n", $table, number_format($count), "TABLE");
    $stats[$table] = ['count' => $count, 'type' => 'TABLE'];
}

// Views
foreach ($viewList as $view) {
    try {
        $count = DB::table($view)->count();
        printf("%-25s %15s %10s\n", $view, number_format($count), "VIEW");
        $stats[$view] = ['count' => $count, 'type' => 'VIEW'];
    } catch (\Exception $e) {
        printf("%-25s %15s %10s\n", $view, "N/A", "VIEW");
        $stats[$view] = ['count' => 0, 'type' => 'VIEW'];
    }
}

echo str_repeat("-", 50) . "\n\n";

// 4. Xuất schema
echo "4. Xuất schema...\n";
$schemaFile = __DIR__ . "/sql/schema_{$timestamp}.sql";
$sf = fopen($schemaFile, 'w');

fwrite($sf, "-- Cinema Booking Schema\n");
fwrite($sf, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($sf, "CREATE DATABASE IF NOT EXISTS `{$database}`;\n");
fwrite($sf, "USE `{$database}`;\n\n");

// Export Tables
fwrite($sf, "-- ===================\n");
fwrite($sf, "-- TABLES\n");
fwrite($sf, "-- ===================\n\n");

foreach ($tableList as $table) {
    fwrite($sf, "DROP TABLE IF EXISTS `{$table}`;\n");

    $create = DB::select("SHOW CREATE TABLE `{$table}`");
    $createData = json_decode(json_encode($create[0]), true);
    $createSql = $createData['Create Table'];

    fwrite($sf, $createSql . ";\n\n");
}

// Export Views
if (!empty($viewList)) {
    fwrite($sf, "-- ===================\n");
    fwrite($sf, "-- VIEWS\n");
    fwrite($sf, "-- ===================\n\n");

    foreach ($viewList as $view) {
        fwrite($sf, "DROP VIEW IF EXISTS `{$view}`;\n");

        $create = DB::select("SHOW CREATE VIEW `{$view}`");
        $createData = json_decode(json_encode($create[0]), true);
        $createSql = $createData['Create View'];

        fwrite($sf, $createSql . ";\n\n");
    }
}

fclose($sf);
$sizeKB = round(filesize($schemaFile) / 1024, 2);
echo "   ✓ Schema: schema_{$timestamp}.sql ({$sizeKB} KB)\n\n";

// 5. Xuất full backup
echo "5. Xuất full data...\n";
$fullFile = __DIR__ . "/sql/full_backup_{$timestamp}.sql";
$ff = fopen($fullFile, 'w');

fwrite($ff, "-- Cinema Booking Full Backup\n");
fwrite($ff, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($ff, "SET FOREIGN_KEY_CHECKS=0;\n\n");

// Export Tables with data
foreach ($tableList as $table) {
    echo "   → {$table}";

    fwrite($ff, "-- Table: {$table}\n");
    fwrite($ff, "DROP TABLE IF EXISTS `{$table}`;\n");

    $create = DB::select("SHOW CREATE TABLE `{$table}`");
    $createData = json_decode(json_encode($create[0]), true);
    $createSql = $createData['Create Table'];
    fwrite($ff, $createSql . ";\n\n");

    // Data
    $rows = DB::table($table)->get();
    echo " ({$rows->count()} rows)\n";

    foreach ($rows as $row) {
        $cols = array_keys((array) $row);
        $vals = array_values((array) $row);

        $escapedVals = array_map(function ($v) {
            if (is_null($v))
                return 'NULL';
            if (is_numeric($v))
                return $v;
            return "'" . str_replace(["'", "\\"], ["''", "\\\\"], $v) . "'";
        }, $vals);

        $colList = '`' . implode('`, `', $cols) . '`';
        $valList = implode(', ', $escapedVals);

        fwrite($ff, "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList});\n");
    }

    fwrite($ff, "\n");
}

// Export Views
if (!empty($viewList)) {
    foreach ($viewList as $view) {
        echo "   → {$view}";

        fwrite($ff, "-- View: {$view}\n");
        fwrite($ff, "DROP VIEW IF EXISTS `{$view}`;\n");

        $create = DB::select("SHOW CREATE VIEW `{$view}`");
        $createData = json_decode(json_encode($create[0]), true);
        $createSql = $createData['Create View'];
        fwrite($ff, $createSql . ";\n\n");

        echo " (VIEW)\n";
    }
}

fwrite($ff, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($ff);

$fullSizeKB = round(filesize($fullFile) / 1024, 2);
echo "\n   ✓ Full: full_backup_{$timestamp}.sql ({$fullSizeKB} KB)\n\n";

// 6. Copy migrations
echo "6. Copy migrations...\n";
$migDir = __DIR__ . '/../backend/database/migrations';
if (is_dir($migDir)) {
    $migs = glob($migDir . '/*.php');
    $copied = 0;
    foreach ($migs as $mig) {
        copy($mig, __DIR__ . '/migrations/' . basename($mig));
        $copied++;
    }
    echo "   ✓ Đã copy {$copied} files\n\n";
} else {
    $copied = 0;
    echo "   ⚠ Không tìm thấy thư mục migrations\n\n";
}

// 7. Tạo README
echo "7. Tạo README...\n";
$readmeContent = <<<MD
# DATA EXPORT - CINEMA BOOKING SYSTEM

**Ngày xuất**: {$timestamp}  
**Database**: {$database}  
**Số bảng**: " . count($tableList) . "  
**Số view**: " . count($viewList) . "

---

## 📁 Cấu trúc

\`\`\`
data/
├── sql/
│   ├── schema_{$timestamp}.sql       ({$sizeKB} KB)
│   └── full_backup_{$timestamp}.sql  ({$fullSizeKB} KB)
├── migrations/                        ({$copied} files)
└── README.md
\`\`\`

---

## 📊 Thống kê

| Tên | Loại | Số dòng |
|-----|------|---------|
MD;

foreach ($stats as $name => $data) {
    $readmeContent .= "| {$name} | {$data['type']} | " . number_format($data['count']) . " |\n";
}

$readmeContent .= <<<MD


---

## 🔧 Import Database

### Schema Only (Cấu trúc)
\`\`\`bash
mysql -u root {$database} < sql/schema_{$timestamp}.sql
\`\`\`

### Full Backup (Cấu trúc + Dữ liệu)
\`\`\`bash
mysql -u root {$database} < sql/full_backup_{$timestamp}.sql
\`\`\`

### Qua phpMyAdmin
1. Mở http://localhost/phpmyadmin
2. Chọn database `{$database}`
3. Tab "Import"
4. Chọn file SQL
5. Click "Go"

---

## ⚠️ Lưu ý

- File **schema** chỉ chứa cấu trúc (CREATE TABLE/VIEW)
- File **full_backup** chứa cả cấu trúc và dữ liệu
- View `showtimes` (nếu có) sẽ được tự động tạo lại

---

*Generated: {$timestamp}*
MD;

file_put_contents(__DIR__ . '/README.md', $readmeContent);
echo "   ✓ README.md\n\n";

// 8. Summary
echo "=================================================\n";
echo "                  ✓ HOÀN TẤT                     \n";
echo "=================================================\n\n";
echo "📁 Thư mục: data/\n\n";
echo "📄 Files:\n";
echo "   - schema_{$timestamp}.sql ({$sizeKB} KB)\n";
echo "   - full_backup_{$timestamp}.sql ({$fullSizeKB} KB)\n";
echo "   - README.md\n";
echo "   - migrations/ ({$copied} files)\n\n";
echo "📊 Database:\n";
echo "   - Tables: " . count($tableList) . "\n";
echo "   - Views: " . count($viewList) . "\n\n";

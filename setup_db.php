<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

echo "<!DOCTYPE html><html lang='vi'><head><meta charset='utf-8'><title>Setup Database VLXD</title><style>body{font-family:monospace;background:#1e1e1e;color:#00ff00;padding:20px;}.err{color:#ff5555;}</style></head><body><h2>Thiết lập database vatlieu (monolith)</h2><pre>";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    $sqlFile = __DIR__ . '/database/vatlieu.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy file: $sqlFile");
    }

    $pdo->exec(file_get_contents($sqlFile));
    echo "[OK] Đã import schema từ database/vatlieu.sql\n";



    echo "\n<h3 style='color:#00e5ff'>=== HOÀN TẤT ===</h3>\n";
    echo "Truy cập: <a href='http://localhost/vlxd/dangnhap' style='color:orange'>http://localhost/vlxd/dangnhap</a>\n";
} catch (PDOException $e) {
    echo "<span class='err'>LỖI: " . $e->getMessage() . "</span>";
} catch (Exception $e) {
    echo "<span class='err'>LỖI: " . $e->getMessage() . "</span>";
}
echo "</pre></body></html>";

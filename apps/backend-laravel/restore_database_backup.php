<?php

$basePath = dirname(__FILE__);
$backupFile = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . '20260424-093051-kominfo_sangihe.sql';
$envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
$envExampleFile = $basePath . DIRECTORY_SEPARATOR . '.env.example';

function parseDotEnv(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $name = trim($name);
        $value = trim($value);

        if ($value !== '' && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = substr($value, 1, -1);
        }

        if ($value !== '' && $value[0] === "'") {
            $value = substr($value, 1, -1);
        }

        $data[$name] = $value;
    }

    return $data;
}

$env = parseDotEnv($envFile);
if (empty($env)) {
    $env = parseDotEnv($envExampleFile);
}

$driver = strtolower($env['DB_CONNECTION'] ?? 'mysql');
if (!in_array($driver, ['mysql', 'mariadb'], true)) {
    echo "ERROR: Hanya koneksi MySQL/MariaDB yang didukung. DB_CONNECTION saat ini=\"{$driver}\"\n";
    exit(1);
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'kominfo_sangihe';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

if (!is_file($backupFile)) {
    echo "ERROR: File backup tidak ditemukan: {$backupFile}\n";
    exit(1);
}

echo "Restore backup dari: {$backupFile}\n";
echo "Koneksi ke: {$host}:{$port} database {$database}\n";

$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

if (!$mysqli->real_connect($host, $username, $password, '', intval($port))) {
    echo "ERROR: Gagal koneksi ke server MySQL/MariaDB. ({$mysqli->connect_errno}) {$mysqli->connect_error}\n";
    exit(1);
}

if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "ERROR: Gagal membuat/mengecek database {$database}: ({$mysqli->errno}) {$mysqli->error}\n";
    exit(1);
}

if (!$mysqli->select_db($database)) {
    echo "ERROR: Gagal memilih database {$database}: ({$mysqli->errno}) {$mysqli->error}\n";
    exit(1);
}

$handle = fopen($backupFile, 'rb');
if ($handle === false) {
    echo "ERROR: Gagal membuka file backup untuk dibaca.\n";
    exit(1);
}

$statement = '';
$lineNumber = 0;
$hasError = false;
$firstLine = true;

while (($line = fgets($handle)) !== false) {
    $lineNumber++;

    if ($firstLine) {
        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
        $firstLine = false;
    }

    $trimmed = trim($line);
    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
        continue;
    }

    $statement .= $line;

    if (str_ends_with(trim($line), ';')) {
        $sql = trim($statement);
        $statement = '';

        if ($sql === '') {
            continue;
        }

        if (!$mysqli->query($sql)) {
            echo "ERROR: Gagal menjalankan statement di baris {$lineNumber}: ({$mysqli->errno}) {$mysqli->error}\n";
            echo "STATEMENT: " . substr($sql, 0, 300) . (strlen($sql) > 300 ? '...' : '') . "\n";
            $hasError = true;
            break;
        }
    }
}

fclose($handle);

if (!$hasError && trim($statement) !== '') {
    if (!$mysqli->query(trim($statement))) {
        echo "ERROR: Gagal menjalankan statement terakhir: ({$mysqli->errno}) {$mysqli->error}\n";
        $hasError = true;
    }
}

if ($hasError) {
    echo "ERROR: Restore berhenti karena kesalahan. Periksa pesan di atas.\n";
    exit(1);
}

echo "Restore berhasil. Semua tabel dan data dari backup telah dikembalikan ke database {$database}.\n";

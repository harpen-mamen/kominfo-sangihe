<?php

// Script untuk membandingkan tabel di database dengan tabel yang direferensikan dalam Models

$models_dir = __DIR__ . '/app/Models';

// Tabel yang direferensikan dalam Models (dari analisis manual)
$expected_tables = [
    'pengguna',
    'ringkasan_statistik',
    'periode_data',
    'pengajuan_data',
    'opd',
    'nilai_data_mentah',
    'lapisan_peta',
    'kecamatan',
    'desa',
    'indikator_data',
    'fitur_peta',
    'konten',
    'aktivitas_sistem',
    'admin_kecamatan_desa',
];

// Query database untuk melihat tabel yang sebenarnya ada
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=kominfo_sangihe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = "kominfo_sangihe" ORDER BY TABLE_NAME');
    $actual_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $actual_tables_set = array_flip($actual_tables);
    
    // Cek tabel mana yang tidak ada di database
    $missing_tables = [];
    foreach ($expected_tables as $table) {
        if (!isset($actual_tables_set[$table])) {
            $missing_tables[] = $table;
        }
    }
    
    // Output hasil
    $output = "=== HASIL PERBANDINGAN TABEL ===" . PHP_EOL . PHP_EOL;
    $output .= "Total tabel yang ada di database: " . count($actual_tables) . PHP_EOL;
    $output .= "Total tabel yang diharapkan: " . count($expected_tables) . PHP_EOL . PHP_EOL;
    
    if (!empty($missing_tables)) {
        $output .= "⚠️  TABEL YANG TIDAK ADA DI DATABASE (Ada di code tapi tidak di DB):" . PHP_EOL;
        foreach ($missing_tables as $table) {
            $output .= "  - " . $table . PHP_EOL;
        }
        $output .= PHP_EOL;
    } else {
        $output .= "✅ Semua tabel sudah ada di database" . PHP_EOL . PHP_EOL;
    }
    
    $output .= "=== DAFTAR TABEL DI DATABASE ===" . PHP_EOL;
    foreach ($actual_tables as $table) {
        $output .= "  - " . $table . PHP_EOL;
    }
    
    file_put_contents(__DIR__ . '/table_comparison_result.txt', $output);
    echo $output;
    
} catch (Exception $e) {
    $error = "ERROR: " . $e->getMessage();
    file_put_contents(__DIR__ . '/table_comparison_result.txt', $error);
    echo $error;
}

<?php
// Konfigurasi Database PostgreSQL
$host = 'localhost';
$port = '5432';
$dbname = 'webgis_sukabumi';
$user = 'postgres';
$password = '002213'; // Sesuaikan dengan password database Anda

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    // make a database connection
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Jangan die() di sini agar bisa ditangani oleh script pemanggil (misal: return JSON)
    $dbError = "Koneksi ke database gagal: " . $e->getMessage();
}
?>

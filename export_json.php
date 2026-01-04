<?php
// Skrip untuk mengekspor data GeoJSON dari database ke file statis
// agar bisa di-hosting di GitHub Pages (yang tidak mendukung PHP/Database)

// include 'get_layers.php'; // Hapus ini karena bikin error header


// Simulasikan request untuk layer risk_zones
$_GET['layer'] = 'risk_zones';

// Tangkap output dari get_layers.php
ob_start();
// Include ulang atau copy logika get_layers.php (karena include 'get_layers.php' diatas mungkin sudah mengeksekusi logika jika tidak di-guard)
// Tapi get_layers.php langsung echo output.
// Mari kita panggil URL lokal atau gunakan output buffer jika file tersebut langsung mengeksekusi.
// Karena get_layers.php di-include di atas, dan dia mengeksekusi kode berdasarkan $_GET['layer'],
// namun $_GET['layer'] baru diset SETELAH include di baris 7.
// Jadi include pertama mungkin gagal/exit karena parameter kosong.

// Reset
ob_clean();

// Set parameter
$_GET['layer'] = 'risk_zones';

// Jalankan logika query manual agar aman
include 'config.php';

$tableName = 'fmd_risk_map2';
$geomCol = 'geom';
$geomColQuoted = '"' . $geomCol . '"';

// Kolom yang dipilih (hardcoded sesuai get_layers.php terakhir)
$properties = '"id" AS id, NULL AS ND, "tingkat_risiko" AS tingkat_risiko';

$sql = "SELECT row_to_json(fc)
        FROM (
            SELECT 'FeatureCollection' As type, array_to_json(array_agg(f)) As features
            FROM (
                SELECT 'Feature' As type,
                    ST_AsGeoJSON(ST_Transform($geomColQuoted, 4326))::json As geometry,
                    row_to_json((SELECT l FROM (SELECT $properties) As l)) As properties
                FROM $tableName
            ) As f
        ) As fc";

$stmt = $pdo->query($sql);
$geojson = $stmt->fetch(PDO::FETCH_ASSOC);

$jsonData = '';
if ($geojson && $geojson['row_to_json']) {
    $jsonData = $geojson['row_to_json'];
} else {
    $jsonData = json_encode(['type' => 'FeatureCollection', 'features' => []]);
}

// Tulis ke file
$file = 'data/risk_zones.json';
if (file_put_contents($file, $jsonData)) {
    echo "Berhasil mengekspor data ke $file\n";
    echo "Ukuran file: " . round(filesize($file) / 1024, 2) . " KB";
} else {
    echo "Gagal menulis file.";
}
?>
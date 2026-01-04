<?php
include 'config.php';

header('Content-Type: application/json');

// Cek jika ada error koneksi dari config.php
if (isset($dbError)) {
    echo json_encode(['error' => $dbError]);
    exit;
}

$layer = isset($_GET['layer']) ? $_GET['layer'] : '';

if (!$layer) {
    echo json_encode(['error' => 'Parameter layer tidak ditemukan']);
    exit;
}

$tableName = '';
$properties = '';

// Validasi dan mapping nama tabel untuk keamanan
switch ($layer) {
    case 'risk_zones':
        $tableName = 'fmd_risk_map2';
        // Deteksi kolom agar aman terhadap perbedaan penamaan (case-sensitive)
        try {
            // Deteksi kolom ND (bisa 'ND' atau 'nd')
            $colStmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema='public' AND table_name=:table");
            $colStmt->execute([':table' => $tableName]);
            $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            $ndColRaw = null;
            foreach ($cols as $c) {
                if (strcasecmp($c, 'ND') === 0) { $ndColRaw = $c; break; }
            }
            $ndColumnQuoted = $ndColRaw ? '"' . $ndColRaw . '"' : null;
            
            // Deteksi kolom ID (fid atau id) lalu alias ke id
            $idColRaw = null;
            foreach ($cols as $c) {
                if (strcasecmp($c, 'fid') === 0 || strcasecmp($c, 'id') === 0) { $idColRaw = $c; break; }
            }
            $idColumnQuoted = $idColRaw ? '"' . $idColRaw . '"' : null;
            
            // Deteksi kolom tingkat risiko (fallback ke risk_level bila perlu)
            $riskColRaw = null;
            $riskCandidates = ['tingkat_risiko', 'tingkat-risiko', 'risk_level'];
            foreach ($riskCandidates as $target) {
                foreach ($cols as $c) {
                    if (strcasecmp($c, $target) === 0) { $riskColRaw = $c; break 2; }
                }
            }
            $riskColumnQuoted = $riskColRaw ? '"' . $riskColRaw . '"' : null;
            
            // Deteksi kolom geometry - User menyatakan kolomnya adalah 'geom'
            $geomCol = 'geom';
            $geomColQuoted = '"' . $geomCol . '"';
            
            // Bangun daftar properti dengan alias konsisten
            $ndSelect = $ndColumnQuoted ? ($ndColumnQuoted . ' AS ND') : 'NULL AS ND';
            $riskSelect = $riskColumnQuoted ? ($riskColumnQuoted . ' AS tingkat_risiko') : 'NULL AS tingkat_risiko';
            $idSelect = $idColumnQuoted ? ($idColumnQuoted . ' AS id') : 'NULL AS id';
            $properties = "$idSelect, $ndSelect, $riskSelect";
            
            // Simpan nama kolom geom untuk digunakan di query utama
            $GLOBALS['__geomColQuoted'] = $geomColQuoted;
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Gagal mendeteksi struktur tabel: ' . $e->getMessage()]);
            exit;
        }
        break;
    default:
        echo json_encode(['error' => 'Layer tidak valid']);
        exit;
}

try {
    // Query untuk mengambil data sebagai GeoJSON
    $geomColQuoted = isset($GLOBALS['__geomColQuoted']) ? $GLOBALS['__geomColQuoted'] : '"geom"';
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

    if ($geojson && $geojson['row_to_json']) {
        echo $geojson['row_to_json'];
    } else {
        echo json_encode(['type' => 'FeatureCollection', 'features' => []]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal mengambil data: ' . $e->getMessage()]);
}
?>

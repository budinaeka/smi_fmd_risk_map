<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGIS Risiko PMK Kabupaten Sukabumi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        #wrapper {
            position: relative;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }
        #map {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        #sidebar {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 300px;
            max-height: calc(100vh - 30px);
            background-color: rgba(255, 255, 255, 0.8); /* Transparan 20% (Opacity 80%) */
            backdrop-filter: blur(8px); /* Efek blur di belakang */
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 15px; /* Sudut membulat */
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar-header {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            background-color: transparent;
            flex-shrink: 0;
        }
        .logos {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        .logo-img {
            height: 40px; /* Lebih kecil */
            width: auto;
        }
        h5 { font-size: 1rem; margin-bottom: 2px; font-weight: 700; }
        h6 { font-size: 0.8rem; margin-bottom: 0; }

        .sidebar-content {
            padding: 10px;
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .card {
            margin-bottom: 10px;
            border: none;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .card-header {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
        }
        .card-body {
            padding: 8px 12px;
        }
        .form-check {
            min-height: 1.2rem;
            margin-bottom: 0.2rem;
        }
        .form-check-label {
            font-size: 0.8rem;
        }
        .layer-item {
            margin-bottom: 4px;
        }
        .footer {
            padding: 8px;
            text-align: center;
            font-size: 0.7rem;
            color: #555;
            border-top: 1px solid rgba(0,0,0,0.1);
            background-color: transparent;
            flex-shrink: 0;
        }
        /* Custom scrollbar hidden */
        .sidebar-content::-webkit-scrollbar { display: none; }
        .legend-color {
            display: inline-block;
            width: 15px;
            height: 15px;
            margin-right: 5px;
            border: 1px solid #ccc;
        }
        
        /* Collapse Icon Rotation */
        .card-header button[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
        .bi-chevron-down {
            transition: transform 0.3s ease;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .info.legend, .info.reference {
                margin-bottom: 50px !important; /* Geser ke atas */
                max-width: 250px !important;
                font-size: 0.7rem !important;
            }
            .info.reference {
                margin-right: 10px !important;
            }
            .info.legend {
                margin-right: 10px !important; /* Karena pindah ke kanan */
                margin-left: auto !important;
            }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <!-- Peta Full Screen -->
    <div id="map"></div>

    <!-- Tombol Toggle Sidebar (Mobile Only) -->
    <button id="sidebar-toggle" title="Buka Menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Sebelah Kanan -->
    <div id="sidebar">
        <div class="sidebar-header position-relative">
            <div class="logos">
                <!-- Logo Pemda Sukabumi -->
                <img src="assets/img/Lambang_Kab_Sukabumi.png" onerror="this.src='https://via.placeholder.com/60?text=PEMDA'" alt="Logo Sukabumi" class="logo-img">
                <!-- Logo UGM -->
                <img src="assets/img/Lambang UGM.png" onerror="this.src='https://via.placeholder.com/60?text=UGM'" alt="Logo UGM" class="logo-img">
            </div>
            <h5 class="fw-bold">PETA RISIKO PENYAKIT MULUT DAN KUKU (PMK)</h5>
            <h6 class="text-muted">Kabupaten Sukabumi</h6>
        </div>

        <div class="sidebar-content">
            <!-- Pilihan Basemap Removed (Moved to Map Control) -->

            <!-- Pilihan Layer -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white p-0" id="headingLayer">
                    <button class="btn btn-link text-white text-decoration-none w-100 text-start p-2 d-flex justify-content-between align-items-center shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLayer" aria-expanded="true" aria-controls="collapseLayer">
                        <span><i class="bi bi-layers"></i> Layer Data</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div id="collapseLayer" class="collapse show" aria-labelledby="headingLayer">
                    <div class="card-body">
                    <div class="form-check layer-item">
                        <input class="form-check-input layer-toggle" type="checkbox" value="risk_zones" id="layer-risk" checked>
                        <label class="form-check-label" for="layer-risk">
                            Zona Risiko PMK
                        </label>
                    </div>
                </div>
                </div>
            </div>
            
            <div class="alert alert-info small mb-0" style="font-size: 0.8rem;">
                <strong>Referensi:</strong> Prasetia, B. E., Primatika, R. A., & Nugroho, W. S. (2025). Spatial risk assessment of foot and mouth disease in Sukabumi regency, Indonesia: A GIS and multicriteria decision analysis approach. Research in Veterinary Science, 105694. <a href="https://doi.org/10.1016/j.rvsc.2025.105694" target="_blank">https://doi.org/10.1016/j.rvsc.2025.105694</a>
            </div>
        </div>
        
        <div class="footer">
            &copy; 2026 WebGIS PMK Sukabumi
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Inisialisasi Peta (Koordinat Sukabumi)
    var map = L.map('map').setView([-6.9216, 106.9249], 10);

    // --- Custom Controls (Legenda & Referensi) ---
    
    // 1. Legend Control (Zona Risiko)
    // Mobile: Pindah ke bottomright agar stack dengan referensi (tidak overlap)
    var legendPos = (window.innerWidth <= 768) ? 'bottomright' : 'bottomleft';
    var legendControl = L.control({position: legendPos});

    legendControl.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend');
        div.style.backgroundColor = "white";
        div.style.padding = "10px";
        div.style.borderRadius = "5px";
        div.style.boxShadow = "0 0 15px rgba(0,0,0,0.2)";
        div.style.marginBottom = "20px"; 
        // Margin Left/Right diatur via CSS Media Query untuk mobile
        if (window.innerWidth > 768) {
            div.style.marginLeft = "10px";
        }
        
        div.innerHTML = `
            <h6 class="mb-2" style="font-size: 0.8rem; font-weight: bold; margin-top:0;">Zona Risiko PMK</h6>
            <div style="font-size: 0.75rem; line-height: 1.5;">
                <div><span style="background: #d32f2f; width: 15px; height: 15px; display: inline-block; margin-right: 5px; vertical-align: middle; border:1px solid #ccc;"></span> Sangat Tinggi</div>
                <div><span style="background: #f57c00; width: 15px; height: 15px; display: inline-block; margin-right: 5px; vertical-align: middle; border:1px solid #ccc;"></span> Tinggi</div>
                <div><span style="background: #fbc02d; width: 15px; height: 15px; display: inline-block; margin-right: 5px; vertical-align: middle; border:1px solid #ccc;"></span> Sedang</div>
                <div><span style="background: #388e3c; width: 15px; height: 15px; display: inline-block; margin-right: 5px; vertical-align: middle; border:1px solid #ccc;"></span> Rendah</div>
                <div><span style="background: #1b5e20; width: 15px; height: 15px; display: inline-block; margin-right: 5px; vertical-align: middle; border:1px solid #ccc;"></span> Sangat Rendah</div>
            </div>
        `;
        return div;
    };

    // 2. Reference Control
    var referenceControl = L.control({position: 'bottomright'});

    referenceControl.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info reference');
        div.style.backgroundColor = "rgba(255, 255, 255, 0.9)";
        div.style.padding = "5px 8px";
        div.style.borderRadius = "5px";
        div.style.boxShadow = "0 0 5px rgba(0,0,0,0.2)";
        div.style.marginBottom = "5px";
        div.style.marginRight = "5px";
        div.style.maxWidth = "200px";
        div.style.fontSize = "0.6rem";
        div.style.lineHeight = "1.2";
        
        div.innerHTML = `
            <strong>Ref:</strong> Prasetia, B. E., et al. (2025). <em>Spatial risk assessment of FMD...</em> <a href="https://doi.org/10.1016/j.rvsc.2025.105694" target="_blank" style="text-decoration:none;">[Link]</a>
        `;
        return div;
    };
    referenceControl.addTo(map);

    // Mobile Sidebar Logic
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
    }

    if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);

    // Tutup sidebar saat klik peta (khusus mobile)
    map.on('click', function() {
        if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });


    // Definisi Basemap
    var basemaps = {
        'osm': L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
            errorTileUrl: 'https://via.placeholder.com/256?text=Map+Error', // Placeholder jika gagal load
            crossOrigin: true,
            updateWhenIdle: true,
            keepBuffer: 2
        }),
        'carto': L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }),
        'satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19,
            errorTileUrl: 'https://via.placeholder.com/256?text=Map+Error',
            crossOrigin: true,
            updateWhenIdle: true,
            keepBuffer: 2
        }),
        'topo': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)',
            maxZoom: 17,
            errorTileUrl: 'https://via.placeholder.com/256?text=Map+Error',
            crossOrigin: true,
            updateWhenIdle: true,
            keepBuffer: 2
        })
    };

    // Fallback otomatis jika tileerror berulang
    var errorCounters = {};
    function setBase(name) {
        if (!basemaps[name]) {
            console.error('Basemap not found: ' + name);
            return;
        }
        for (var key in basemaps) {
            if (map.hasLayer(basemaps[key])) {
                map.removeLayer(basemaps[key]);
            }
        }
        basemaps[name].addTo(map);
        basemaps[name].bringToBack(); // Pastikan basemap selalu di belakang
    }
    Object.keys(basemaps).forEach(function(name) {
        errorCounters[name] = 0;
        basemaps[name].on('tileerror', function() {
            errorCounters[name]++;
            if (errorCounters[name] >= 5 && name === 'osm') {
                alert('Basemap OSM mengalami kendala jaringan. Beralih ke Esri Satellite.');
                setBase('satellite');
            }
        });
        basemaps[name].on('tileload', function() {
            if (errorCounters[name] > 0) errorCounters[name]--;
        });
    });

    // Tambahkan basemap default
    basemaps['osm'].addTo(map);

    // --- Control Layers (Basemap) ---
    // Diposisikan di topleft (akan muncul di bawah zoom control secara default)
    L.control.layers(basemaps, null, {position: 'topleft'}).addTo(map);

    // Object untuk menyimpan layer yang aktif
    var activeLayers = {};

    // Fungsi Styling untuk Zona Risiko
    function styleRisk(feature) {
        var lvl = (feature.properties.tingkat_risiko || '').toString().toLowerCase().trim();
        var color = '#cccccc';
        if (lvl === 'sangat tinggi') color = '#d32f2f';       // Merah Gelap (Vivid)
        else if (lvl === 'tinggi') color = '#f57c00';          // Orange Tua
        else if (lvl === 'sedang') color = '#fbc02d';          // Kuning Emas (Lebih Gelap)
        else if (lvl === 'rendah') color = '#388e3c';          // Hijau Medium
        else if (lvl === 'sangat rendah') color = '#1b5e20';   // Hijau Sangat Tua

        return {
            fillColor: color,
            weight: 1.5,           // Garis sedikit lebih tipis tapi tegas
            opacity: 1,
            color: '#333333',      // Garis tepi abu tua (kontras di peta terang)
            dashArray: '0',        // Garis solid (bukan putus-putus)
            fillOpacity: 0.85      // Opasitas tinggi agar warna 'pop'
        };
    }

    // Fungsi untuk memuat layer dari database
    function loadLayer(layerName) {
        if (activeLayers[layerName]) return; // Sudah dimuat

        fetch('get_layers.php?layer=' + layerName)
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON:", text);
                    alert("Terjadi kesalahan server. Cek console untuk detail.");
                    throw new Error("Respon server bukan JSON yang valid.");
                }
            })
            .then(data => {
                if (data.error) {
                    console.error("Database Error:", data.error);
                    alert("Gagal memuat data: " + data.error);
                    return;
                }

                var layer;
                
                if (layerName === 'risk_zones') {
                    layer = L.geoJSON(data, {
                        style: styleRisk,
                        onEachFeature: function(feature, layer) {
                            // Popup awal (static info)
                            var content = '<div class="p-2">' +
                                '<h6 class="fw-bold mb-1">Info Zona Risiko</h6>' +
                                '<table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">' +
                                    '<tr><td><strong>ID:</strong></td><td>' + feature.properties.id + '</td></tr>' +
                                    '<tr><td><strong>Risiko:</strong></td><td>' + feature.properties.tingkat_risiko + '</td></tr>' +
                                    '<tr><td><strong>Lokasi:</strong></td><td id="loc-' + feature.properties.id + '"><em>Klik untuk memuat...</em></td></tr>' +
                                '</table>' +
                                '</div>';
                            
                            layer.bindPopup(content);

                            // Event saat popup dibuka: Fetch nama kecamatan
                            layer.on('popupopen', function(e) {
                                var lat = e.popup.getLatLng().lat;
                                var lng = e.popup.getLatLng().lng;
                                var id = feature.properties.id;
                                var locEl = document.getElementById('loc-' + id);
                                
                                if (locEl && locEl.innerText.includes('Klik') || locEl.innerText.includes('Loading')) {
                                    locEl.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
                                    
                                    // Reverse Geocoding Nominatim
                                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=12`)
                                        .then(res => res.json())
                                        .then(data => {
                                            var district = data.address.city_district || data.address.county || data.address.state_district || 'Tidak diketahui';
                                            var village = data.address.village || data.address.town || '';
                                            var fullLoc = district;
                                            if (village) fullLoc += ', ' + village;
                                            
                                            locEl.innerHTML = fullLoc;
                                        })
                                        .catch(err => {
                                            locEl.innerHTML = 'Gagal memuat';
                                            console.error(err);
                                        });
                                }
                            });
                        }
                    });
                }

                if (layer) {
                    layer.addTo(map);
                    activeLayers[layerName] = layer;
                    
                    // Zoom ke layer jika data ada
                    if (data.features && data.features.length > 0) {
                        // Responsive Padding for FitBounds
                        var padding = (window.innerWidth <= 768) ? [20, 20] : [50, 50];
                        
                        map.fitBounds(layer.getBounds(), {
                            padding: padding
                        });
                    }
                }
            })
            .catch(error => console.error('Error loading layer:', error));
    }

    // Fungsi untuk menghapus layer
    function removeLayer(layerName) {
        if (activeLayers[layerName]) {
            map.removeLayer(activeLayers[layerName]);
            delete activeLayers[layerName];

            // Hapus Legenda jika layer risk_zones
            if (layerName === 'risk_zones') {
                legendControl.remove();
            }
        }
    }

    // Event Listener untuk Checkbox Layer
    document.querySelectorAll('.layer-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                loadLayer(this.value);
            } else {
                removeLayer(this.value);
            }
        });
        
        // Muat layer jika checkbox dicentang secara default (saat load)
        if (checkbox.checked) {
            loadLayer(checkbox.value);
        }
    });

    // Auto-collapse sections on mobile
    document.addEventListener("DOMContentLoaded", function() {
        if (window.innerWidth <= 768) {
            var collapseElements = document.querySelectorAll('.collapse.show');
            collapseElements.forEach(function(el) {
                // Remove 'show' class directly to hide without animation issues on load
                el.classList.remove('show');
                // Update button state
                var id = el.getAttribute('id');
                var btn = document.querySelector(`button[data-bs-target="#${id}"]`);
                if(btn) {
                    btn.classList.add('collapsed');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });

</script>

</body>
</html>

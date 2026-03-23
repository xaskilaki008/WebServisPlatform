<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг пляжей Севастополя</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            position: relative;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        #info-panel {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 1000;
            width: 280px;
            max-width: calc(100% - 32px);
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }

        #info-panel h2 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        #info-panel p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    {{-- Место для перенесенного исходного HTML-кода карты --}}
    <div id="info-panel">
        <h2>Информация о пляже</h2>
        <p><strong>Название:</strong> <span id="info-name">Выберите пляж на карте</span></p>
        <p><strong>Номер:</strong> <span id="info-num">-</span></p>
        <p><strong>Площадь, га:</strong> <span id="info-area">-</span></p>
        <p><strong>Береговая линия, м:</strong> <span id="info-shoreline">-</span></p>
        <p><strong>Зона купания, м:</strong> <span id="info-swimzone">-</span></p>
    </div>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([44.61, 33.52], 11);
        const infoName = document.getElementById('info-name');
        const infoNum = document.getElementById('info-num');
        const infoArea = document.getElementById('info-area');
        const infoShoreline = document.getElementById('info-shoreline');
        const infoSwimzone = document.getElementById('info-swimzone');

        function updateInfoPanel(properties = {}) {
            infoName.textContent = properties.name || 'Без названия';
            infoNum.textContent = properties.num ?? '-';
            infoArea.textContent = properties.area_ha ?? '-';
            infoShoreline.textContent = properties.shoreline_m ?? '-';
            infoSwimzone.textContent = properties.swimzone_m ?? '-';
        }

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        fetch('/sevastopol_beaches.geojson')
            .then(response => response.json())
            .then(data => {
                const beachesLayer = L.geoJSON(data, {
                    onEachFeature: function (feature, layer) {
                        const p = feature.properties;
                        layer.bindPopup(
                            '<b>' + (p.name || 'Без названия') + '</b><br>' +
                            'Номер: ' + (p.num ?? '-') + '<br>' +
                            'Площадь, га: ' + (p.area_ha ?? '-') + '<br>' +
                            'Береговая линия, м: ' + (p.shoreline_m ?? '-') + '<br>' +
                            'Зона купания, м: ' + (p.swimzone_m ?? '-')
                        );
                        layer.on('click', function () {
                            updateInfoPanel(p);
                        });
                    }
                }).addTo(map);

                map.fitBounds(beachesLayer.getBounds());
            })
            .catch(error => {
                console.error('Ошибка загрузки GeoJSON:', error);
                alert('Не удалось загрузить файл sevastopol_beaches.geojson');
            });
    </script>
</body>
</html>

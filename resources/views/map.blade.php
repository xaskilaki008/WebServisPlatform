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

        #map {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    {{-- Место для перенесенного исходного HTML-кода карты --}}
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([44.61, 33.52], 11);

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

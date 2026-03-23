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
        <p><strong>Номер:</strong> <span id="info-number">-</span></p>
        <p><strong>Уровень волнения:</strong> <span id="info-wave-level">-</span></p>
    </div>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([44.61, 33.52], 11);
        const infoName = document.getElementById('info-name');
        const infoNumber = document.getElementById('info-number');
        const infoWaveLevel = document.getElementById('info-wave-level');

        function updateInfoPanel(beach = {}) {
            infoName.textContent = beach.name || 'Без названия';
            infoNumber.textContent = beach.number ?? '-';
            infoWaveLevel.textContent = beach.wave_level ?? '-';
        }

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        fetch('/api/beaches')
            .then(response => response.json())
            .then(data => {
                const markers = [];

                data.forEach(beach => {
                    const marker = L.marker([beach.latitude, beach.longitude])
                        .addTo(map)
                        .bindPopup(
                            '<b>' + (beach.name || 'Без названия') + '</b><br>' +
                            'Номер: ' + (beach.number ?? '-') + '<br>' +
                            'Уровень волнения: ' + (beach.wave_level ?? '-')
                        );

                    marker.on('click', function () {
                        updateInfoPanel(beach);
                    });

                    markers.push(marker);
                });

                if (markers.length > 0) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds());
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки пляжей:', error);
                alert('Не удалось загрузить данные пляжей');
            });
    </script>
</body>
</html>

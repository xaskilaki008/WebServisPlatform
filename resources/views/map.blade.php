<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг пляжей Севастополя</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        :root {
            color-scheme: light;
            --bg: #eef3f7;
            --surface: #ffffff;
            --surface-muted: #f6f8fb;
            --border: #d8e1ea;
            --text: #173042;
            --text-soft: #5f7283;
            --accent: #1d6fa5;
            --accent-soft: #d9edf9;
            --shadow: 0 12px 24px rgba(23, 48, 66, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
        }

        .app-shell {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fbfd 0%, #eef3f7 100%);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            padding: 16px;
            background: rgba(248, 251, 253, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border);
        }

        .topbar-title {
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 700;
        }

        .topbar-nav {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .nav-button,
        .action-button,
        .back-button,
        .filter-chip {
            border: 0;
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
            cursor: pointer;
        }

        .nav-button {
            background: var(--surface);
            color: var(--text);
            box-shadow: inset 0 0 0 1px var(--border);
            font-weight: 700;
        }

        .nav-button.active,
        .filter-chip.active,
        .action-button.primary,
        .back-button {
            background: var(--accent);
            color: #ffffff;
            box-shadow: none;
        }

        .screen {
            display: none;
            padding: 16px;
        }

        .screen.active {
            display: block;
        }

        .panel,
        .list-card,
        .detail-card,
        .filter-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
        }

        .panel,
        .filter-panel,
        .detail-card,
        .list-card {
            padding: 16px;
        }

        .panel {
            margin-bottom: 14px;
        }

        .panel h2,
        .detail-card h2,
        .screen-title,
        .filter-title {
            margin: 0 0 12px;
            font-size: 18px;
        }

        .panel p,
        .detail-field,
        .list-meta,
        .screen-subtitle,
        .filter-description,
        .info-note {
            margin: 6px 0;
            color: var(--text-soft);
            line-height: 1.45;
        }

        .panel strong,
        .detail-field strong,
        .list-meta strong,
        .info-note strong {
            color: var(--text);
        }

        .map-card {
            overflow: hidden;
            border-radius: 0;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: var(--surface);
        }

        #map {
            width: 100%;
            height: 52vh;
            min-height: 360px;
        }

        .screen-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .list-wrap {
            display: grid;
            gap: 12px;
        }

        .list-card h3 {
            margin: 0 0 8px;
            font-size: 17px;
        }

        .list-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .action-button,
        .filter-chip {
            background: var(--surface-muted);
            color: var(--text);
            box-shadow: inset 0 0 0 1px var(--border);
            font-weight: 700;
        }

        .filter-panel {
            margin-bottom: 14px;
        }

        .search-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #ffffff;
            color: var(--text);
            font: inherit;
            margin-bottom: 12px;
        }

        .filter-chips {
            display: grid;
            gap: 8px;
            grid-template-columns: 1fr;
        }

        .detail-fields {
            display: grid;
            gap: 10px;
        }

        .back-button {
            padding-inline: 16px;
            white-space: nowrap;
        }

        .category-badge {
            display: inline-flex;
            margin-top: 10px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
        }

        .empty-state {
            padding: 18px;
            border-radius: 18px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-soft);
            box-shadow: var(--shadow);
        }

        @media (min-width: 768px) {
            .app-shell {
                max-width: 540px;
            }

            #map {
                height: 58vh;
            }

            .filter-chips {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <h1 class="topbar-title">Мониторинг пляжей Севастополя</h1>
            <div class="topbar-nav">
                <button type="button" class="nav-button active" data-screen-target="map-screen">Карта</button>
                <button type="button" class="nav-button" data-screen-target="list-screen">Доступные пляжи</button>
            </div>
        </header>

        <main>
            <section id="map-screen" class="screen active">
                <div id="info-panel" class="panel">
                    <h2>Информация о пляже</h2>
                    <p><strong>Название:</strong> <span id="info-name">Выберите пляж на карте</span></p>
                    <p><strong>Номер:</strong> <span id="info-number">-</span></p>
                    <p><strong>Уровень волнения:</strong> <span id="info-wave-level">-</span></p>
                    <p><strong>Описание:</strong> <span id="info-wave-text">Нет данных</span></p>
                    <p><strong>Категория:</strong> <span id="info-category">-</span></p>
                    <p class="info-note">Категории для учебного прототипа основаны на уровне волнения и показывают пригодность пляжа для купания.</p>
                </div>
                <div class="map-card">
                    <div id="map"></div>
                </div>
            </section>

            <section id="list-screen" class="screen">
                <div class="screen-header">
                    <div>
                        <h2 class="screen-title">Список пляжей</h2>
                        <p class="screen-subtitle">Здесь можно искать пляжи по названию и фильтровать их по учебным категориям безопасности.</p>
                    </div>
                </div>

                <div class="filter-panel">
                    <h3 class="filter-title">Поиск и категории</h3>
                    <p class="filter-description">Поиск работает по названию, а категории рассчитываются по значению wave_level.</p>
                    <input id="search-input" class="search-input" type="text" placeholder="Введите часть названия пляжа">
                    <div class="filter-chips">
                        <button type="button" class="filter-chip active" data-category="all">Все пляжи</button>
                        <button type="button" class="filter-chip" data-category="safe">Купание допустимо</button>
                        <button type="button" class="filter-chip" data-category="caution">Нужна осторожность</button>
                        <button type="button" class="filter-chip" data-category="danger">Купание не рекомендуется</button>
                    </div>
                </div>

                <div id="beaches-list" class="list-wrap"></div>
            </section>

            <section id="detail-screen" class="screen">
                <div class="screen-header">
                    <div>
                        <h2 class="screen-title">Подробная информация</h2>
                        <p class="screen-subtitle">Детали выбранного пляжа.</p>
                    </div>
                    <button type="button" id="detail-back-button" class="back-button">Назад</button>
                </div>
                <div class="detail-card">
                    <h2 id="detail-name">Пляж не выбран</h2>
                    <div class="detail-fields">
                        <div class="detail-field"><strong>Номер:</strong> <span id="detail-number">-</span></div>
                        <div class="detail-field"><strong>Уровень волнения:</strong> <span id="detail-wave-level">-</span></div>
                        <div class="detail-field"><strong>Описание волнения:</strong> <span id="detail-wave-text">Нет данных</span></div>
                        <div class="detail-field"><strong>Категория:</strong> <span id="detail-category">-</span></div>
                        <div class="detail-field"><strong>Широта:</strong> <span id="detail-latitude">-</span></div>
                        <div class="detail-field"><strong>Долгота:</strong> <span id="detail-longitude">-</span></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([44.61, 33.52], 11);
        const infoName = document.getElementById('info-name');
        const infoNumber = document.getElementById('info-number');
        const infoWaveLevel = document.getElementById('info-wave-level');
        const infoWaveText = document.getElementById('info-wave-text');
        const infoCategory = document.getElementById('info-category');
        const beachesList = document.getElementById('beaches-list');
        const detailName = document.getElementById('detail-name');
        const detailNumber = document.getElementById('detail-number');
        const detailWaveLevel = document.getElementById('detail-wave-level');
        const detailWaveText = document.getElementById('detail-wave-text');
        const detailCategory = document.getElementById('detail-category');
        const detailLatitude = document.getElementById('detail-latitude');
        const detailLongitude = document.getElementById('detail-longitude');
        const detailBackButton = document.getElementById('detail-back-button');
        const navButtons = document.querySelectorAll('[data-screen-target]');
        const screens = document.querySelectorAll('.screen');
        const searchInput = document.getElementById('search-input');
        const filterChips = document.querySelectorAll('[data-category]');

        const beaches = [];
        const markersById = new Map();
        let markersGroup = null;
        let selectedBeach = null;
        let lastNonDetailScreen = 'map-screen';
        let activeCategory = 'all';
        let searchQuery = '';

        function getWaveLevelText(level) {
            const numericLevel = Number(level);

            if (Number.isNaN(numericLevel)) {
                return 'Нет данных';
            }

            if (numericLevel === 0) {
                return 'Слабое волнение';
            }

            if (numericLevel <= 2) {
                return 'Небольшое волнение';
            }

            if (numericLevel <= 4) {
                return 'Умеренное волнение';
            }

            if (numericLevel <= 6) {
                return 'Заметное волнение';
            }

            if (numericLevel <= 9) {
                return 'Сильное волнение';
            }

            return 'Очень сильное волнение';
        }

        function getBeachCategoryKey(beach) {
            if (beach.category_key) {
                return beach.category_key;
            }

            const level = Number(beach.wave_level);

            if (Number.isNaN(level)) {
                return 'danger';
            }

            if (level <= 2) {
                return 'safe';
            }

            if (level <= 5) {
                return 'caution';
            }

            return 'danger';
        }

        function getBeachCategoryLabel(beach) {
            if (beach.category_label) {
                return beach.category_label;
            }

            const categoryKey = getBeachCategoryKey(beach);

            if (categoryKey === 'safe') {
                return 'Купание допустимо';
            }

            if (categoryKey === 'caution') {
                return 'Нужна осторожность';
            }

            return 'Купание не рекомендуется';
        }

        function updateInfoPanel(beach = {}) {
            infoName.textContent = beach.name || 'Без названия';
            infoNumber.textContent = beach.number ?? '-';
            infoWaveLevel.textContent = beach.wave_level ?? '-';
            infoWaveText.textContent = getWaveLevelText(beach.wave_level);
            infoCategory.textContent = getBeachCategoryLabel(beach);
        }

        function updateDetailScreen(beach = {}) {
            detailName.textContent = beach.name || 'Без названия';
            detailNumber.textContent = beach.number ?? '-';
            detailWaveLevel.textContent = beach.wave_level ?? '-';
            detailWaveText.textContent = getWaveLevelText(beach.wave_level);
            detailCategory.textContent = getBeachCategoryLabel(beach);
            detailLatitude.textContent = beach.latitude ?? '-';
            detailLongitude.textContent = beach.longitude ?? '-';
        }

        function setActiveScreen(screenId) {
            screens.forEach(screen => {
                screen.classList.toggle('active', screen.id === screenId);
            });

            navButtons.forEach(button => {
                button.classList.toggle('active', button.dataset.screenTarget === screenId);
            });

            if (screenId !== 'detail-screen') {
                lastNonDetailScreen = screenId;
            }

            if (screenId === 'map-screen') {
                setTimeout(() => {
                    map.invalidateSize();
                }, 0);
            }
        }

        function buildPopupContent(beach) {
            return '<b>' + (beach.name || 'Без названия') + '</b><br>' +
                'Номер: ' + (beach.number ?? '-') + '<br>' +
                'Уровень волнения: ' + (beach.wave_level ?? '-') + '<br>' +
                'Описание: ' + getWaveLevelText(beach.wave_level) + '<br>' +
                'Категория: ' + getBeachCategoryLabel(beach);
        }

        function getFilteredBeaches() {
            return beaches.filter(beach => {
                const matchesName = beach.name.toLowerCase().includes(searchQuery);
                const matchesCategory = activeCategory === 'all' || getBeachCategoryKey(beach) === activeCategory;
                return matchesName && matchesCategory;
            });
        }

        function refreshMarkerVisibility() {
            const visibleIds = new Set(getFilteredBeaches().map(beach => beach.id));

            markersById.forEach((marker, beachId) => {
                const isVisible = visibleIds.has(beachId);

                if (isVisible && !map.hasLayer(marker)) {
                    marker.addTo(map);
                }

                if (!isVisible && map.hasLayer(marker)) {
                    map.removeLayer(marker);
                }
            });
        }

        function renderBeachesList() {
            const filteredBeaches = getFilteredBeaches();

            if (filteredBeaches.length === 0) {
                beachesList.innerHTML = '<div class="empty-state">По заданным условиям пляжи не найдены.</div>';
                refreshMarkerVisibility();
                return;
            }

            beachesList.innerHTML = filteredBeaches.map(beach => {
                return `
                    <article class="list-card">
                        <h3>${beach.name || 'Без названия'}</h3>
                        <p class="list-meta"><strong>Номер:</strong> ${beach.number ?? '-'}</p>
                        <p class="list-meta"><strong>Уровень волнения:</strong> ${beach.wave_level ?? '-'} (${getWaveLevelText(beach.wave_level)})</p>
                        <span class="category-badge">${getBeachCategoryLabel(beach)}</span>
                        <div class="list-actions">
                            <button type="button" class="action-button primary" data-action="show-on-map" data-id="${beach.id}">Показать на карте</button>
                            <button type="button" class="action-button" data-action="show-details" data-id="${beach.id}">Подробно</button>
                        </div>
                    </article>
                `;
            }).join('');

            refreshMarkerVisibility();
        }

        function renderMapMarkers() {
            const markers = beaches.map(beach => {
                const marker = L.marker([beach.latitude, beach.longitude])
                    .bindPopup(buildPopupContent(beach))
                    .addTo(map);

                marker.on('click', function () {
                    selectedBeach = beach;
                    updateInfoPanel(beach);
                });

                markersById.set(beach.id, marker);
                return marker;
            });

            markersGroup = L.featureGroup(markers);

            if (markers.length > 0) {
                map.fitBounds(markersGroup.getBounds(), {
                    padding: [24, 24]
                });
            }
        }

        function focusBeachOnMap(beach) {
            const marker = markersById.get(beach.id);

            if (!marker) {
                return;
            }

            selectedBeach = beach;
            updateInfoPanel(beach);
            setActiveScreen('map-screen');

            if (!map.hasLayer(marker)) {
                marker.addTo(map);
            }

            map.setView([beach.latitude, beach.longitude], 14, { animate: true });
            marker.openPopup();
        }

        function openBeachDetails(beach) {
            selectedBeach = beach;
            updateDetailScreen(beach);
            setActiveScreen('detail-screen');
        }

        navButtons.forEach(button => {
            button.addEventListener('click', function () {
                setActiveScreen(button.dataset.screenTarget);
            });
        });

        searchInput.addEventListener('input', function () {
            searchQuery = searchInput.value.trim().toLowerCase();
            renderBeachesList();
        });

        filterChips.forEach(chip => {
            chip.addEventListener('click', function () {
                activeCategory = chip.dataset.category;

                filterChips.forEach(button => {
                    button.classList.toggle('active', button === chip);
                });

                renderBeachesList();
            });
        });

        beachesList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-action]');

            if (!button) {
                return;
            }

            const beachId = Number(button.dataset.id);
            const beach = beaches.find(item => item.id === beachId);

            if (!beach) {
                return;
            }

            if (button.dataset.action === 'show-on-map') {
                focusBeachOnMap(beach);
            }

            if (button.dataset.action === 'show-details') {
                openBeachDetails(beach);
            }
        });

        detailBackButton.addEventListener('click', function () {
            setActiveScreen(lastNonDetailScreen);
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        fetch('/api/beaches')
            .then(response => response.json())
            .then(data => {
                beaches.push(...data);
                renderMapMarkers();
                renderBeachesList();

                if (beaches.length > 0) {
                    updateInfoPanel(beaches[0]);
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки пляжей:', error);
                beachesList.innerHTML = '<div class="empty-state">Не удалось загрузить данные пляжей.</div>';
                alert('Не удалось загрузить данные пляжей');
            });
    </script>
</body>
</html>
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
            --status-safe: #2f9e44;
            --status-caution: #d9a404;
            --status-danger: #d94841;
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
            padding: 0;
        }

        .app-shell {
            width: 100%;
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

        .topbar-inner {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        .topbar-title {
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
        }

        .topbar-nav {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            max-width: 480px;
            margin: 0 auto;
            align-items: center;
        }

        .page-body {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
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

        .nav-button[data-screen-target="map-screen"] {
            font-size: 0.92em;
        }

        @media (max-width: 699px) {
            .topbar-nav {
                gap: 8px;
            }

            .nav-button {
                padding: 10px 12px;
                font-size: 15px;
            }

            .nav-button[data-screen-target="map-screen"] {
                font-size: 15px;
            }
        }

        .nav-button.active,
        .filter-chip.active,
        .action-button.primary,
        .back-button {
            background: #5f7283;
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
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .panel,
        .filter-panel,
        .detail-card,
        .list-card {
            padding: 16px;
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

        .map-layout {
            display: grid;
            gap: 14px;
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
            height: 62vh;
            min-height: 420px;
        }

        .screen-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
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

        .filter-chips,
        .list-wrap,
        .detail-fields {
            display: grid;
            gap: 10px;
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
            color: #5f7283;
            font-size: 13px;
            font-weight: 700;
        }

        .category-badge.badge-safe {
            background: var(--status-safe);
            color: #ffffff;
        }

        .category-badge.badge-caution {
            background: var(--status-caution);
            color: #ffffff;
        }

        .category-badge.badge-danger {
            background: var(--status-danger);
            color: #ffffff;
        }

        .empty-state {
            padding: 18px;
            border-radius: 18px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-soft);
            box-shadow: var(--shadow);
        }

        @media (max-width: 699px) {
            #info-panel h2 {
                font-size: 18px;
            }

            #info-panel p,
            #info-panel .info-note,
            #info-panel span {
                font-size: 10px;
                line-height: 1.4;
            }
        }

        @media (min-width: 700px) {
            body {
                padding: 18px;
            }

            .app-shell {
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 24px 48px rgba(23, 48, 66, 0.12);
                min-height: calc(100vh - 36px);
            }

            .topbar {
                padding: 20px 24px;
            }

            .screen {
                padding: 24px;
            }

            .topbar-title {
                font-size: 24px;
            }

            .topbar-nav {
                grid-template-columns: repeat(2, minmax(180px, 220px));
                justify-content: center;
            }

            .filter-chips {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .list-wrap {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .detail-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px 18px;
            }

            #map {
                height: 68vh;
                min-height: 520px;
            }
        }

        @media (min-width: 1024px) {
            .screen {
                padding: 28px;
            }

            .topbar-inner {
                max-width: 1840px;
            }

            .page-body {
                max-width: 1840px;
            }

            .map-layout {
                grid-template-columns: minmax(200px, 220px) minmax(0, 1fr);
                align-items: start;
                gap: 20px;
            }

            .map-layout .panel {
                position: sticky;
                top: 112px;
                margin: 0;
                max-height: calc(100vh - 160px);
                overflow: auto;
            }

            .topbar-title,
            .panel h2,
            .detail-card h2,
            .screen-title,
            .filter-title,
            .list-card h3,
            .nav-button,
            .action-button,
            .back-button,
            .filter-chip,
            .search-input,
            .panel p,
            .detail-field,
            .list-meta,
            .screen-subtitle,
            .filter-description,
            .info-note,
            .category-badge {
                font-size: 14px;
            }

            #map {
                height: calc(100vh - 145px);
                min-height: 680px;
            }

            .list-wrap {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }

            .list-card {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
        }

        @media (min-width: 1440px) {
            .topbar-inner,
            .page-body {
                max-width: 1880px;
            }

            .map-layout {
                grid-template-columns: minmax(190px, 210px) minmax(0, 1fr);
                gap: 22px;
            }

            #map {
                height: calc(100vh - 130px);
                min-height: 760px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-inner">
                <h1 class="topbar-title">Мониторинг пляжей Севастополя</h1>
                <div class="topbar-nav">
                    <button type="button" class="nav-button active" data-screen-target="map-screen">Карта</button>
                    <button type="button" class="nav-button" data-screen-target="list-screen">Доступные пляжи</button>
                </div>
            </div>
        </header>

        <main class="page-body">
            <section id="map-screen" class="screen active">
                <div class="map-layout">
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
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script>
        const map = L.map('map').setView([44.61, 33.52], 11);
        map.createPane('beachPolygonsPane');
        map.getPane('beachPolygonsPane').style.zIndex = '350';
        map.getPane('beachPolygonsPane').style.pointerEvents = 'auto';
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
        const cssVariables = getComputedStyle(document.documentElement);

        const polygonColors = {
            safe: cssVariables.getPropertyValue('--status-safe').trim(),
            caution: cssVariables.getPropertyValue('--status-caution').trim(),
            danger: cssVariables.getPropertyValue('--status-danger').trim()
        };

        const beaches = [];
        const markersById = new Map();
        let beachesPolygonLayer = null;
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

        function getBeachCategoryBadgeClass(beach) {
            const categoryKey = getBeachCategoryKey(beach);

            if (categoryKey === 'safe') {
                return 'badge-safe';
            }

            if (categoryKey === 'caution') {
                return 'badge-caution';
            }

            return 'badge-danger';
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

        function buildPolygonPopupContent(properties = {}) {
            const lines = [];
            const hasPrimaryData = properties.name ||
                (properties.number !== undefined && properties.number !== null && properties.number !== '') ||
                (properties.wave_level !== undefined && properties.wave_level !== null && properties.wave_level !== '') ||
                properties.category_label;

            if (properties.name) {
                lines.push('<b>' + properties.name + '</b>');
            }

            if (properties.number !== undefined && properties.number !== null && properties.number !== '') {
                lines.push('Номер: ' + properties.number);
            }

            if (properties.wave_level !== undefined && properties.wave_level !== null && properties.wave_level !== '') {
                lines.push('Уровень волнения: ' + properties.wave_level);
                lines.push('Описание: ' + getWaveLevelText(properties.wave_level));
            }

            if (hasPrimaryData) {
                lines.push('Категория: ' + getBeachCategoryLabel(properties));
            }

            return lines.join('<br>');
        }

        function getPolygonStyle(properties = {}) {
            const categoryKey = getBeachCategoryKey(properties);

            const polygonColor = polygonColors[categoryKey] || polygonColors.danger;

            return {
                color: polygonColor,
                weight: 2,
                opacity: 0.95,
                fillColor: polygonColor,
                fillOpacity: 0.28
            };
        }
        
        function ensureMarkersOnTop() {
            markersById.forEach(marker => {
                marker.setZIndexOffset(1000);

                if (typeof marker.bringToFront === 'function') {
                    marker.bringToFront();
                }
            });
        }

        function getMapDataBounds() {
            let combinedBounds = null;

            markersById.forEach(marker => {
                const latLng = marker.getLatLng();
                const pointBounds = L.latLngBounds(latLng, latLng);
                combinedBounds = combinedBounds ? combinedBounds.extend(pointBounds) : pointBounds;
            });

            if (beachesPolygonLayer && beachesPolygonLayer.getLayers().length > 0) {
                const polygonBounds = beachesPolygonLayer.getBounds();

                if (polygonBounds.isValid()) {
                    combinedBounds = combinedBounds ? combinedBounds.extend(polygonBounds) : polygonBounds;
                }
            }

            return combinedBounds;
        }

        function fitMapToAvailableData() {
            const bounds = getMapDataBounds();

            if (bounds && bounds.isValid()) {
                map.fitBounds(bounds, {
                    padding: [24, 24]
                });
            }
        }

        function getBeachPointFeature(beach) {
            const latitude = Number(beach.latitude);
            const longitude = Number(beach.longitude);

            if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
                return null;
            }

            return turf.point([longitude, latitude]);
        }

        function isBeachRelatedToPolygon(beach, feature) {
            const beachPoint = getBeachPointFeature(beach);

            if (!beachPoint || !feature || !feature.geometry) {
                return false;
            }

            try {
                if (turf.booleanPointInPolygon(beachPoint, feature)) {
                    return true;
                }

                const polygonOutline = turf.polygonToLine(feature);

                const distanceToPolygonKm = turf.pointToLineDistance(
                    beachPoint,
                    polygonOutline,
                    { units: 'kilometers' }
                );

                return distanceToPolygonKm <= 0.4;
            } catch (error) {
                console.error('Ошибка сопоставления пляжа и полигона:', error);
                return false;
            }
        }
        function getRelatedBeachesForFeature(feature) {
            if (!beaches.length) {
                return [];
            }

            return beaches.filter(beach => isBeachRelatedToPolygon(beach, feature));
        }

        function buildPolygonHoverContent(feature) {
            if (!beaches.length) {
                return '<strong>Связанные пляжи</strong><br>Данные пляжей еще загружаются';
            }

            const relatedBeaches = getRelatedBeachesForFeature(feature);

            if (relatedBeaches.length === 0) {
                return '<strong>Связанные пляжи</strong><br>Пляжи не найдены';
            }

            return '<strong>Связанные пляжи</strong><br>' + relatedBeaches
                .map(beach => beach.name || 'Без названия')
                .join('<br>');
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
                        <span class="category-badge ${getBeachCategoryBadgeClass(beach)}">${getBeachCategoryLabel(beach)}</span>
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
            beaches.forEach(beach => {
                const marker = L.marker([beach.latitude, beach.longitude])
                    .bindPopup(buildPopupContent(beach))
                    .addTo(map);

                marker.on('click', function () {
                    selectedBeach = beach;
                    updateInfoPanel(beach);
                });

                markersById.set(beach.id, marker);
            });

            ensureMarkersOnTop();
            fitMapToAvailableData();
        }

        function renderBeachPolygons(geoJson) {
            if (beachesPolygonLayer) {
                map.removeLayer(beachesPolygonLayer);
            }

            beachesPolygonLayer = L.geoJSON(geoJson, {
                pane: 'beachPolygonsPane',
                filter: function (feature) {
                    const geometryType = feature?.geometry?.type;
                    return geometryType === 'Polygon' || geometryType === 'MultiPolygon';
                },
                style: function (feature) {
                    const relatedBeaches = getRelatedBeachesForFeature(feature);
                    const source = relatedBeaches.length > 0
                        ? relatedBeaches[0]
                        : (feature.properties || {});

                    return getPolygonStyle(source);
                },
                onEachFeature: function (feature, layer) {
                    const properties = feature.properties || {};
                    const popupContent = buildPolygonPopupContent(properties);

                    if (popupContent) {
                        layer.bindPopup(popupContent);
                    }

                    layer.on('click', function () {
                        selectedBeach = properties;
                        updateInfoPanel(properties);

                        if (document.getElementById('detail-screen').classList.contains('active')) {
                            updateDetailScreen(properties);
                        }
                    });

                    layer.on('mouseover', function () {
                        layer
                            .bindTooltip(buildPolygonHoverContent(feature), {
                                sticky: true,
                                direction: 'top',
                                opacity: 0.95
                            })
                            .openTooltip();
                    });

                    layer.on('mouseout', function () {
                        layer.closeTooltip();
                    });
                }
            }).addTo(map);

            fitMapToAvailableData();
            ensureMarkersOnTop();
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

                if (beachesPolygonLayer) {
                    beachesPolygonLayer.setStyle(function (feature) {
                        const relatedBeaches = getRelatedBeachesForFeature(feature);
                        const source = relatedBeaches.length > 0
                            ? relatedBeaches[0]
                            : (feature.properties || {});

                        return getPolygonStyle(source);
                    });
                }

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

        fetch('/beaches-polygon.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ' ' + response.statusText);
                }

                return response.json();
            })
            .then(data => {
                if (!data || (data.type !== 'FeatureCollection' && data.type !== 'Feature')) {
                    throw new Error('Ожидался GeoJSON FeatureCollection или Feature.');
                }

                renderBeachPolygons(data);
            })
            .catch(error => {
                console.error('Ошибка загрузки GeoJSON полигонов пляжей из /sevastopol_beaches_renumbered.geojson:', error);
            });
    </script>
</body>
</html>

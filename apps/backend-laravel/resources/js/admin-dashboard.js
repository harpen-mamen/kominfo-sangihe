import Chart from 'chart.js/auto';
import { area as turfArea } from '@turf/turf';

const chartStore = new WeakMap();
const mapStateStore = new WeakMap();
const dashboardSelector = '.admin-dashboard-showcase';
const minMapZoom = 7;
const maxMapZoom = 18;
const compactBreakpoint = 960;
let initFrame = null;
let initTimer = null;

const baseMapCatalog = {
    street: {
        label: 'OpenStreetMap',
        tileUrl: (z, x, y) => `https://tile.openstreetmap.org/${z}/${x}/${y}.png`,
    },
    satellite: {
        label: 'Esri World Imagery',
        tileUrl: (z, x, y) => `https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/${z}/${y}/${x}`,
    },
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const numberFormatter = new Intl.NumberFormat('id-ID');
const decimalFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

const normalizeDistrict = (value) => String(value ?? '').trim().toLowerCase();
const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const parsePayload = (root) => {
    const payloadNode = root.querySelector('.admin-dashboard-showcase__payload');

    if (!payloadNode) {
        return null;
    }

    try {
        return JSON.parse(payloadNode.textContent ?? '{}');
    } catch {
        return null;
    }
};

const destroyCharts = (root) => {
    const chartState = chartStore.get(root);

    if (!chartState?.instances) {
        return;
    }

    Object.values(chartState.instances).forEach((chart) => chart?.destroy?.());
    chartState.instances = {};
    chartState.activeDistrict = null;
};

const baseChartOptions = (type) => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index',
    },
    animation: {
        duration: 420,
        easing: 'easeOutQuart',
    },
    plugins: {
        legend: {
            display: type === 'doughnut',
            position: 'bottom',
            labels: {
                usePointStyle: true,
                boxWidth: 10,
                boxHeight: 10,
                padding: 16,
                color: '#495057',
                font: {
                    family: 'system-ui, sans-serif',
                    size: 11,
                    weight: '600',
                },
            },
        },
        tooltip: {
            backgroundColor: 'rgba(33, 37, 41, 0.94)',
            titleColor: '#ffffff',
            bodyColor: '#f8f9fa',
            cornerRadius: 10,
            padding: 12,
            displayColors: true,
        },
    },
    scales: type === 'doughnut'
        ? {}
        : {
            x: {
                grid: {
                    display: false,
                },
                border: {
                    display: false,
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                        weight: '600',
                    },
                },
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(222, 226, 230, 0.85)',
                    drawBorder: false,
                },
                border: {
                    display: false,
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                    },
                },
            },
        },
});

const getChartState = (root) => {
    let chartState = chartStore.get(root);

    if (!chartState) {
        chartState = {
            instances: {},
            selections: {
                trend: null,
                comparison: null,
                composition: null,
            },
            activeDistrict: null,
        };

        chartStore.set(root, chartState);
    }

    return chartState;
};

const chartDomMap = {
    trend: '[data-admin-dashboard-trend]',
    comparison: '[data-admin-dashboard-comparison]',
    composition: '[data-admin-dashboard-composition]',
};

const renderPanelStats = (root, key, stats) => {
    const container = root.querySelector(`[data-admin-chart-stats="${key}"]`);

    if (!container) {
        return;
    }

    container.innerHTML = (stats ?? [])
        .map((item) => `
            <div>
                <span>${escapeHtml(item.label)}</span>
                <strong>${escapeHtml(item.value)}</strong>
            </div>
        `)
        .join('');
};

const updateChartText = (root, key, meta) => {
    const titleNode = root.querySelector(`[data-admin-chart-title="${key}"]`);
    const descriptionNode = root.querySelector(`[data-admin-chart-description="${key}"]`);

    if (titleNode) {
        titleNode.textContent = meta?.title ?? '-';
    }

    if (descriptionNode) {
        descriptionNode.textContent = meta?.description ?? '-';
    }

    renderPanelStats(root, key, meta?.stats ?? []);
};

const defaultChartFocus = (meta, key) => {
    if (key === 'trend') {
        return {
            label: meta?.scope_label ? `Kecamatan ${meta.scope_label}` : 'Fokus Interaktif',
            value: meta?.focus_indicator_label ?? '-',
            meta: meta?.stats?.[1]?.value ?? '-',
        };
    }

    if (key === 'comparison') {
        return {
            label: 'Wilayah Aktif',
            value: meta?.stats?.[1]?.value ?? '-',
            meta: meta?.stats?.[2]?.value ?? '-',
        };
    }

    return {
        label: meta?.scope_label ? `Komposisi ${meta.scope_label}` : 'Irisan Aktif',
        value: meta?.stats?.[0]?.value ?? '-',
        meta: meta?.stats?.[1]?.value ?? '-',
    };
};

const buildChartFocus = (meta, key, index) => {
    const labels = meta?.chart?.labels ?? [];
    const values = meta?.chart?.datasets?.[0]?.data ?? [];

    if (index === null || index === undefined || labels[index] === undefined) {
        return defaultChartFocus(meta, key);
    }

    const label = labels[index] ?? '-';
    const rawValue = Number(values[index] ?? 0);

    if (key === 'trend') {
        return {
            label: meta?.focus_indicator_label ?? 'Indikator',
            value: label,
            meta: `Nilai ${decimalFormatter.format(rawValue)}`,
        };
    }

    if (key === 'comparison') {
        return {
            label: 'Wilayah Tersorot',
            value: label,
            meta: `Nilai ${decimalFormatter.format(rawValue)}`,
        };
    }

    const total = values.reduce((sum, value) => sum + Number(value ?? 0), 0);
    const percentage = total > 0 ? (rawValue / total) * 100 : 0;

    return {
        label: 'Irisan Aktif',
        value: label,
        meta: `${decimalFormatter.format(rawValue)} (${decimalFormatter.format(percentage)}%)`,
    };
};

const updateChartFocusPanel = (root, key, meta, index) => {
    const focus = buildChartFocus(meta, key, index);
    const labelNode = root.querySelector(`[data-admin-chart-focus-label="${key}"]`);
    const valueNode = root.querySelector(`[data-admin-chart-focus-value="${key}"]`);
    const metaNode = root.querySelector(`[data-admin-chart-focus-meta="${key}"]`);

    if (labelNode) {
        labelNode.textContent = focus.label;
    }

    if (valueNode) {
        valueNode.textContent = focus.value;
    }

    if (metaNode) {
        metaNode.textContent = focus.meta;
    }
};

const cloneChartMeta = (meta) => JSON.parse(JSON.stringify(meta ?? {}));

const applyChartSelectionStyle = (chart, key, selectedIndex) => {
    const dataset = chart.data.datasets?.[0];

    if (!dataset) {
        return;
    }

    const totalItems = chart.data.labels?.length ?? 0;
    const baseBackground = Array.isArray(dataset._baseBackgroundColor)
        ? [...dataset._baseBackgroundColor]
        : dataset._baseBackgroundColor;
    const baseBorder = Array.isArray(dataset._baseBorderColor)
        ? [...dataset._baseBorderColor]
        : dataset._baseBorderColor;

    if (key === 'composition') {
        const colors = Array.isArray(baseBackground) ? [...baseBackground] : Array.from({ length: totalItems }, () => baseBackground ?? '#0d6efd');

        dataset.backgroundColor = colors.map((color, index) => (selectedIndex === index ? '#FACC15' : color));
        dataset.borderColor = colors.map((_, index) => (selectedIndex === index ? '#F59E0B' : '#ffffff'));
        dataset.borderWidth = colors.map((_, index) => (selectedIndex === index ? 3 : 2));
        dataset.offset = colors.map((_, index) => (selectedIndex === index ? 18 : 0));
    } else if (key === 'comparison') {
        const colors = Array.isArray(baseBackground) ? [...baseBackground] : Array.from({ length: totalItems }, () => baseBackground ?? '#0d6efd');

        dataset.backgroundColor = colors.map((color, index) => (selectedIndex === index ? '#FACC15' : color));
        dataset.borderColor = colors.map((_, index) => (selectedIndex === index ? '#F59E0B' : 'transparent'));
        dataset.borderWidth = colors.map((_, index) => (selectedIndex === index ? 2 : 0));
    } else {
        dataset.pointRadius = Array.from({ length: totalItems }, (_, index) => (selectedIndex === index ? 6 : 3));
        dataset.pointHoverRadius = Array.from({ length: totalItems }, (_, index) => (selectedIndex === index ? 8 : 5));
        dataset.pointBackgroundColor = Array.from({ length: totalItems }, (_, index) => (selectedIndex === index ? '#FACC15' : '#0d6efd'));
        dataset.pointBorderColor = Array.from({ length: totalItems }, (_, index) => (selectedIndex === index ? '#F59E0B' : '#ffffff'));
        dataset.pointBorderWidth = Array.from({ length: totalItems }, (_, index) => (selectedIndex === index ? 3 : 2));
        dataset.borderColor = selectedIndex !== null ? '#0b5ed7' : (baseBorder ?? '#0d6efd');
    }
};

const renderChartInstance = (root, key, meta) => {
    const chartState = getChartState(root);
    const canvas = root.querySelector(chartDomMap[key]);
    const config = meta?.chart;

    if (!canvas || !config?.labels?.length || !config?.datasets?.length) {
        chartState.instances[key]?.destroy?.();
        chartState.instances[key] = null;
        return null;
    }

    chartState.instances[key]?.destroy?.();

    const chartData = {
        labels: [...config.labels],
        datasets: config.datasets.map((dataset) => {
            const clone = { ...dataset };

            clone._baseBackgroundColor = Array.isArray(dataset.backgroundColor)
                ? [...dataset.backgroundColor]
                : dataset.backgroundColor;
            clone._baseBorderColor = Array.isArray(dataset.borderColor)
                ? [...dataset.borderColor]
                : dataset.borderColor;

            return clone;
        }),
    };

    const chart = new Chart(canvas, {
        type: config.type,
        data: chartData,
        options: {
            ...baseChartOptions(config.type),
            onHover: (event, elements, instance) => {
                canvas.style.cursor = elements.length ? 'pointer' : 'default';
                const selectedIndex = chartState.selections[key];

                if (elements.length) {
                    updateChartFocusPanel(root, key, meta, elements[0].index);
                } else {
                    updateChartFocusPanel(root, key, meta, selectedIndex);
                }

                instance.draw();
            },
            onClick: (_event, elements) => {
                const currentSelection = chartState.selections[key];
                const nextSelection = elements.length ? elements[0].index : null;

                chartState.selections[key] = currentSelection === nextSelection ? null : nextSelection;
                applyChartSelectionStyle(chart, key, chartState.selections[key]);
                chart.update();
                updateChartFocusPanel(root, key, meta, chartState.selections[key]);
            },
        },
    });

    canvas.onmouseleave = () => {
        updateChartFocusPanel(root, key, meta, chartState.selections[key]);
    };

    applyChartSelectionStyle(chart, key, chartState.selections[key]);
    chart.update();
    updateChartFocusPanel(root, key, meta, chartState.selections[key]);
    chartState.instances[key] = chart;

    return chart;
};

const getScopedChartMeta = (payload, districtName, key) => {
    if (districtName && payload?.district_charts?.[districtName]?.[key]) {
        return cloneChartMeta(payload.district_charts[districtName][key]);
    }

    return cloneChartMeta(payload?.[`${key}_meta`]);
};

const syncChartsForSelection = (root) => {
    const payload = root._adminDashboardPayload;

    if (!payload) {
        return;
    }

    const chartState = getChartState(root);
    const districtName = getMapState(root).selectedDistrict || null;

    if (chartState.activeDistrict === districtName && Object.keys(chartState.instances).length) {
        return;
    }

    chartState.activeDistrict = districtName;
    chartState.selections = {
        trend: null,
        comparison: null,
        composition: null,
    };

    ['trend', 'comparison', 'composition'].forEach((key) => {
        const meta = getScopedChartMeta(payload, districtName, key);

        updateChartText(root, key, meta);
        renderChartInstance(root, key, meta);
    });
};

const lngLatToWorld = (longitude, latitude, zoom) => {
    const scale = 256 * 2 ** zoom;
    const sin = Math.sin((clamp(latitude, -85.05112878, 85.05112878) * Math.PI) / 180);

    return {
        x: ((longitude + 180) / 360) * scale,
        y: (0.5 - Math.log((1 + sin) / (1 - sin)) / (4 * Math.PI)) * scale,
    };
};

const worldToLngLat = (x, y, zoom) => {
    const scale = 256 * 2 ** zoom;
    const longitude = (x / scale) * 360 - 180;
    const n = Math.PI - (2 * Math.PI * y) / scale;
    const latitude = (180 / Math.PI) * Math.atan(0.5 * (Math.exp(n) - Math.exp(-n)));

    return { latitude, longitude };
};

const flattenCoordinates = (geometry) => {
    if (!geometry?.type) {
        return [];
    }

    if (geometry.type === 'Point') {
        return [geometry.coordinates];
    }

    const walk = (value) => {
        if (!Array.isArray(value)) {
            return [];
        }

        if (typeof value[0] === 'number' && typeof value[1] === 'number') {
            return [value];
        }

        return value.flatMap(walk);
    };

    return walk(geometry.coordinates);
};

const buildPath = (geometry, project) => {
    if (!geometry?.coordinates) {
        return '';
    }

    const polygonToPath = (rings) =>
        rings
            .map((ring) =>
                ring
                    .map((point, index) => {
                        const [x, y] = project(point);

                        return `${index === 0 ? 'M' : 'L'} ${x} ${y}`;
                    })
                    .join(' ') + ' Z',
            )
            .join(' ');

    if (geometry.type === 'Polygon') {
        return polygonToPath(geometry.coordinates);
    }

    if (geometry.type === 'MultiPolygon') {
        return geometry.coordinates.map((polygon) => polygonToPath(polygon)).join(' ');
    }

    return '';
};

const getAlphaHex = (value) => Math.round(clamp(value, 0, 1) * 255).toString(16).padStart(2, '0').toUpperCase();

const collectMapFeatures = (mapConfig) =>
    (mapConfig?.layers ?? []).flatMap((layer) =>
        (layer.features ?? []).map((feature) => ({
            ...feature,
            color: feature.color ?? layer.color ?? '#0d6efd',
            fill_opacity: feature.fill_opacity ?? layer.fill_opacity ?? 0.18,
            weight: feature.weight ?? layer.weight ?? 1.4,
        })),
    );

const getFeatureKey = (feature) => [
    feature.layer_slug ?? '',
    feature.boundary_type ?? '',
    feature.name ?? '',
    feature.district ?? feature.scope ?? '',
].join('::');

const getFeatureDistrict = (feature) => {
    if (feature.boundary_type === 'kecamatan') {
        return String(feature.name ?? '');
    }

    return String(feature.district ?? feature.scope ?? '');
};

const getFeatureTypeLabel = (feature) => {
    if (feature.boundary_type === 'kecamatan') {
        return 'Kecamatan';
    }

    if (feature.boundary_type === 'desa') {
        return 'Kampung';
    }

    return 'Titik Layanan';
};

const getFeatureLabel = (feature) => {
    const typeLabel = getFeatureTypeLabel(feature);
    const districtLabel = getFeatureDistrict(feature);

    return `${typeLabel}: ${feature.name ?? '-'} | Kecamatan: ${districtLabel || '-'}`;
};

const formatArea = (squareKilometers) => {
    if (!Number.isFinite(squareKilometers) || squareKilometers <= 0) {
        return '-';
    }

    return `${decimalFormatter.format(squareKilometers)} km2`;
};

const formatCoordinate = (value) => decimalFormatter.format(Number(value ?? 0));

const renderStatGrid = (container, stats, className) => {
    if (!container) {
        return;
    }

    container.innerHTML = stats
        .map((stat) => `
            <div class="${className}">
                <span>${escapeHtml(stat.label)}</span>
                <strong>${escapeHtml(stat.value)}</strong>
            </div>
        `)
        .join('');
};

const renderChips = (container, values) => {
    if (!container) {
        return;
    }

    container.innerHTML = values
        .map((value) => `<span>${escapeHtml(value)}</span>`)
        .join('');
};

const getGeometryAreaSqKm = (geometry) => {
    if (!geometry || !['Polygon', 'MultiPolygon'].includes(String(geometry.type ?? ''))) {
        return 0;
    }

    try {
        return turfArea({
            type: 'Feature',
            properties: {},
            geometry,
        }) / 1000000;
    } catch {
        return 0;
    }
};

const fitPointsToViewport = (viewport, points, maxZoom = 12) => {
    if (!viewport || !points.length) {
        return {
            latitude: 3.6118,
            longitude: 125.5302,
            zoom: 9,
        };
    }

    const worldPoints = points.map(([longitude, latitude]) => lngLatToWorld(Number(longitude), Number(latitude), 0));
    const xs = worldPoints.map((point) => point.x);
    const ys = worldPoints.map((point) => point.y);
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);
    const width = Math.max((viewport.clientWidth || 320) - 96, 160);
    const height = Math.max((viewport.clientHeight || 320) - 120, 160);
    const spanX = Math.max(maxX - minX, 0.0001);
    const spanY = Math.max(maxY - minY, 0.0001);
    const zoom = clamp(Math.floor(Math.min(Math.log2(width / spanX), Math.log2(height / spanY))), minMapZoom, maxZoom);
    const centerPoint = worldToLngLat((minX + maxX) / 2, (minY + maxY) / 2, 0);

    return {
        latitude: centerPoint.latitude,
        longitude: centerPoint.longitude,
        zoom,
    };
};

const setDetailText = (root, value) => {
    const detailNode = root.querySelector('[data-admin-map-detail]');

    if (detailNode) {
        detailNode.textContent = value;
    }
};

const getMapState = (root) => {
    let state = mapStateStore.get(root);

    if (!state) {
        state = {
            baseLayer: 'street',
            zoom: 9,
            centerLatitude: 3.6118,
            centerLongitude: 125.5302,
            selectedDistrict: '',
            selectedVillage: '',
            selectedCategory: '',
            selectedOpd: '',
            selectedFeatureKey: '',
            visibleBoundaryTypes: new Set(['kecamatan', 'desa']),
            visibleLayerSlugs: new Set(),
            initialView: null,
            viewInitialized: false,
            dragStart: null,
            isDragging: false,
            renderFrame: null,
            popupOpen: false,
            sidebarOpen: window.innerWidth > compactBreakpoint,
        };

        mapStateStore.set(root, state);
    }

    return state;
};

const getAllFeatures = (mapConfig) => collectMapFeatures(mapConfig);

const getVisibleFeatures = (mapConfig, state) =>
    getAllFeatures(mapConfig).filter((feature) => {
        if (state.visibleLayerSlugs.size && !state.visibleLayerSlugs.has(feature.layer_slug)) {
            return false;
        }

        if (feature.boundary_type === 'kecamatan' && !state.visibleBoundaryTypes.has('kecamatan')) {
            return false;
        }

        if (feature.boundary_type === 'desa' && !state.visibleBoundaryTypes.has('desa')) {
            return false;
        }

        if (state.selectedDistrict && normalizeDistrict(getFeatureDistrict(feature)) !== normalizeDistrict(state.selectedDistrict)) {
            return false;
        }

        if (state.selectedVillage) {
            const villageName = String(feature.village ?? (feature.boundary_type === 'desa' ? feature.name : '') ?? '');

            if (normalizeDistrict(villageName) !== normalizeDistrict(state.selectedVillage)) {
                return false;
            }
        }

        if (state.selectedCategory && normalizeDistrict(feature.category ?? '') !== normalizeDistrict(state.selectedCategory)) {
            return false;
        }

        if (state.selectedOpd && normalizeDistrict(feature.opd ?? feature.properties?.opd ?? '') !== normalizeDistrict(state.selectedOpd)) {
            return false;
        }

        return true;
    });

const getSelectedFeature = (mapConfig, state) =>
    getAllFeatures(mapConfig).find((feature) => getFeatureKey(feature) === state.selectedFeatureKey) ?? null;

const getDefaultDetailText = (state, mapConfig) => {
    if (state.selectedFeatureKey) {
        return 'Klik tombol tutup pada popup atau pilih wilayah lain untuk memperbarui informasi detail.';
    }

    if (state.selectedDistrict) {
        return `Menyorot Kecamatan ${state.selectedDistrict}. Pilih kampung atau batas kecamatan untuk melihat detail yang lebih lengkap.`;
    }

    return mapConfig?.note ?? 'Pilih fitur peta untuk melihat detail.';
};

const getFeaturesForDistrict = (features, district) => {
    const normalized = normalizeDistrict(district);

    return normalized
        ? features.filter((feature) => normalizeDistrict(getFeatureDistrict(feature)) === normalized)
        : features;
};

const getDistrictSummary = (features, district) => {
    const districtFeatures = getFeaturesForDistrict(features, district);
    const districtBoundary = districtFeatures.find((feature) => feature.boundary_type === 'kecamatan');
    const villageFeatures = districtFeatures.filter((feature) => feature.boundary_type === 'desa');
    const pointFeatures = districtFeatures.filter((feature) => !feature.boundary_type);
    const districtArea = districtBoundary
        ? getGeometryAreaSqKm(districtBoundary.geometry)
        : villageFeatures.reduce((total, feature) => total + getGeometryAreaSqKm(feature.geometry), 0);

    return {
        districtBoundary,
        villageFeatures,
        pointFeatures,
        boundaryCount: districtFeatures.filter((feature) => feature.boundary_type).length,
        districtArea,
    };
};

const buildSelectionInfo = (root, mapConfig) => {
    const state = getMapState(root);
    const allFeatures = getAllFeatures(mapConfig);
    const selectedFeature = getSelectedFeature(mapConfig, state);
    const selectedDistrict = state.selectedDistrict || (selectedFeature ? getFeatureDistrict(selectedFeature) : '');
    const districtSummary = selectedDistrict ? getDistrictSummary(allFeatures, selectedDistrict) : null;

    if (selectedFeature?.boundary_type === 'desa') {
        return {
            type: 'Kampung Terpilih',
            title: selectedFeature.name ?? '-',
            subtitle: `Kecamatan ${selectedFeature.district ?? '-'} sedang menjadi fokus utama pada peta interaktif.`,
            meta: ['Batas Kampung', `Kecamatan ${selectedFeature.district ?? '-'}`],
            stats: [
                { label: 'Luas', value: formatArea(getGeometryAreaSqKm(selectedFeature.geometry)) },
                { label: 'Kecamatan', value: selectedFeature.district ?? '-' },
                { label: 'Titik Layanan', value: numberFormatter.format(districtSummary?.pointFeatures.length ?? 0) },
                { label: 'Boundary', value: numberFormatter.format(districtSummary?.boundaryCount ?? 0) },
            ],
            focusLabel: `Kampung di ${selectedFeature.district ?? 'wilayah fokus'}`,
            focusAreas: (districtSummary?.villageFeatures ?? []).map((feature) => feature.name).filter(Boolean).slice(0, 10),
        };
    }

    if (selectedDistrict) {
        return {
            type: 'Kecamatan Terpilih',
            title: selectedDistrict,
            subtitle: `${numberFormatter.format(districtSummary?.villageFeatures.length ?? 0)} kampung dan ${numberFormatter.format(districtSummary?.pointFeatures.length ?? 0)} titik layanan berada dalam fokus saat ini.`,
            meta: ['Batas Kecamatan', `Boundary ${numberFormatter.format(districtSummary?.boundaryCount ?? 0)}`],
            stats: [
                { label: 'Luas', value: formatArea(districtSummary?.districtArea ?? 0) },
                { label: 'Desa', value: numberFormatter.format(districtSummary?.villageFeatures.length ?? 0) },
                { label: 'Fitur Titik', value: numberFormatter.format(districtSummary?.pointFeatures.length ?? 0) },
                { label: 'Boundary', value: numberFormatter.format(districtSummary?.boundaryCount ?? 0) },
            ],
            focusLabel: `Kampung di ${selectedDistrict}`,
            focusAreas: (districtSummary?.villageFeatures ?? []).map((feature) => feature.name).filter(Boolean).slice(0, 12),
        };
    }

    if (selectedFeature) {
        const coordinates = selectedFeature.geometry?.type === 'Point'
            ? selectedFeature.geometry.coordinates ?? []
            : [];

        return {
            type: 'Titik Layanan',
            title: selectedFeature.name ?? '-',
            subtitle: `Layer ${selectedFeature.layer ?? 'peta'} berada pada ${selectedFeature.scope ?? 'wilayah terpilih'}.`,
            meta: [selectedFeature.layer ?? 'Layer aktif'],
            stats: [
                { label: 'Kecamatan', value: selectedFeature.scope ?? '-' },
                { label: 'Longitude', value: coordinates[0] !== undefined ? formatCoordinate(coordinates[0]) : '-' },
                { label: 'Latitude', value: coordinates[1] !== undefined ? formatCoordinate(coordinates[1]) : '-' },
                { label: 'Status Layer', value: 'Aktif' },
            ],
            focusLabel: 'Area yang ditampilkan',
            focusAreas: mapConfig?.areas ?? [],
        };
    }

    return {
        type: 'Ringkasan Wilayah',
        title: mapConfig?.title ?? 'Peta Digital Wilayah',
        subtitle: mapConfig?.note ?? 'Pilih layer atau wilayah untuk melihat detail dinamis.',
        meta: [mapConfig?.status_label ?? 'Boundary aktif', `${numberFormatter.format(mapConfig?.layers?.length ?? 0)} layer aktif`],
        stats: mapConfig?.coverage ?? [],
        focusLabel: 'Area yang ditampilkan',
        focusAreas: mapConfig?.areas ?? [],
    };
};

const updateSelectionPanels = (root, mapConfig) => {
    const info = buildSelectionInfo(root, mapConfig);
    const typeNode = root.querySelector('[data-admin-map-selection-type]');
    const titleNode = root.querySelector('[data-admin-map-selection-title]');
    const subtitleNode = root.querySelector('[data-admin-map-selection-subtitle]');
    const metaNode = root.querySelector('[data-admin-map-selection-meta]');
    const focusLabelNode = root.querySelector('[data-admin-map-focus-label]');

    if (typeNode) {
        typeNode.textContent = info.type;
    }

    if (titleNode) {
        titleNode.textContent = info.title;
    }

    if (subtitleNode) {
        subtitleNode.textContent = info.subtitle;
    }

    if (metaNode) {
        metaNode.innerHTML = info.meta.map((item) => `<span>${escapeHtml(item)}</span>`).join('');
    }

    renderStatGrid(root.querySelector('[data-admin-map-stats]'), info.stats, 'adminlte-map__stat-card');

    if (focusLabelNode) {
        focusLabelNode.textContent = info.focusLabel;
    }

    renderChips(root.querySelector('[data-admin-map-focus-areas]'), info.focusAreas);
};

const setButtonGroupState = (root, selector, activeValue, attributeName) => {
    root.querySelectorAll(selector).forEach((button) => {
        const isActive = button.getAttribute(attributeName) === activeValue;
        button.classList.toggle('is-active', isActive);
    });
};

const syncMapUi = (root, mapConfig) => {
    const state = getMapState(root);
    const zoomOutButton = root.querySelector('[data-admin-map-zoom-out]');
    const zoomInButton = root.querySelector('[data-admin-map-zoom-in]');
    const zoomLevel = root.querySelector('[data-admin-map-zoom-level]');
    const attributionNode = root.querySelector('[data-admin-map-attribution]');
    const districtSelect = root.querySelector('[data-admin-map-district]');
    const villageSelect = root.querySelector('[data-admin-map-village]');
    const categorySelect = root.querySelector('[data-admin-map-category]');
    const opdSelect = root.querySelector('[data-admin-map-opd]');
    const fullscreenButton = root.querySelector('[data-admin-map-fullscreen]');
    const sidebarToggle = root.querySelector('[data-admin-map-sidebar-toggle]');
    const sidebar = root.querySelector('[data-admin-map-sidebar]');
    const stage = root.querySelector('[data-admin-map-stage]');

    if (window.innerWidth > compactBreakpoint) {
        state.sidebarOpen = true;
    }

    if (zoomLevel) {
        zoomLevel.textContent = `${Math.round((state.zoom / 10) * 100)}%`;
    }

    if (zoomOutButton) {
        zoomOutButton.disabled = state.zoom <= minMapZoom;
    }

    if (zoomInButton) {
        zoomInButton.disabled = state.zoom >= maxMapZoom;
    }

    if (attributionNode) {
        attributionNode.textContent = baseMapCatalog[state.baseLayer]?.label ?? 'OpenStreetMap';
    }

    if (districtSelect && districtSelect.value !== state.selectedDistrict) {
        districtSelect.value = state.selectedDistrict;
    }

    if (villageSelect && villageSelect.value !== state.selectedVillage) {
        villageSelect.value = state.selectedVillage;
    }

    if (categorySelect && categorySelect.value !== state.selectedCategory) {
        categorySelect.value = state.selectedCategory;
    }

    if (opdSelect && opdSelect.value !== state.selectedOpd) {
        opdSelect.value = state.selectedOpd;
    }

    setButtonGroupState(root, '[data-admin-map-style]', state.baseLayer, 'data-admin-map-style');

    root.querySelectorAll('[data-admin-map-layer-toggle]').forEach((button) => {
        const boundaryType = button.getAttribute('data-admin-map-layer-toggle');
        button.classList.toggle('is-active', state.visibleBoundaryTypes.has(boundaryType));
    });

    root.querySelectorAll('[data-admin-map-layer]').forEach((button) => {
        const layerSlug = button.getAttribute('data-admin-map-layer');
        button.classList.toggle('is-active', state.visibleLayerSlugs.has(layerSlug));
    });

    if (fullscreenButton && stage) {
        fullscreenButton.textContent = document.fullscreenElement === stage ? 'Exit' : 'Full';
    }

    if (sidebarToggle) {
        sidebarToggle.textContent = state.sidebarOpen ? 'Tutup' : 'Info';
    }

    if (sidebar) {
        sidebar.classList.toggle('is-open', state.sidebarOpen);
    }

    setDetailText(root, getDefaultDetailText(state, mapConfig));
    updateSelectionPanels(root, mapConfig);
};

const renderTileLayer = (viewport, tilePane, state) => {
    const width = Math.max(viewport.clientWidth || 0, 320);
    const height = Math.max(viewport.clientHeight || 0, 320);
    const centerWorld = lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom);
    const startX = centerWorld.x - width / 2;
    const startY = centerWorld.y - height / 2;
    const endX = centerWorld.x + width / 2;
    const endY = centerWorld.y + height / 2;
    const minTileX = Math.floor(startX / 256);
    const maxTileX = Math.floor(endX / 256);
    const minTileY = Math.floor(startY / 256);
    const maxTileY = Math.floor(endY / 256);
    const maxTile = 2 ** state.zoom;
    const fragment = document.createDocumentFragment();
    const baseMap = baseMapCatalog[state.baseLayer] ?? baseMapCatalog.street;

    tilePane.innerHTML = '';

    for (let tileX = minTileX; tileX <= maxTileX; tileX += 1) {
        for (let tileY = minTileY; tileY <= maxTileY; tileY += 1) {
            if (tileY < 0 || tileY >= maxTile) {
                continue;
            }

            const tile = document.createElement('img');
            const wrappedX = ((tileX % maxTile) + maxTile) % maxTile;

            tile.alt = '';
            tile.draggable = false;
            tile.loading = 'lazy';
            tile.src = baseMap.tileUrl(state.zoom, wrappedX, tileY);
            tile.style.left = `${Math.round(tileX * 256 - startX)}px`;
            tile.style.top = `${Math.round(tileY * 256 - startY)}px`;

            fragment.appendChild(tile);
        }
    }

    tilePane.appendChild(fragment);
};

const getFilteredFeaturePoints = (features, selectedDistrict) => {
    if (!selectedDistrict) {
        return features.flatMap((feature) => flattenCoordinates(feature.geometry));
    }

    const scopedFeatures = getFeaturesForDistrict(features, selectedDistrict);

    return (scopedFeatures.length ? scopedFeatures : features).flatMap((feature) => flattenCoordinates(feature.geometry));
};

const applyDistrictFocus = (root, mapConfig, district, restoreInitialView = false) => {
    const viewport = root.querySelector('[data-admin-dashboard-map]');
    const state = getMapState(root);

    if (!viewport) {
        return;
    }

    if (restoreInitialView && state.initialView) {
        state.centerLatitude = state.initialView.latitude;
        state.centerLongitude = state.initialView.longitude;
        state.zoom = state.initialView.zoom;
        return;
    }

    const features = getAllFeatures(mapConfig);
    const points = getFilteredFeaturePoints(features, district);
    const view = fitPointsToViewport(viewport, points, district ? 12 : 11);

    state.centerLatitude = view.latitude;
    state.centerLongitude = view.longitude;
    state.zoom = view.zoom;
};

const getProjectedAnchor = (feature, project) => {
    if (feature.geometry?.type === 'Point') {
        return project(feature.geometry.coordinates);
    }

    const projectedPoints = flattenCoordinates(feature.geometry).map(project);

    if (!projectedPoints.length) {
        return [160, 160];
    }

    const xs = projectedPoints.map(([x]) => x);
    const ys = projectedPoints.map(([, y]) => y);

    return [
        (Math.min(...xs) + Math.max(...xs)) / 2,
        (Math.min(...ys) + Math.max(...ys)) / 2,
    ];
};

const buildPopupInfo = (feature, allFeatures) => {
    const district = getFeatureDistrict(feature);
    const districtSummary = district ? getDistrictSummary(allFeatures, district) : null;

    if (feature.boundary_type === 'desa') {
        return {
            type: 'Kampung',
            title: feature.name ?? '-',
            subtitle: `Kecamatan ${feature.district ?? '-'} sedang disorot dan wilayahnya sudah terseleksi di peta.`,
            stats: [
                { label: 'Luas', value: formatArea(getGeometryAreaSqKm(feature.geometry)) },
                { label: 'Kecamatan', value: feature.district ?? '-' },
                { label: 'Titik', value: numberFormatter.format(districtSummary?.pointFeatures.length ?? 0) },
                { label: 'Boundary', value: numberFormatter.format(districtSummary?.boundaryCount ?? 0) },
            ],
        };
    }

    if (feature.boundary_type === 'kecamatan') {
        return {
            type: 'Kecamatan',
            title: feature.name ?? '-',
            subtitle: `${numberFormatter.format(districtSummary?.villageFeatures.length ?? 0)} kampung berada di dalam kecamatan ini.`,
            stats: [
                { label: 'Luas', value: formatArea(districtSummary?.districtArea ?? 0) },
                { label: 'Desa', value: numberFormatter.format(districtSummary?.villageFeatures.length ?? 0) },
                { label: 'Titik', value: numberFormatter.format(districtSummary?.pointFeatures.length ?? 0) },
                { label: 'Boundary', value: numberFormatter.format(districtSummary?.boundaryCount ?? 0) },
            ],
        };
    }

    const coordinates = feature.geometry?.coordinates ?? [];

    return {
        type: 'Titik Layanan',
        title: feature.name ?? '-',
        subtitle: `${feature.layer ?? 'Layer aktif'} pada ${feature.scope ?? 'wilayah terpilih'}.`,
        stats: [
            { label: 'Kecamatan', value: feature.scope ?? '-' },
            { label: 'Longitude', value: coordinates[0] !== undefined ? formatCoordinate(coordinates[0]) : '-' },
            { label: 'Latitude', value: coordinates[1] !== undefined ? formatCoordinate(coordinates[1]) : '-' },
            { label: 'Layer', value: feature.layer ?? '-' },
        ],
    };
};

const renderPopup = (root, mapConfig, project) => {
    const state = getMapState(root);
    const popup = root.querySelector('[data-admin-map-popup]');
    const popupType = root.querySelector('[data-admin-map-popup-type]');
    const popupTitle = root.querySelector('[data-admin-map-popup-title]');
    const popupSubtitle = root.querySelector('[data-admin-map-popup-subtitle]');
    const popupStats = root.querySelector('[data-admin-map-popup-stats]');
    const stage = root.querySelector('[data-admin-map-stage]');

    if (!popup || !stage || !state.popupOpen || !state.selectedFeatureKey) {
        if (popup) {
            popup.hidden = true;
        }

        return;
    }

    const selectedFeature = getSelectedFeature(mapConfig, state);

    if (!selectedFeature) {
        popup.hidden = true;
        return;
    }

    const allFeatures = getAllFeatures(mapConfig);
    const info = buildPopupInfo(selectedFeature, allFeatures);
    const [anchorX, anchorY] = getProjectedAnchor(selectedFeature, project);

    popup.hidden = false;
    popup.classList.remove('is-bottom');

    if (popupType) {
        popupType.textContent = info.type;
    }

    if (popupTitle) {
        popupTitle.textContent = info.title;
    }

    if (popupSubtitle) {
        popupSubtitle.textContent = info.subtitle;
    }

    renderStatGrid(popupStats, info.stats, 'adminlte-map__popup-stat');

    const stageWidth = stage.clientWidth || 320;
    const stageHeight = stage.clientHeight || 320;
    const popupWidth = popup.offsetWidth || 280;
    const popupHeight = popup.offsetHeight || 180;
    const left = clamp(anchorX, popupWidth / 2 + 12, stageWidth - popupWidth / 2 - 12);
    const shouldShowBelow = anchorY < popupHeight + 28;
    const top = shouldShowBelow
        ? clamp(anchorY + 16, 16, stageHeight - popupHeight - 16)
        : clamp(anchorY - 16, popupHeight + 16, stageHeight - 16);

    popup.style.left = `${left}px`;
    popup.style.top = `${top}px`;
    popup.classList.toggle('is-bottom', shouldShowBelow);
};

const renderOverlay = (root, viewport, overlayPane, mapConfig) => {
    const state = getMapState(root);
    const visibleFeatures = getVisibleFeatures(mapConfig, state);
    const visibleKeys = new Set(visibleFeatures.map((feature) => getFeatureKey(feature)));

    if (!visibleFeatures.length) {
        overlayPane.innerHTML = '<div class="adminlte-map__empty">Filter layer aktif saat ini tidak menampilkan data di peta.</div>';
        root.querySelector('[data-admin-map-popup]')?.setAttribute('hidden', 'hidden');
        return;
    }

    if (state.selectedFeatureKey && !visibleKeys.has(state.selectedFeatureKey)) {
        state.popupOpen = false;
    }

    const width = Math.max(viewport.clientWidth || 0, 320);
    const height = Math.max(viewport.clientHeight || 0, 320);
    const centerWorld = lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom);
    const startX = centerWorld.x - width / 2;
    const startY = centerWorld.y - height / 2;
    const svgNS = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(svgNS, 'svg');
    const selectedDistrict = normalizeDistrict(state.selectedDistrict);

    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('class', 'adminlte-map__svg');

    const project = ([longitude, latitude]) => {
        const world = lngLatToWorld(Number(longitude), Number(latitude), state.zoom);

        return [world.x - startX, world.y - startY];
    };

    visibleFeatures.forEach((feature) => {
        const geometryType = String(feature.geometry?.type ?? '').toLowerCase();
        const featureKey = getFeatureKey(feature);
        const district = normalizeDistrict(getFeatureDistrict(feature));
        const isInSelectedDistrict = selectedDistrict && district === selectedDistrict;
        const isMuted = selectedDistrict && !isInSelectedDistrict;
        const isExactSelected = state.selectedFeatureKey === featureKey;

        if (geometryType === 'polygon' || geometryType === 'multipolygon') {
            const path = document.createElementNS(svgNS, 'path');
            const fillColor = isExactSelected
                ? 'rgba(250, 204, 21, 0.72)'
                : `${feature.color}${getAlphaHex(feature.fill_opacity ?? 0.18)}`;
            const strokeColor = isExactSelected
                ? '#EAB308'
                : isInSelectedDistrict
                    ? '#F59E0B'
                    : feature.color;
            const strokeWidth = isExactSelected
                ? Math.max((feature.weight ?? 1.4) + 2.4, 3.6)
                : isInSelectedDistrict
                    ? Math.max((feature.weight ?? 1.4) + 1.2, 2.6)
                    : feature.weight ?? 1.4;

            path.setAttribute('d', buildPath(feature.geometry, project));
            path.setAttribute('fill', fillColor);
            path.setAttribute('stroke', strokeColor);
            path.setAttribute('stroke-width', String(strokeWidth));
            path.setAttribute('class', 'adminlte-map__shape');
            path.dataset.label = getFeatureLabel(feature);

            if (isMuted) {
                path.classList.add('is-muted');
            }

            if (isExactSelected) {
                path.classList.add('is-selected');
            }

            path.addEventListener('mouseenter', () => {
                setDetailText(root, path.dataset.label ?? getDefaultDetailText(state, mapConfig));
            });

            path.addEventListener('mouseleave', () => {
                setDetailText(root, getDefaultDetailText(state, mapConfig));
            });

            path.addEventListener('click', () => {
                state.selectedFeatureKey = featureKey;
                state.popupOpen = true;
                state.selectedDistrict = feature.boundary_type === 'kecamatan'
                    ? String(feature.name ?? '')
                    : String(feature.district ?? '');

                applyDistrictFocus(root, mapConfig, state.selectedDistrict);
                if (window.innerWidth <= compactBreakpoint) {
                    state.sidebarOpen = false;
                }
                queueMapRender(root);
            });

            svg.appendChild(path);
            return;
        }

        if (geometryType === 'point') {
            const [centerX, centerY] = project(feature.geometry.coordinates);
            const marker = document.createElementNS(svgNS, 'g');
            const halo = document.createElementNS(svgNS, 'circle');
            const circle = document.createElementNS(svgNS, 'circle');

            halo.setAttribute('cx', String(centerX));
            halo.setAttribute('cy', String(centerY));
            halo.setAttribute('r', isExactSelected ? '16' : isInSelectedDistrict ? '14' : '10');
            halo.setAttribute('fill', isExactSelected ? '#FACC152E' : isInSelectedDistrict ? '#F59E0B22' : `${feature.color}22`);

            circle.setAttribute('cx', String(centerX));
            circle.setAttribute('cy', String(centerY));
            circle.setAttribute('r', isExactSelected ? '7' : isInSelectedDistrict ? '6' : '5');
            circle.setAttribute('fill', isExactSelected ? '#FACC15' : isInSelectedDistrict ? '#F59E0B' : feature.color);
            circle.setAttribute('stroke', '#ffffff');
            circle.setAttribute('stroke-width', '2');

            marker.setAttribute('class', 'adminlte-map__marker');
            marker.dataset.label = getFeatureLabel(feature);

            if (isMuted) {
                marker.classList.add('is-muted');
            }

            marker.addEventListener('mouseenter', () => {
                setDetailText(root, marker.dataset.label ?? getDefaultDetailText(state, mapConfig));
            });

            marker.addEventListener('mouseleave', () => {
                setDetailText(root, getDefaultDetailText(state, mapConfig));
            });

            marker.addEventListener('click', () => {
                state.selectedFeatureKey = featureKey;
                state.popupOpen = true;

                if (!state.selectedDistrict && feature.scope) {
                    const matchDistrict = (mapConfig?.district_options ?? []).find(
                        (item) => normalizeDistrict(item) === normalizeDistrict(feature.scope),
                    );

                    if (matchDistrict) {
                        state.selectedDistrict = matchDistrict;
                    }
                }

                queueMapRender(root);
            });

            marker.appendChild(halo);
            marker.appendChild(circle);
            svg.appendChild(marker);
        }
    });

    overlayPane.innerHTML = '';
    overlayPane.appendChild(svg);
    renderPopup(root, mapConfig, project);
};

const ensureStateFromPayload = (root, mapConfig) => {
    const state = getMapState(root);

    if (!state.visibleLayerSlugs.size) {
        mapConfig.layers.forEach((layer) => {
            state.visibleLayerSlugs.add(layer.slug);
        });
    }
};

const renderInteractiveMap = (root, mapConfig) => {
    const viewport = root.querySelector('[data-admin-dashboard-map]');
    const tilePane = root.querySelector('[data-admin-map-tiles]');
    const overlayPane = root.querySelector('[data-admin-map-overlay]');
    const state = getMapState(root);

    if (!viewport || !tilePane || !overlayPane) {
        return;
    }

    ensureStateFromPayload(root, mapConfig);

    const features = getAllFeatures(mapConfig);
    const points = features.flatMap((feature) => flattenCoordinates(feature.geometry));

    if (!points.length) {
        tilePane.innerHTML = '';
        overlayPane.innerHTML = `<div class="adminlte-map__empty">${mapConfig?.note ?? 'Belum ada fitur peta yang bisa divisualisasikan.'}</div>`;
        return;
    }

    if (!state.viewInitialized) {
        const initialView = fitPointsToViewport(viewport, points, 11);

        state.centerLatitude = initialView.latitude;
        state.centerLongitude = initialView.longitude;
        state.zoom = initialView.zoom;
        state.initialView = initialView;
        state.viewInitialized = true;
    }

    renderTileLayer(viewport, tilePane, state);
    renderOverlay(root, viewport, overlayPane, mapConfig);
    syncMapUi(root, mapConfig);
};

const queueMapRender = (root) => {
    const payload = root._adminDashboardPayload;
    const state = getMapState(root);

    if (!payload?.map) {
        return;
    }

    if (state.renderFrame) {
        window.cancelAnimationFrame(state.renderFrame);
    }

    state.renderFrame = window.requestAnimationFrame(() => {
        renderInteractiveMap(root, payload.map);
        syncChartsForSelection(root);
        state.renderFrame = null;
    });
};

const bindViewportInteractions = (root) => {
    if (root.dataset.adminMapViewportReady === 'true') {
        return;
    }

    const viewport = root.querySelector('[data-admin-dashboard-map]');

    if (!viewport) {
        return;
    }

    const state = getMapState(root);

    viewport.addEventListener('pointerdown', (event) => {
        const centerWorld = lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom);

        state.dragStart = {
            x: event.clientX,
            y: event.clientY,
            centerWorld,
        };
        state.isDragging = false;
        viewport.setPointerCapture(event.pointerId);
    });

    viewport.addEventListener('pointermove', (event) => {
        if (!state.dragStart) {
            return;
        }

        const deltaX = event.clientX - state.dragStart.x;
        const deltaY = event.clientY - state.dragStart.y;

        if (Math.abs(deltaX) + Math.abs(deltaY) < 3) {
            return;
        }

        state.isDragging = true;
        viewport.classList.add('is-dragging');

        const nextCenter = worldToLngLat(
            state.dragStart.centerWorld.x - deltaX,
            state.dragStart.centerWorld.y - deltaY,
            state.zoom,
        );

        state.centerLatitude = nextCenter.latitude;
        state.centerLongitude = nextCenter.longitude;
        queueMapRender(root);
    });

    const finishDragging = () => {
        window.setTimeout(() => {
            state.dragStart = null;
            state.isDragging = false;
            viewport.classList.remove('is-dragging');
        }, 0);
    };

    viewport.addEventListener('pointerup', finishDragging);
    viewport.addEventListener('pointercancel', finishDragging);
    viewport.addEventListener('pointerleave', () => {
        if (state.dragStart) {
            finishDragging();
        }
    });

    viewport.addEventListener('wheel', (event) => {
        event.preventDefault();

        const nextZoom = clamp(state.zoom + (event.deltaY < 0 ? 1 : -1), minMapZoom, maxMapZoom);

        if (nextZoom === state.zoom) {
            return;
        }

        const rect = viewport.getBoundingClientRect();
        const width = Math.max(viewport.clientWidth || 0, 320);
        const height = Math.max(viewport.clientHeight || 0, 320);
        const pointerX = event.clientX - rect.left;
        const pointerY = event.clientY - rect.top;
        const centerWorld = lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom);
        const pointerWorldX = centerWorld.x - width / 2 + pointerX;
        const pointerWorldY = centerWorld.y - height / 2 + pointerY;
        const hoveredLocation = worldToLngLat(pointerWorldX, pointerWorldY, state.zoom);
        const hoveredWorldAtNextZoom = lngLatToWorld(hoveredLocation.longitude, hoveredLocation.latitude, nextZoom);
        const nextCenterWorldX = hoveredWorldAtNextZoom.x - (pointerX - width / 2);
        const nextCenterWorldY = hoveredWorldAtNextZoom.y - (pointerY - height / 2);
        const nextCenter = worldToLngLat(nextCenterWorldX, nextCenterWorldY, nextZoom);

        state.zoom = nextZoom;
        state.centerLatitude = nextCenter.latitude;
        state.centerLongitude = nextCenter.longitude;
        queueMapRender(root);
    }, { passive: false });

    viewport.addEventListener('dblclick', () => {
        const nextZoom = clamp(state.zoom + 1, minMapZoom, maxMapZoom);

        if (nextZoom !== state.zoom) {
            state.zoom = nextZoom;
            queueMapRender(root);
        }
    });

    root.dataset.adminMapViewportReady = 'true';
};

const initializeMapControls = (root) => {
    if (root.dataset.adminMapControlsReady === 'true') {
        return;
    }

    const state = getMapState(root);
    const stage = root.querySelector('[data-admin-map-stage]');
    const districtSelect = root.querySelector('[data-admin-map-district]');
    const villageSelect = root.querySelector('[data-admin-map-village]');
    const categorySelect = root.querySelector('[data-admin-map-category]');
    const opdSelect = root.querySelector('[data-admin-map-opd]');

    root.querySelectorAll('[data-admin-map-style]').forEach((button) => {
        button.addEventListener('click', () => {
            const style = button.getAttribute('data-admin-map-style');

            if (!style || !baseMapCatalog[style]) {
                return;
            }

            state.baseLayer = style;
            queueMapRender(root);
        });
    });

    root.querySelector('[data-admin-map-sidebar-toggle]')?.addEventListener('click', () => {
        state.sidebarOpen = !state.sidebarOpen;
        queueMapRender(root);
    });

    root.querySelector('[data-admin-map-reset]')?.addEventListener('click', () => {
        state.selectedDistrict = '';
        state.selectedVillage = '';
        state.selectedCategory = '';
        state.selectedOpd = '';
        state.selectedFeatureKey = '';
        state.popupOpen = false;

        if (districtSelect) {
            districtSelect.value = '';
        }

        if (villageSelect) {
            villageSelect.value = '';
        }

        if (categorySelect) {
            categorySelect.value = '';
        }

        if (opdSelect) {
            opdSelect.value = '';
        }

        applyDistrictFocus(root, root._adminDashboardPayload?.map, '', true);
        queueMapRender(root);
    });

    root.querySelector('[data-admin-map-zoom-out]')?.addEventListener('click', () => {
        state.zoom = clamp(state.zoom - 1, minMapZoom, maxMapZoom);
        queueMapRender(root);
    });

    root.querySelector('[data-admin-map-zoom-in]')?.addEventListener('click', () => {
        state.zoom = clamp(state.zoom + 1, minMapZoom, maxMapZoom);
        queueMapRender(root);
    });

    root.querySelector('[data-admin-map-fullscreen]')?.addEventListener('click', async () => {
        if (!stage) {
            return;
        }

        try {
            if (document.fullscreenElement === stage) {
                await document.exitFullscreen();
            } else {
                await stage.requestFullscreen();
            }
        } catch {
            queueMapRender(root);
        }
    });

    root.querySelector('[data-admin-map-popup-close]')?.addEventListener('click', () => {
        state.popupOpen = false;
        queueMapRender(root);
    });

    root.querySelectorAll('[data-admin-map-layer-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const boundaryType = button.getAttribute('data-admin-map-layer-toggle');

            if (!boundaryType) {
                return;
            }

            if (state.visibleBoundaryTypes.has(boundaryType)) {
                state.visibleBoundaryTypes.delete(boundaryType);
            } else {
                state.visibleBoundaryTypes.add(boundaryType);
            }

            queueMapRender(root);
        });
    });

    root.querySelectorAll('[data-admin-map-layer]').forEach((button) => {
        button.addEventListener('click', () => {
            const layerSlug = button.getAttribute('data-admin-map-layer');

            if (!layerSlug) {
                return;
            }

            if (state.visibleLayerSlugs.has(layerSlug)) {
                state.visibleLayerSlugs.delete(layerSlug);
            } else {
                state.visibleLayerSlugs.add(layerSlug);
            }

            queueMapRender(root);
        });
    });

    districtSelect?.addEventListener('change', (event) => {
        state.selectedDistrict = event.target.value;
        state.selectedFeatureKey = '';
        state.popupOpen = false;
        applyDistrictFocus(root, root._adminDashboardPayload?.map, state.selectedDistrict, !state.selectedDistrict);
        queueMapRender(root);
    });

    villageSelect?.addEventListener('change', (event) => {
        state.selectedVillage = event.target.value;
        state.selectedFeatureKey = '';
        state.popupOpen = false;
        queueMapRender(root);
    });

    categorySelect?.addEventListener('change', (event) => {
        state.selectedCategory = event.target.value;
        state.selectedFeatureKey = '';
        state.popupOpen = false;
        queueMapRender(root);
    });

    opdSelect?.addEventListener('change', (event) => {
        state.selectedOpd = event.target.value;
        state.selectedFeatureKey = '';
        state.popupOpen = false;
        queueMapRender(root);
    });

    document.addEventListener('fullscreenchange', () => {
        queueMapRender(root);
    });

    bindViewportInteractions(root);
    root.dataset.adminMapControlsReady = 'true';
};

const initDashboard = () => {
    document.querySelectorAll(dashboardSelector).forEach((root) => {
        const payloadNode = root.querySelector('.admin-dashboard-showcase__payload');
        const payloadText = payloadNode?.textContent ?? '';
        const payload = parsePayload(root);

        if (!payload) {
            return;
        }

        const payloadChanged = root._adminDashboardPayloadText !== payloadText;

        root._adminDashboardPayload = payload;
        root._adminDashboardPayloadText = payloadText;

        if (payloadChanged) {
            destroyCharts(root);
        }

        initializeMapControls(root);
        queueMapRender(root);
    });
};

const scheduleInitDashboard = () => {
    if (initFrame) {
        window.cancelAnimationFrame(initFrame);
    }

    window.clearTimeout(initTimer);

    initFrame = window.requestAnimationFrame(() => {
        window.clearTimeout(initTimer);
        initTimer = null;
        initDashboard();
        initFrame = null;
    });

    initTimer = window.setTimeout(() => {
        if (initFrame) {
            window.cancelAnimationFrame(initFrame);
            initFrame = null;
        }

        initDashboard();
        initTimer = null;
    }, 180);
};

let resizeTimer;

window.addEventListener('resize', () => {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(scheduleInitDashboard, 160);
});

document.addEventListener('DOMContentLoaded', scheduleInitDashboard);
document.addEventListener('livewire:navigated', scheduleInitDashboard);
document.addEventListener('livewire:initialized', scheduleInitDashboard);
document.addEventListener('livewire:updated', scheduleInitDashboard);

new MutationObserver((mutations) => {
    const hasDashboardMutation = mutations.some(({ addedNodes }) => {
        return Array.from(addedNodes).some((node) =>
            node instanceof Element
            && (node.matches?.(dashboardSelector) || node.querySelector?.(dashboardSelector)),
        );
    });

    if (hasDashboardMutation) {
        scheduleInitDashboard();
    }
}).observe(document.documentElement, {
    childList: true,
    subtree: true,
});

scheduleInitDashboard();

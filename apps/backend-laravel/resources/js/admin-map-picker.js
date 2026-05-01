const pickerSelector = '[data-admin-map-picker]';

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const roundCoordinate = (value) => Number.parseFloat(value).toFixed(8);

const setNativeValue = (element, value) => {
    if (!element) {
        return;
    }

    const prototype = element instanceof HTMLTextAreaElement
        ? HTMLTextAreaElement.prototype
        : HTMLInputElement.prototype;
    const descriptor = Object.getOwnPropertyDescriptor(prototype, 'value');

    descriptor?.set?.call(element, value);
    element.dispatchEvent(new Event('input', { bubbles: true }));
    element.dispatchEvent(new Event('change', { bubbles: true }));
};

const readNumber = (element, fallback) => {
    const parsed = Number.parseFloat(element?.value ?? '');

    return Number.isFinite(parsed) ? parsed : fallback;
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

const updateGeoJson = (geoJsonInput, latitude, longitude) => {
    if (!geoJsonInput) {
        return;
    }

    setNativeValue(
        geoJsonInput,
        JSON.stringify({
            type: 'Point',
            coordinates: [
                Number.parseFloat(roundCoordinate(longitude)),
                Number.parseFloat(roundCoordinate(latitude)),
            ],
        }, null, 2),
    );
};

const initializePicker = (root) => {
    if (root.dataset.adminMapPickerReady === 'true') {
        return;
    }

    root.dataset.adminMapPickerReady = 'true';

    const form = root.closest('form') ?? document;
    const latitudeInput = form.querySelector('[data-map-latitude-input]');
    const longitudeInput = form.querySelector('[data-map-longitude-input]');
    const geoJsonInput = form.querySelector('[data-map-geojson-input]');
    const canvas = root.querySelector('[data-map-canvas]');
    const marker = root.querySelector('[data-map-marker]');
    const status = root.querySelector('[data-map-status]');
    const useLocationButton = root.querySelector('[data-map-use-location]');
    const resetButton = root.querySelector('[data-map-reset]');
    const zoomInButton = root.querySelector('[data-map-zoom-in]');
    const zoomOutButton = root.querySelector('[data-map-zoom-out]');
    const defaultLatitude = Number.parseFloat(root.dataset.defaultLatitude ?? '3.6118');
    const defaultLongitude = Number.parseFloat(root.dataset.defaultLongitude ?? '125.5302');

    if (!canvas || !latitudeInput || !longitudeInput) {
        return;
    }

    const state = {
        centerLatitude: readNumber(latitudeInput, defaultLatitude),
        centerLongitude: readNumber(longitudeInput, defaultLongitude),
        latitude: readNumber(latitudeInput, defaultLatitude),
        longitude: readNumber(longitudeInput, defaultLongitude),
        zoom: Number.parseInt(root.dataset.defaultZoom ?? '12', 10),
        isDragging: false,
        dragStart: null,
    };

    const syncStatus = (message = null) => {
        if (!status) {
            return;
        }

        status.textContent = message ?? `Lat ${roundCoordinate(state.latitude)} | Lng ${roundCoordinate(state.longitude)}`;
    };

    const render = () => {
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(rect.width, 1);
        const height = Math.max(rect.height, 1);
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

        canvas.querySelectorAll('[data-map-tile]').forEach((tile) => tile.remove());

        for (let x = minTileX; x <= maxTileX; x += 1) {
            for (let y = minTileY; y <= maxTileY; y += 1) {
                if (y < 0 || y >= maxTile) {
                    continue;
                }

                const tile = document.createElement('img');
                const wrappedX = ((x % maxTile) + maxTile) % maxTile;

                tile.dataset.mapTile = 'true';
                tile.alt = '';
                tile.draggable = false;
                tile.loading = 'lazy';
                tile.src = `https://tile.openstreetmap.org/${state.zoom}/${wrappedX}/${y}.png`;
                tile.style.left = `${Math.round(x * 256 - startX)}px`;
                tile.style.top = `${Math.round(y * 256 - startY)}px`;

                fragment.appendChild(tile);
            }
        }

        canvas.prepend(fragment);

        const selectedWorld = lngLatToWorld(state.longitude, state.latitude, state.zoom);
        const markerLeft = selectedWorld.x - startX;
        const markerTop = selectedWorld.y - startY;

        if (marker) {
            marker.style.left = `${markerLeft}px`;
            marker.style.top = `${markerTop}px`;
        }

        syncStatus();
    };

    const setCoordinates = (latitude, longitude, shouldCenter = true, message = null) => {
        state.latitude = clamp(latitude, -90, 90);
        state.longitude = clamp(longitude, -180, 180);

        if (shouldCenter) {
            state.centerLatitude = state.latitude;
            state.centerLongitude = state.longitude;
        }

        setNativeValue(latitudeInput, roundCoordinate(state.latitude));
        setNativeValue(longitudeInput, roundCoordinate(state.longitude));
        updateGeoJson(geoJsonInput, state.latitude, state.longitude);
        render();
        syncStatus(message);
    };

    const pickFromCanvasPoint = (clientX, clientY) => {
        const rect = canvas.getBoundingClientRect();
        const centerWorld = lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom);
        const worldX = centerWorld.x - rect.width / 2 + clientX - rect.left;
        const worldY = centerWorld.y - rect.height / 2 + clientY - rect.top;
        const point = worldToLngLat(worldX, worldY, state.zoom);

        setCoordinates(point.latitude, point.longitude, false, 'Titik peta diperbarui.');
    };

    latitudeInput.addEventListener('change', () => {
        setCoordinates(readNumber(latitudeInput, state.latitude), readNumber(longitudeInput, state.longitude));
    });

    longitudeInput.addEventListener('change', () => {
        setCoordinates(readNumber(latitudeInput, state.latitude), readNumber(longitudeInput, state.longitude));
    });

    canvas.addEventListener('click', (event) => {
        if (state.isDragging) {
            return;
        }

        pickFromCanvasPoint(event.clientX, event.clientY);
    });

    canvas.addEventListener('pointerdown', (event) => {
        state.dragStart = {
            x: event.clientX,
            y: event.clientY,
            center: lngLatToWorld(state.centerLongitude, state.centerLatitude, state.zoom),
        };

        state.isDragging = false;
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!state.dragStart) {
            return;
        }

        const deltaX = event.clientX - state.dragStart.x;
        const deltaY = event.clientY - state.dragStart.y;

        if (Math.abs(deltaX) + Math.abs(deltaY) < 4) {
            return;
        }

        state.isDragging = true;

        const point = worldToLngLat(
            state.dragStart.center.x - deltaX,
            state.dragStart.center.y - deltaY,
            state.zoom,
        );

        state.centerLatitude = point.latitude;
        state.centerLongitude = point.longitude;
        render();
    });

    canvas.addEventListener('pointerup', () => {
        window.setTimeout(() => {
            state.isDragging = false;
            state.dragStart = null;
        }, 0);
    });

    canvas.addEventListener('pointercancel', () => {
        state.isDragging = false;
        state.dragStart = null;
    });

    canvas.addEventListener('wheel', (event) => {
        event.preventDefault();
        state.zoom = clamp(state.zoom + (event.deltaY < 0 ? 1 : -1), 5, 18);
        render();
    }, { passive: false });

    useLocationButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            syncStatus('Browser tidak mendukung geolokasi.');

            return;
        }

        syncStatus('Mengambil lokasi perangkat...');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setCoordinates(position.coords.latitude, position.coords.longitude, true, 'Lokasi perangkat dipakai.');
            },
            () => syncStatus('Lokasi perangkat tidak bisa diambil.'),
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 },
        );
    });

    resetButton?.addEventListener('click', () => {
        setCoordinates(defaultLatitude, defaultLongitude, true, 'Peta dipusatkan ke Sangihe.');
    });

    zoomInButton?.addEventListener('click', () => {
        state.zoom = clamp(state.zoom + 1, 5, 18);
        render();
    });

    zoomOutButton?.addEventListener('click', () => {
        state.zoom = clamp(state.zoom - 1, 5, 18);
        render();
    });

    window.addEventListener('resize', render);
    setCoordinates(state.latitude, state.longitude);
};

const initializePickers = () => {
    document.querySelectorAll(pickerSelector).forEach(initializePicker);
};

initializePickers();

document.addEventListener('DOMContentLoaded', initializePickers);
document.addEventListener('livewire:navigated', initializePickers);
document.addEventListener('livewire:updated', initializePickers);

new MutationObserver(initializePickers).observe(document.documentElement, {
    childList: true,
    subtree: true,
});

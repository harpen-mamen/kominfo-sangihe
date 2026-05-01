<div
    class="admin-map-picker"
    data-admin-map-picker
    data-default-latitude="3.6118"
    data-default-longitude="125.5302"
    data-default-zoom="12"
>
    <div class="admin-map-picker__toolbar">
        <button type="button" class="admin-map-picker__button admin-map-picker__button--primary" data-map-use-location>
            Ambil Koordinat Saya
        </button>
        <button type="button" class="admin-map-picker__button" data-map-reset>
            Pusat Sangihe
        </button>
        <button type="button" class="admin-map-picker__icon-button" data-map-zoom-in aria-label="Perbesar peta">
            +
        </button>
        <button type="button" class="admin-map-picker__icon-button" data-map-zoom-out aria-label="Perkecil peta">
            -
        </button>
        <span class="admin-map-picker__status" data-map-status></span>
    </div>

    <div class="admin-map-picker__canvas" data-map-canvas role="application" aria-label="Pilih titik koordinat peta">
        <span class="admin-map-picker__marker" data-map-marker aria-hidden="true"></span>
        <a class="admin-map-picker__attribution" href="https://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">
            OpenStreetMap
        </a>
    </div>
</div>

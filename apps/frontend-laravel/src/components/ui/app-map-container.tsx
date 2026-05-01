"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import Link from "next/link";
import {
  CalendarDays,
  Layers3,
  MapPin,
  Newspaper,
  Search,
} from "lucide-react";
import maplibregl, { type GeoJSONSource, type LngLatBoundsLike, type StyleSpecification } from "maplibre-gl";
import type { MapPoint, MapWorkbenchFeature, MapWorkbenchLayer } from "@/lib/portal-data";
import { cn } from "@/lib/utils";

type AppMapContainerProps = {
  points?: MapPoint[];
  workbenchLayers?: MapWorkbenchLayer[];
  initialView?: {
    center: [number, number];
    zoom: number;
  };
  className?: string;
  height?: number;
  showSearch?: boolean;
  mode?: "compact" | "workbench";
};

const defaultInitialView = { center: [125.5302, 3.6118] as [number, number], zoom: 11 };

const basemaps: Record<"street" | "satellite" | "light", StyleSpecification> = {
  street: {
    version: 8,
    sources: {
      raster: {
        type: "raster",
        tiles: ["https://tile.openstreetmap.org/{z}/{x}/{y}.png"],
        tileSize: 256,
        attribution: "&copy; OpenStreetMap Contributors",
      },
    },
    layers: [{ id: "street", type: "raster", source: "raster" }],
  },
  satellite: {
    version: 8,
    sources: {
      raster: {
        type: "raster",
        tiles: [
          "https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        ],
        tileSize: 256,
        attribution: "Esri, Maxar, Earthstar Geographics",
      },
    },
    layers: [{ id: "satellite", type: "raster", source: "raster" }],
  },
  light: {
    version: 8,
    sources: {
      raster: {
        type: "raster",
        tiles: ["https://basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png"],
        tileSize: 256,
        attribution: "&copy; OpenStreetMap, &copy; CARTO",
      },
    },
    layers: [{ id: "light", type: "raster", source: "raster" }],
  },
};

function featureIcon(kind: MapWorkbenchFeature["kind"]) {
  if (kind === "news") {
    return <Newspaper size={15} />;
  }

  if (kind === "event") {
    return <CalendarDays size={15} />;
  }

  return <MapPin size={15} />;
}

function popupMarkup(feature: MapWorkbenchFeature) {
  const dateMarkup = feature.startsAt
    ? `<div class="map-popup__meta">${new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      }).format(new Date(feature.startsAt))}</div>`
    : "";

  const locationMarkup = feature.locationLabel
    ? `<div class="map-popup__meta">${feature.locationLabel}</div>`
    : "";

  const summaryMarkup = feature.summary ? `<p>${feature.summary}</p>` : "";
  const linkMarkup = feature.detailUrl
    ? `<a class="map-popup__link" href="${feature.detailUrl}">Buka detail</a>`
    : "";

  return `
    <div class="map-popup">
      <strong>${feature.name}</strong>
      ${feature.subtitle ? `<span>${feature.subtitle}</span>` : ""}
      ${locationMarkup}
      ${dateMarkup}
      ${summaryMarkup}
      ${linkMarkup}
    </div>
  `;
}

function buildCompactLayers(points: MapPoint[]): MapWorkbenchLayer[] {
  return [
    {
      id: "compact-points",
      slug: "compact-points",
      name: "Titik Peta",
      kind: "gis",
      layerType: "point",
      featureCount: points.length,
      department: "Publik",
      color: "#0f766e",
      features: points.map((point, index) => ({
        id: `compact-${index}`,
        name: point.name,
        coordinates: point.coordinates,
        layerName: point.layer,
        layerSlug: "compact-points",
        kind: "gis",
        color: point.color,
        summary: point.layer,
      })),
    },
  ];
}

export function AppMapContainer({
  points = [],
  workbenchLayers,
  initialView = defaultInitialView,
  className,
  height = 420,
  showSearch = true,
  mode = "compact",
}: AppMapContainerProps) {
  const containerRef = useRef<HTMLDivElement | null>(null);
  const mapRef = useRef<maplibregl.Map | null>(null);
  const popupRef = useRef<maplibregl.Popup | null>(null);
  const initialViewRef = useRef(initialView);
  const [basemap, setBasemap] = useState<"street" | "satellite" | "light">(
    mode === "workbench" ? "satellite" : "light",
  );
  const activeBasemapRef = useRef(basemap);
  const [keyword, setKeyword] = useState("");
  const layers = useMemo(
    () => (workbenchLayers?.length ? workbenchLayers : buildCompactLayers(points)),
    [points, workbenchLayers],
  );
  const defaultLayerState = useMemo(
    () => Object.fromEntries(layers.map((layer) => [layer.id, true])),
    [layers],
  );
  const [layerOverrides, setLayerOverrides] = useState<Record<string, boolean>>({});
  const activeLayerIds = useMemo(
    () => ({ ...defaultLayerState, ...layerOverrides }),
    [defaultLayerState, layerOverrides],
  );
  const [selectedFeatureId, setSelectedFeatureId] = useState<string | null>(null);

  const visibleFeatures = useMemo(() => {
    const lowered = keyword.trim().toLowerCase();

    return layers.flatMap((layer) =>
      activeLayerIds[layer.id] === false
        ? []
        : layer.features.filter((feature) =>
            lowered.length === 0
              ? true
              : [
                  feature.name,
                  feature.summary,
                  feature.subtitle,
                  feature.locationLabel,
                  layer.name,
                ]
                  .filter(Boolean)
                  .some((value) => String(value).toLowerCase().includes(lowered)),
          ),
    );
  }, [activeLayerIds, keyword, layers]);

  const selectedFeature =
    visibleFeatures.find((feature) => feature.id === selectedFeatureId) ?? visibleFeatures[0] ?? null;

  useEffect(() => {
    if (!containerRef.current || mapRef.current) {
      return;
    }

    const startingView = initialViewRef.current;

    mapRef.current = new maplibregl.Map({
      container: containerRef.current,
      style: basemaps[activeBasemapRef.current],
      center: startingView.center,
      zoom: startingView.zoom,
    });

    mapRef.current.addControl(new maplibregl.NavigationControl(), "bottom-right");
    mapRef.current.addControl(new maplibregl.FullscreenControl(), "bottom-right");

    return () => {
      popupRef.current?.remove();
      mapRef.current?.remove();
      mapRef.current = null;
    };
  }, []);

  useEffect(() => {
    if (!mapRef.current || activeBasemapRef.current === basemap) {
      return;
    }

    activeBasemapRef.current = basemap;
    mapRef.current.setStyle(basemaps[basemap]);
  }, [basemap]);

  useEffect(() => {
    const map = mapRef.current;

    if (!map) {
      return;
    }

    const syncLayers = () => {
      layers.forEach((layer) => {
        const sourceId = `source-${layer.id}`;
        const fillId = `fill-${layer.id}`;
        const lineId = `line-${layer.id}`;
        const pointId = `point-${layer.id}`;

        const activeFeatures = activeLayerIds[layer.id] === false
          ? []
          : layer.features
              .filter((feature) =>
                keyword.trim().length === 0
                  ? true
                  : [
                      feature.name,
                      feature.summary,
                      feature.subtitle,
                      feature.locationLabel,
                      layer.name,
                    ]
                      .filter(Boolean)
                      .some((value) =>
                        String(value)
                          .toLowerCase()
                          .includes(keyword.trim().toLowerCase()),
                      ),
              )
              .map((feature) => ({
                type: "Feature",
                geometry:
                  feature.rawGeometry && typeof feature.rawGeometry === "object"
                    ? feature.rawGeometry
                    : {
                        type: "Point",
                        coordinates: feature.coordinates,
                      },
                properties: {
                  id: feature.id,
                  name: feature.name,
                  summary: feature.summary,
                  subtitle: feature.subtitle,
                  detailUrl: feature.detailUrl,
                  locationLabel: feature.locationLabel,
                  startsAt: feature.startsAt,
                  color: feature.color,
                  kind: feature.kind,
                  layerName: layer.name,
                },
              }));

        const featureCollection = {
          type: "FeatureCollection" as const,
          features: activeFeatures.map((feature) => ({
            ...feature,
            type: "Feature" as const,
          })),
        } as Parameters<GeoJSONSource["setData"]>[0];

        if (map.getSource(sourceId)) {
          (map.getSource(sourceId) as GeoJSONSource).setData(featureCollection);
        } else {
          map.addSource(sourceId, {
            type: "geojson",
            data: featureCollection,
          });
        }

        if (!map.getLayer(fillId)) {
          map.addLayer({
            id: fillId,
            type: "fill",
            source: sourceId,
            filter: ["==", ["geometry-type"], "Polygon"],
            paint: {
              "fill-color": layer.color,
              "fill-opacity": 0.18,
            },
          });
        }

        if (!map.getLayer(lineId)) {
          map.addLayer({
            id: lineId,
            type: "line",
            source: sourceId,
            filter: [
              "any",
              ["==", ["geometry-type"], "LineString"],
              ["==", ["geometry-type"], "Polygon"],
            ],
            paint: {
              "line-color": layer.color,
              "line-width": 2,
            },
          });
        }

        if (!map.getLayer(pointId)) {
          map.addLayer({
            id: pointId,
            type: "circle",
            source: sourceId,
            filter: ["==", ["geometry-type"], "Point"],
            paint: {
              "circle-radius": mode === "workbench" ? 7 : 6,
              "circle-color": layer.color,
              "circle-stroke-color": "#ffffff",
              "circle-stroke-width": 2,
            },
          });

          map.on("click", pointId, (event) => {
            const feature = activeFeatures.find((item) => item.properties.id === event.features?.[0]?.properties?.id);

            if (!feature || !event.lngLat) {
              return;
            }

            setSelectedFeatureId(String(feature.properties.id));
            popupRef.current?.remove();
            popupRef.current = new maplibregl.Popup({ offset: 14 })
              .setLngLat(event.lngLat)
              .setHTML(
                popupMarkup({
                  id: String(feature.properties.id),
                  name: String(feature.properties.name),
                  coordinates: [event.lngLat.lng, event.lngLat.lat],
                  layerName: layer.name,
                  layerSlug: layer.slug,
                  kind: (feature.properties.kind as MapWorkbenchFeature["kind"]) ?? "gis",
                  color: String(feature.properties.color ?? layer.color),
                  summary: feature.properties.summary ? String(feature.properties.summary) : undefined,
                  subtitle: feature.properties.subtitle ? String(feature.properties.subtitle) : undefined,
                  locationLabel: feature.properties.locationLabel
                    ? String(feature.properties.locationLabel)
                    : undefined,
                  startsAt: feature.properties.startsAt ? String(feature.properties.startsAt) : undefined,
                  detailUrl: feature.properties.detailUrl ? String(feature.properties.detailUrl) : undefined,
                }),
              )
              .addTo(map);
          });
        }
      });
    };

    if (map.isStyleLoaded()) {
      syncLayers();
    } else {
      map.once("styledata", syncLayers);
    }

    return () => {
      map.off("styledata", syncLayers);
    };
  }, [activeLayerIds, basemap, keyword, layers, mode]);

  useEffect(() => {
    const map = mapRef.current;

    if (!map || !visibleFeatures.length) {
      return;
    }

    const bounds = new maplibregl.LngLatBounds();

    visibleFeatures.forEach((feature) => {
      bounds.extend(feature.coordinates);
    });

    if (!bounds.isEmpty()) {
      map.fitBounds(bounds as LngLatBoundsLike, {
        padding: mode === "workbench" ? 72 : 42,
        maxZoom: visibleFeatures.length === 1 ? 14 : 12,
        duration: 800,
      });
    }
  }, [mode, visibleFeatures]);

  return (
    <div className={cn("map-workbench", `map-workbench--${mode}`, className)}>
      {mode === "workbench" ? (
        <aside className="map-workbench__sidebar">
          <div className="map-workbench__sidebar-head">
            <div>
              <span className="map-workbench__eyebrow">Peta Interaktif</span>
              <h2>Peta Digital Kabupaten</h2>
            </div>
            <Layers3 size={18} />
          </div>

          {showSearch ? (
            <label className="map-workbench__search">
              <Search size={16} />
              <input
                onChange={(event) => setKeyword(event.target.value)}
                placeholder="Cari lokasi, berita, agenda, atau layer"
                type="search"
                value={keyword}
              />
            </label>
          ) : null}

          <div className="map-workbench__layers">
            {layers.map((layer) => (
              <button
                className="map-layer-toggle"
                data-active={activeLayerIds[layer.id] !== false}
                key={layer.id}
                onClick={() =>
                    setLayerOverrides((current) => ({
                      ...current,
                      [layer.id]: activeLayerIds[layer.id] === false,
                    }))
                  }
                type="button"
              >
                <span className="map-layer-toggle__dot" style={{ background: layer.color }} />
                <span className="map-layer-toggle__copy">
                  <strong>{layer.name}</strong>
                  <small>
                    {layer.featureCount} fitur{layer.department ? ` • ${layer.department}` : ""}
                  </small>
                </span>
              </button>
            ))}
          </div>

          <div className="map-workbench__feature-list">
            {visibleFeatures.slice(0, 12).map((feature) => (
              <button
                className="map-feature-card"
                data-active={selectedFeature?.id === feature.id}
                key={feature.id}
                onClick={() => setSelectedFeatureId(feature.id)}
                type="button"
              >
                <span className="map-feature-card__icon" style={{ color: feature.color }}>
                  {featureIcon(feature.kind)}
                </span>
                <span className="map-feature-card__copy">
                  <strong>{feature.name}</strong>
                  <small>{feature.subtitle ?? feature.layerName}</small>
                </span>
              </button>
            ))}
          </div>

          {selectedFeature ? (
            <div className="map-workbench__detail">
              <div className="map-workbench__detail-head">
                <span className="map-workbench__detail-icon" style={{ color: selectedFeature.color }}>
                  {featureIcon(selectedFeature.kind)}
                </span>
                <div>
                  <strong>{selectedFeature.name}</strong>
                  <small>{selectedFeature.subtitle ?? selectedFeature.layerName}</small>
                </div>
              </div>
              {selectedFeature.locationLabel ? <p>{selectedFeature.locationLabel}</p> : null}
              {selectedFeature.summary ? <p>{selectedFeature.summary}</p> : null}
              {selectedFeature.startsAt ? (
                <p>
                  {new Intl.DateTimeFormat("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  }).format(new Date(selectedFeature.startsAt))}
                </p>
              ) : null}
              {selectedFeature.detailUrl ? (
                <Link className="map-workbench__detail-link" href={selectedFeature.detailUrl}>
                  Buka detail
                </Link>
              ) : null}
            </div>
          ) : null}
        </aside>
      ) : null}

      <section className="map-workbench__surface">
        <div className="map-shell__toolbar">
          <div className="map-basemap-toggle" role="group">
            {(["satellite", "street", "light"] as const).map((item) => (
              <button
                aria-pressed={basemap === item}
                className="map-basemap-toggle__button"
                data-active={basemap === item}
                key={item}
                onClick={() => setBasemap(item)}
                type="button"
              >
                {item === "satellite" ? "Satelit" : item === "street" ? "Jalan" : "Terang"}
              </button>
            ))}
          </div>
          <div className="map-shell__summary">
            <span>{visibleFeatures.length} fitur aktif</span>
          </div>
        </div>

        <div
          className="map-shell__viewport map-shell__viewport--workbench"
          ref={containerRef}
          style={{ height }}
        />
      </section>
    </div>
  );
}

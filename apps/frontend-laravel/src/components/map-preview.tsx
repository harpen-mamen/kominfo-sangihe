"use client";

import { useEffect, useRef } from "react";
import maplibregl from "maplibre-gl";
import type { StyleSpecification } from "maplibre-gl";

type MarkerPoint = {
  name: string;
  coordinates: [number, number];
  color: string;
};

type MapPreviewProps = {
  points: MarkerPoint[];
};

const style: StyleSpecification = {
  version: 8,
  sources: {
    osm: {
      type: "raster",
      tiles: ["https://tile.openstreetmap.org/{z}/{x}/{y}.png"],
      tileSize: 256,
      attribution: "&copy; OpenStreetMap Contributors",
    },
  },
  layers: [
    {
      id: "osm",
      type: "raster",
      source: "osm",
    },
  ],
};

export function MapPreview({ points }: MapPreviewProps) {
  const containerRef = useRef<HTMLDivElement | null>(null);
  const mapRef = useRef<maplibregl.Map | null>(null);

  useEffect(() => {
    if (!containerRef.current || mapRef.current) {
      return;
    }

    mapRef.current = new maplibregl.Map({
      container: containerRef.current,
      style,
      center: [125.53, 3.61],
      zoom: 8,
    });

    points.forEach((point) => {
      const marker = document.createElement("div");
      marker.style.width = "14px";
      marker.style.height = "14px";
      marker.style.borderRadius = "999px";
      marker.style.border = "2px solid white";
      marker.style.boxShadow = "0 8px 20px rgba(15, 23, 42, 0.16)";
      marker.style.background = point.color;

      new maplibregl.Marker({ element: marker })
        .setLngLat(point.coordinates)
        .setPopup(new maplibregl.Popup({ offset: 10 }).setText(point.name))
        .addTo(mapRef.current!);
    });

    return () => {
      mapRef.current?.remove();
      mapRef.current = null;
    };
  }, [points]);

  return <div ref={containerRef} style={{ width: "100%", height: 360, borderRadius: 24, overflow: "hidden" }} />;
}

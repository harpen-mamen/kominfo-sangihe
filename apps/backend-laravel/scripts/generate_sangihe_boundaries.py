import json
import sys
from collections import defaultdict
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / ".cache" / "pydeps"))

from shapely.geometry import mapping, shape  # type: ignore
from shapely.ops import unary_union  # type: ignore


def build_desa_feature(feature):
    properties = feature.get("properties", {})

    return {
        "type": "Feature",
        "properties": {
            "name": properties.get("village"),
            "code": properties.get("village_code"),
            "district": properties.get("district"),
            "district_code": properties.get("district_code"),
            "regency": properties.get("regency"),
            "regency_code": properties.get("regency_code"),
            "province": properties.get("province"),
            "province_code": properties.get("province_code"),
            "source": properties.get("source"),
            "valid_on": properties.get("valid_on"),
        },
        "geometry": feature.get("geometry"),
    }


def normalize_source_features(source_path: Path):
    source = json.loads(source_path.read_text(encoding="utf-8"))
    return [build_desa_feature(feature) for feature in source.get("features", [])]


def dissolve_kecamatan(features):
    grouped = defaultdict(list)

    for feature in features:
        props = feature["properties"]
        grouped[(props["district_code"], props["district"])].append(shape(feature["geometry"]))

    dissolved = []

    for (district_code, district_name), geometries in sorted(grouped.items(), key=lambda item: item[0][1]):
        merged = unary_union(geometries)
        sample = next(feature for feature in features if feature["properties"]["district_code"] == district_code)
        props = sample["properties"]
        dissolved.append(
            {
                "type": "Feature",
                "properties": {
                    "name": district_name,
                    "code": district_code,
                    "regency": props.get("regency"),
                    "regency_code": props.get("regency_code"),
                    "province": props.get("province"),
                    "province_code": props.get("province_code"),
                    "source": props.get("source"),
                    "valid_on": props.get("valid_on"),
                    "villages_count": len(geometries),
                },
                "geometry": mapping(merged),
            }
        )

    return dissolved


def write_feature_collection(path: Path, features):
    path.parent.mkdir(parents=True, exist_ok=True)
    payload = {"type": "FeatureCollection", "features": features}
    path.write_text(
        json.dumps(payload, ensure_ascii=False, separators=(",", ":")),
        encoding="utf-8",
    )


def main():
    root = Path(__file__).resolve().parents[1]
    source_path = root / "storage" / "app" / "sangihe-kecamatan.geojson"
    desa_output = root / "database" / "data" / "maps" / "sangihe-desa.geojson"
    kecamatan_output = root / "database" / "data" / "maps" / "sangihe-kecamatan.geojson"

    features = normalize_source_features(source_path)
    write_feature_collection(desa_output, features)
    write_feature_collection(kecamatan_output, dissolve_kecamatan(features))

    print(f"desa_features={len(features)}")
    print(f"kecamatan_features={len(dissolve_kecamatan(features))}")
    print(str(desa_output))
    print(str(kecamatan_output))


if __name__ == "__main__":
    main()

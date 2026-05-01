import { MapPageView } from "@/components/public/map-page-view";
import { getHeroData, getMapPageData } from "@/lib/api";

export default async function DigitalMapPage() {
  const [data, hero] = await Promise.all([getMapPageData(), getHeroData()]);

  return <MapPageView hero={hero} {...data} />;
}

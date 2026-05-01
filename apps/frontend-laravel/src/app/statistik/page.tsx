import { StatisticsPageView } from "@/components/public/statistics-page-view";
import { getHeroData, getStatisticsPageData } from "@/lib/api";

export default async function StatisticsPage() {
  const [data, hero] = await Promise.all([getStatisticsPageData(), getHeroData()]);

  return <StatisticsPageView hero={hero} {...data} />;
}

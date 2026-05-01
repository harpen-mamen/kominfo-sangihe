import { HomePageView } from "@/components/public/home-page-view";
import { getHomePageData } from "@/lib/api";

export default async function HomePage() {
  const data = await getHomePageData();

  return <HomePageView {...data} />;
}


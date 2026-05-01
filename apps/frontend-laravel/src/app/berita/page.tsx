import { NewsPageView } from "@/components/public/news-page-view";
import { getNewsPageData } from "@/lib/api";

export default async function NewsPage() {
  const news = await getNewsPageData();

  return <NewsPageView news={news} />;
}


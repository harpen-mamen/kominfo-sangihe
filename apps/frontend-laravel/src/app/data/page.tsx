import { OpenDataPageView } from "@/components/public/open-data-page-view";
import { getOpenDataPageData } from "@/lib/api";

export default async function OpenDataPage() {
  const data = await getOpenDataPageData();

  return <OpenDataPageView {...data} />;
}


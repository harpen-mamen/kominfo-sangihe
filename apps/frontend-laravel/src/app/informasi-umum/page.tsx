import { InfoPageView } from "@/components/public/info-page-view";
import { getPageContent } from "@/lib/api";
import { pageSlugs } from "@/lib/portal-data";

export default async function PublicInfoPage() {
  const page = await getPageContent(pageSlugs.publicInfo, "Informasi Umum Layanan Data");

  return <InfoPageView content={page.content} title={page.title} />;
}


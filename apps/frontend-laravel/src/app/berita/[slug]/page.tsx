import { NewsDetailView } from "@/components/public/news-detail-view";
import { getNewsDetailPageData } from "@/lib/api";

type NewsDetailPageProps = {
  params: Promise<{ slug: string }>;
};

export default async function NewsDetailPage({ params }: NewsDetailPageProps) {
  const { slug } = await params;
  const detail = await getNewsDetailPageData(slug);

  return <NewsDetailView {...detail} />;
}

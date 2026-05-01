import { SearchPageView } from "@/components/public/search-page-view";
import { getSearchPageData } from "@/lib/api";

type SearchPageProps = {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
};

export default async function SearchPage({ searchParams }: SearchPageProps) {
  const params = await searchParams;
  const query = typeof params.q === "string" ? params.q : "";
  const data = await getSearchPageData(query);

  return <SearchPageView {...data} />;
}

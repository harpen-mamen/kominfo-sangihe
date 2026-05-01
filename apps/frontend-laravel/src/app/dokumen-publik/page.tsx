import { DocumentsPageView } from "@/components/public/documents-page-view";
import { getDocumentsPageData } from "@/lib/api";

export default async function DocumentsPage() {
  const documents = await getDocumentsPageData();

  return <DocumentsPageView documents={documents} />;
}


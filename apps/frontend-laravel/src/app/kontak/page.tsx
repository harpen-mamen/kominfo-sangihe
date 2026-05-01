import { ContactPageView } from "@/components/public/contact-page-view";
import { getContactPageData } from "@/lib/api";

export default async function ContactPage() {
  const page = await getContactPageData();

  return <ContactPageView content={page.content} title={page.title} />;
}


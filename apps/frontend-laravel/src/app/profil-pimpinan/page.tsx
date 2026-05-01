import { ProfilePageView } from "@/components/public/profile-page-view";
import { getLeadershipPageData } from "@/lib/api";

export default async function LeadershipProfilePage() {
  const { hero, page } = await getLeadershipPageData();

  return (
    <ProfilePageView
      content={page.content}
      description={{
        id: "Profil Bupati dan Wakil Bupati dengan tampilan resmi, bersih, dan terhubung dengan konfigurasi hero portal.",
        en: "Leadership profile page connected to the portal hero configuration with a formal and clean presentation.",
      }}
      hero={hero}
      mode="leadership"
      title={{ id: page.title, en: page.title }}
    />
  );
}


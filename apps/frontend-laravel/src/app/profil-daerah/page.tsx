import { ProfilePageView } from "@/components/public/profile-page-view";
import { getHeroData, getPageContent } from "@/lib/api";
import { pageSlugs } from "@/lib/portal-data";

export default async function RegencyProfilePage() {
  const [page, hero] = await Promise.all([
    getPageContent(pageSlugs.profileRegion, "Profil Kabupaten Kepulauan Sangihe"),
    getHeroData(),
  ]);

  return (
    <ProfilePageView
      content={page.content}
      hero={hero}
      description={{
        id: "Profil premium daerah: sejarah, geografis, visi misi, statistik singkat, dan karakter wilayah maritim.",
        en: "Premium regional profile: history, geography, vision, concise statistics, and maritime territorial character.",
      }}
      imageUrl={page.imageUrl}
      mode="region"
      title={{ id: page.title, en: page.title }}
    />
  );
}

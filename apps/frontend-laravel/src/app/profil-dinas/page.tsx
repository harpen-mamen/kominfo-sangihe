import { ProfilePageView } from "@/components/public/profile-page-view";
import { getDepartmentsPageData, getPageContent } from "@/lib/api";
import { pageSlugs } from "@/lib/portal-data";

export default async function DepartmentProfilePage() {
  const [page, departments] = await Promise.all([
    getPageContent(pageSlugs.profileDepartment, "Profil Dinas Komunikasi dan Informatika"),
    getDepartmentsPageData(),
  ]);

  return (
    <ProfilePageView
      content={page.content}
      departments={departments}
      description={{
        id: "Profil dinas, layanan, dan daftar OPD yang terhubung dalam platform data sektoral kabupaten.",
        en: "Department profile, services, and the list of agencies connected to the regency sectoral data platform.",
      }}
      mode="department"
      title={{ id: page.title, en: page.title }}
    />
  );
}


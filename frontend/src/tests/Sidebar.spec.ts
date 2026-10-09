import { mount } from "@vue/test-utils";
import { describe, expect, it, vi, beforeEach } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import Sidebar from "@/components/shared/Sidebar.vue";
import NavLink from "@/components/shared/NavLink.vue";
import CompanySwitcher from "@/components/shared/CompanySwitcher.vue";
import { useCompanyStore } from "@/stores/company.store";

// Mock router
vi.mock("@/router", () => ({
  useRouter: () => ({
    currentPath: "/dashboard",
    navigateTo: vi.fn(),
  }),
  routes: [
    { path: "/", label: "Home", layout: "app" },
    { path: "/dashboard", label: "Dashboard", layout: "app", group: "Main" },
  ],
}));

describe("Sidebar Layout Components", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("Sidebar renders correctly with routes", () => {
    const wrapper = mount(Sidebar, {
      global: {
        stubs: { NavLink: true, CompanySwitcher: true, Menu: true, X: true },
      },
    });

    expect(wrapper.find("aside").exists()).toBe(true);
    expect(wrapper.text()).toContain("LedgerScope");
  });

  it("NavLink calculates active state based on custom router", () => {
    const wrapper = mount(NavLink, {
      props: { href: "/dashboard" },
    });
    
    expect(wrapper.classes()).toContain("active");
  });

  it("CompanySwitcher switches company via pinia store", async () => {
    const companyStore = useCompanyStore();
    companyStore.companies = [{ id: 1, name: "Acme Corp" }, { id: 2, name: "Globex" }] as any;
    companyStore.activeCompanyId = 1;
    
    // Spy on switchCompany action
    const switchSpy = vi.spyOn(companyStore, 'switchCompany');

    const wrapper = mount(CompanySwitcher);
    
    const select = wrapper.find("select");
    
    // Simulate user selecting id 2
    await select.setValue("2");
    
    expect(switchSpy).toHaveBeenCalledWith(2);
  });
});

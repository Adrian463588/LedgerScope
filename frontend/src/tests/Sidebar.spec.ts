import { mount } from "@vue/test-utils";
import { describe, expect, it, vi, beforeEach } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import Sidebar from "@/components/shared/Sidebar.vue";
import NavLink from "@/components/shared/NavLink.vue";
import CompanySwitcher from "@/components/shared/CompanySwitcher.vue";
import { useCompanyStore } from "@/stores/company.store";
import type { Company } from "@/types";

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

describe("Sidebar Layout Components (BDD / SDD Specification)", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("Given desktop layout, Sidebar renders brand and navigation routes", () => {
    const wrapper = mount(Sidebar, {
      global: {
        stubs: { NavLink: true, CompanySwitcher: true, Menu: true, X: true },
      },
    });

    expect(wrapper.find("aside").exists()).toBe(true);
    expect(wrapper.text()).toContain("LedgerScope");
  });

  it("Given mobile view, clicking mobile menu toggle opens drawer and backdrop", async () => {
    const wrapper = mount(Sidebar, {
      global: {
        stubs: { NavLink: true, CompanySwitcher: true, Menu: true, X: true },
      },
    });

    const toggleBtn = wrapper.find("button[aria-label='Toggle Sidebar']");
    expect(toggleBtn.exists()).toBe(true);

    // Initial state: backdrop absent
    expect(wrapper.find(".fixed.inset-0.z-40").exists()).toBe(false);

    // When clicked
    await toggleBtn.trigger("click");

    // Then backdrop becomes visible and sidebar slides in
    const backdrop = wrapper.find(".fixed.inset-0.z-40");
    expect(backdrop.exists()).toBe(true);

    // When backdrop clicked, drawer closes
    await backdrop.trigger("click");
    expect(wrapper.find(".fixed.inset-0.z-40").exists()).toBe(false);
  });

  it("Given route matching current path, NavLink applies active class", () => {
    const wrapper = mount(NavLink, {
      props: { href: "/dashboard" },
    });

    expect(wrapper.classes()).toContain("active");
  });

  it("Given companies list, CompanySwitcher changes active tenant via pinia store", async () => {
    const companyStore = useCompanyStore();
    const mockCompanies: Company[] = [
      {
        id: 1,
        name: "Acme Corp",
        legal_name: "Acme Corporation Inc",
        industry: "Technology",
        status: "active",
      },
      {
        id: 2,
        name: "Globex",
        legal_name: "Globex International Corp",
        industry: "Finance",
        status: "active",
      },
    ];
    companyStore.companies = mockCompanies;
    companyStore.activeCompanyId = 1;

    const switchSpy = vi.spyOn(companyStore, "switchCompany");
    const wrapper = mount(CompanySwitcher);
    const select = wrapper.find("select");

    await select.setValue("2");

    expect(switchSpy).toHaveBeenCalledWith(2);
  });
});

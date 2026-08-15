import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";

import AppButton from "@/components/ui/AppButton.vue";
import AppTable from "@/components/ui/AppTable.vue";
import AmountDisplay from "@/components/ui/AmountDisplay.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";

describe("design system components", () => {
  it("renders button loading state", () => {
    const wrapper = mount(AppButton, {
      props: { loading: true },
      slots: { default: "Save" },
    });

    expect(wrapper.attributes("data-loading")).toBe("true");
    expect(wrapper.text()).toContain("Save");
  });

  it("renders status badge tone", () => {
    const wrapper = mount(StatusBadge, { props: { status: "posted" } });

    expect(wrapper.classes()).toContain("status-badge--success");
  });

  it("renders amount display with accounting dash for zero", () => {
    const wrapper = mount(AmountDisplay, { props: { value: "0.00" } });

    expect(wrapper.text()).toBe("-");
  });

  it("renders table skeleton and empty states", () => {
    const columns = [{ key: "name", label: "Name" }];
    expect(
      mount(AppTable, { props: { columns, data: [], loading: true } })
        .find(".skeleton")
        .exists(),
    ).toBe(true);
    expect(
      mount(AppTable, {
        props: { columns, data: [], emptyText: "Nothing here" },
      }).text(),
    ).toContain("Nothing here");
  });
});

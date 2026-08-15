import { describe, expect, it } from "vitest";

import { useCurrency } from "@/composables/useCurrency";

describe("useCurrency", () => {
  it("formats IDR amounts without floating precision leaks", () => {
    const currency = useCurrency();

    expect(currency.formatCurrency("1234567.00")).toBe("IDR 1.234.567,00");
    expect(currency.formatAmount("0.00")).toBe("-");
  });

  it("parses user input to decimal string", () => {
    const currency = useCurrency();

    expect(currency.parseCurrency("1.234.567,89")).toBe("1234567.89");
  });

  it("returns semantic amount color classes", () => {
    const currency = useCurrency();

    expect(currency.amountColorClass("10.00", "debit")).toBe("amount-debit");
    expect(currency.amountColorClass("10.00", "credit")).toBe("amount-credit");
    expect(currency.amountColorClass("0.00", "debit")).toBe("amount-zero");
  });
});

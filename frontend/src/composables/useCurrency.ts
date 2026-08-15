import {
  compareDecimals,
  formatDecimal,
  isZeroDecimal,
  normalizeDecimal,
} from "@/utils/decimal";

export function useCurrency() {
  function formatAmount(value: string | number): string {
    return isZeroDecimal(value) ? "-" : formatDecimal(value).replace(/^-/, "");
  }

  function formatCurrency(value: string | number, currency = "IDR"): string {
    const amount = formatAmount(value);
    return amount === "-" ? "-" : `${currency} ${amount}`;
  }

  function parseCurrency(value: string): string {
    return normalizeDecimal(value);
  }

  function amountColorClass(value: string, type: "debit" | "credit"): string {
    if (isZeroDecimal(value)) {
      return "amount-zero";
    }

    return type === "debit" ? "amount-debit" : "amount-credit";
  }

  return {
    formatCurrency,
    formatAmount,
    parseCurrency,
    amountColorClass,
    compareDecimals,
  };
}

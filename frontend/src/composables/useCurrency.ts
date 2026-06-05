export function useCurrency() {
  const numberFormat = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  function toNumber(value: string | number): number {
    if (typeof value === 'number') {
      return Number.isFinite(value) ? value : 0;
    }

    const trimmed = value.trim();
    const normalized = trimmed.includes(',') ? trimmed.replace(/\./g, '').replace(/[^\d,-]/g, '').replace(',', '.') : trimmed.replace(/[^\d.-]/g, '');
    const parsed = Number.parseFloat(normalized);
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function formatAmount(value: string | number): string {
    const amount = toNumber(value);
    return amount === 0 ? '-' : numberFormat.format(Math.abs(amount));
  }

  function formatCurrency(value: string | number, currency = 'IDR'): string {
    const amount = formatAmount(value);
    return amount === '-' ? '-' : `${currency} ${amount}`;
  }

  function parseCurrency(value: string): string {
    const normalized = value.includes(',') ? value.replace(/\./g, '').replace(/[^\d,-]/g, '').replace(',', '.') : value.replace(/[^\d.-]/g, '');
    const parsed = Number.parseFloat(normalized);
    return Number.isFinite(parsed) ? parsed.toFixed(2) : '0.00';
  }

  function amountColorClass(value: string, type: 'debit' | 'credit'): string {
    const amount = toNumber(value);
    if (amount === 0) {
      return 'amount-zero';
    }

    return type === 'debit' ? 'amount-debit' : 'amount-credit';
  }

  return { formatCurrency, formatAmount, parseCurrency, amountColorClass };
}

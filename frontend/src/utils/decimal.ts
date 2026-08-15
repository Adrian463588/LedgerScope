const DECIMAL_SEPARATOR = /[.,]/;

function clean(value: string | number): string {
  const raw = String(value)
    .trim()
    .replace(/[^\d,.-]/g, "");
  const lastComma = raw.lastIndexOf(",");
  const lastDot = raw.lastIndexOf(".");

  if (lastComma > -1 && lastDot > -1) {
    return lastComma > lastDot
      ? raw.replace(/\./g, "").replace(",", ".")
      : raw.replace(/,/g, "");
  }

  return raw.replace(",", ".");
}

function minorUnits(value: string | number, scale = 2): bigint {
  const normalized = clean(value);
  if (!normalized || !DECIMAL_SEPARATOR.test(normalized)) {
    return BigInt(normalized || "0") * 10n ** BigInt(scale);
  }

  const negative = normalized.startsWith("-");
  const unsigned = normalized.replace(/^[+-]/, "");
  const [whole = "0", fraction = ""] = unsigned.split(".");
  const padded = fraction.padEnd(scale, "0").slice(0, scale);
  const units =
    BigInt(whole || "0") * 10n ** BigInt(scale) + BigInt(padded || "0");

  return negative ? -units : units;
}

export function normalizeDecimal(value: string | number, scale = 2): string {
  const units = minorUnits(value, scale);
  const divisor = 10n ** BigInt(scale);
  const negative = units < 0n;
  const absolute = negative ? -units : units;
  const whole = absolute / divisor;
  const fraction = absolute % divisor;

  return `${negative ? "-" : ""}${whole.toString()}.${fraction
    .toString()
    .padStart(scale, "0")}`;
}

export function addDecimals(values: Array<string | number>, scale = 2): string {
  const total = values.reduce(
    (sum, value) => sum + minorUnits(value, scale),
    0n,
  );
  return normalizeDecimal(total.toString(), scale);
}

export function multiplyDecimal(
  value: string | number,
  multiplier: string | number,
  scale = 2,
): string {
  const product = minorUnits(value, scale) * minorUnits(multiplier, scale);
  const divisor = 10n ** BigInt(scale);

  return normalizeDecimal((product / divisor).toString(), scale);
}

export function compareDecimals(
  left: string | number,
  right: string | number,
  scale = 2,
): number {
  const leftUnits = minorUnits(left, scale);
  const rightUnits = minorUnits(right, scale);
  return leftUnits === rightUnits ? 0 : leftUnits > rightUnits ? 1 : -1;
}

export function formatDecimal(value: string | number, scale = 2): string {
  const normalized = normalizeDecimal(value, scale);
  const negative = normalized.startsWith("-");
  const unsigned = normalized.replace(/^-/, "");
  const [whole = "0", fraction = ""] = unsigned.split(".");
  const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

  return `${negative ? "-" : ""}${grouped},${fraction}`;
}

export function isZeroDecimal(value: string | number, scale = 2): boolean {
  return compareDecimals(value, "0", scale) === 0;
}

/** Convert decimal amounts to relative CSS bar heights without float arithmetic. */
export function decimalChartHeights(values: string[], scale = 2): number[] {
  const units = values.map((value) => minorUnits(value, scale));
  const maximum = units.reduce(
    (max, value) =>
      (value < 0n ? -value : value) > max ? (value < 0n ? -value : value) : max,
    0n,
  );

  if (maximum === 0n) return values.map(() => 10);

  return units.map((value) => {
    const absolute = value < 0n ? -value : value;
    const percentage = Number((absolute * 100n) / maximum);

    return Math.max(10, percentage);
  });
}

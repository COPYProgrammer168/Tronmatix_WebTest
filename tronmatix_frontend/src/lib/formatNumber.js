// src/lib/formatNumber.js

/**
 * Compact number formatting for large values:
 *   100,000   → "100K"
 *   1,000,000 → "1.0M"
 *   1,500,000 → "1.5M"
 *   999       → "999"
 *   < 1000     → plain number
 *
 * Optionally prefixes a currency symbol: formatCompact(100000, '$') → "$100K"
 */
export function formatCompact(value, prefix = '') {
  const n = Number(value ?? 0)
  if (!isFinite(n)) return prefix + '0'
  if (Math.abs(n) >= 1_000_000) {
    return prefix + (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M'
  }
  if (Math.abs(n) >= 1_000) {
    return prefix + (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'K'
  }
  return prefix + n.toLocaleString('en-US', { maximumFractionDigits: 0 })
}

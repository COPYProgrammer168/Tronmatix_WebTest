// Returns true if price is unknown symbol ($, $$, $$$)
export const isSymbolPrice = (p) => !p || p == 0 || /^\$+$/.test(String(p));

// Returns numeric float or null
export const numericPrice = (p) =>
  isSymbolPrice(p) ? null : parseFloat(String(p).replace(/[$,]/g, ""));

// Returns display string
export const displayPrice = (p) => {
  if (!p || p == 0) return "$";
  if (/^\$+$/.test(String(p))) return String(p); // $, $$, $$$
  const n = parseFloat(String(p).replace(/[$,]/g, ""));
  return isNaN(n) ? "$" : `$${n.toFixed(2)}`;
};

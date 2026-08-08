import { useState } from "react";
import { Link } from "react-router-dom";
import { useCart } from "../context/CartContext";
import { useFavorites } from "../context/FavoritesContext";
import { useDiscount } from "../context/DiscountContext";
import { useTheme } from "../context/ThemeContext";
import { useLang } from "../context/LanguageContext";
import { resolveImage } from "../lib/resolveImage";
import { isSymbolPrice, numericPrice, displayPrice } from "../hooks/priceUtils";

function PlaceholderImg({ name, dark }) {
  return (
    <div
      className="h-32 w-full flex flex-col items-center justify-center"
      style={{ color: dark ? "#4b5563" : "#d1d5db" }}
    >
      <svg
        className="w-10 h-10 mb-1"
        fill="none"
        stroke="currentColor"
        strokeWidth={1.5}
        viewBox="0 0 24 24"
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 19.5h18M3 4.5h18M12 9.75a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"
        />
      </svg>
      <span
        style={{
          fontSize: isKhmer ? 10 : 11,
          textAlign: "center",
          padding: "0 8px",
          lineHeight: 1.3,
        }}
      >
        {name}
      </span>
    </div>
  );
}

function PriceTagIcon({ size = 12 }) {
  return (
    <svg
      className="flex-shrink-0"
      style={{ width: size, height: size }}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={2.5}
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z" />
      <line x1="7" x2="7.01" y1="7" y2="7" />
    </svg>
  );
}

function AddToCartBtn({
  onAdd,
  dark,
  cardHovered,
  btnFont = "Rajdhani, sans-serif",
  isKhmer = false,
}) {
  const [state, setState] = useState("idle"); // idle | adding | added

  const handleClick = () => {
    if (state !== "idle") return;
    setState("adding");
    setTimeout(() => {
      onAdd();
      setState("added");
      setTimeout(() => setState("idle"), 1800);
    }, 220);
  };

  const isAdded = state === "added";
  const isAdding = state === "adding";

  const bgColor = isAdded
    ? "#22c55e"
    : isAdding
      ? "#F97316"
      : cardHovered // whole-card hover drives the orange
        ? "#F97316"
        : "#111827";

  return (
    <button
      onClick={handleClick}
      disabled={state !== "idle"}
      className="product-card-cta mt-auto w-full font-extrabold rounded transition-all duration-200"
      style={{
        fontFamily: `${btnFont} !important`,
        fontWeight: 700,
        fontSize: 16,
        letterSpacing: 1,
        height: 42,
        background: bgColor,
        color: "#fff",
        border: "none",
        transform: isAdding ? "scale(0.97)" : "scale(1)",
        boxShadow: isAdded
          ? "0 0 0 3px rgba(34,197,94,0.3)"
          : cardHovered && !isAdding
            ? "0 4px 14px rgba(249,115,22,0.4)"
            : "none",
        cursor: state !== "idle" ? "not-allowed" : "pointer",
      }}
    >
      <span className="flex items-center justify-center gap-2 w-full h-full">
        {isAdded ? (
          <>
            <svg
              className="w-4 h-4"
              fill="none"
              stroke="currentColor"
              strokeWidth={2.5}
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M5 13l4 4L19 7"
              />
            </svg>
            <span>{isKhmer ? "បានបន្ថែម!" : "ADDED!"}</span>
          </>
        ) : isAdding ? (
          <>
            <svg
              className="w-4 h-4 animate-spin"
              fill="none"
              stroke="currentColor"
              strokeWidth={2}
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            <span>{isKhmer ? "កំពុងបន្ថែម..." : "ADDING..."}</span>
          </>
        ) : (
          <>
            <svg
              className="w-4 h-4"
              fill="none"
              stroke="currentColor"
              strokeWidth={2}
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <span>{isKhmer ? "បន្ថែមទៅកន្ត្រក" : "ADD TO CART"}</span>
          </>
        )}
      </span>
    </button>
  );
}

export default function ProductCard({ product }) {
  const { addItem } = useCart();
  const { toggleFavorite, isFavorite } = useFavorites();
  const { getItemDiscounts, bestDiscountForItem } = useDiscount();
  const { dark } = useTheme();
  const { isKhmer } = useLang();
  const btnFont = isKhmer
    ? "Kdam Thmor Pro, Rajdhani, sans-serif"
    : "Rajdhani, sans-serif";
  const [hovered, setHovered] = useState(false);

  const fav = isFavorite(product.id);
  const images = Array.isArray(product.images) ? product.images : [];
  const image1Path = images[0] ?? product.image ?? null;
  const image2Path = images[1] ?? null;
  const imageUrl = resolveImage(image1Path);
  const image2Url = resolveImage(image2Path);
  const hasHoverImg = image2Url && image2Url !== imageUrl;

  // All discounts that apply to this product (code + public/auto)
  const itemDiscounts = getItemDiscounts(product);
  const isDiscounted = itemDiscounts.length > 0;
  const bestDiscount = bestDiscountForItem(product); // best single discount for price calc

  const numPrice = numericPrice(product.price);
  const discountedPrice =
    bestDiscount && numPrice
      ? Math.max(
        0,
        numPrice -
        (bestDiscount.type === "percentage"
          ? (numPrice * bestDiscount.value) / 100
          : Math.min(bestDiscount.value, numPrice)),
      )
      : null;

  // Product badge (set from admin dashboard)
  const badge = product.badge ?? null;

  const cardBg = dark ? "#1f2937" : "#fff";
  const border = dark ? "#374151" : "#e5e7eb";
  const imgBg = dark ? "#111827" : "#f9fafb";
  const text = dark ? "#f9fafb" : "#1f2937";
  const favBg = dark ? "#1f2937" : "#fff";

  // Detect "Ask Price" state — NULL/0/"$$$" price (not listed) or out of stock
  const isAskPrice = isSymbolPrice(product.price) || (product.stock ?? 99) <= 0;
  const telegramLink = `https://t.me/KJ_Jen?text=${encodeURIComponent("Hello, I would like to ask about the price of: " + product.name)}`;

  return (
    <div
      className="product-card rounded-lg overflow-hidden transition-shadow relative group flex flex-col h-full"
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      style={{
        border: isDiscounted
          ? "1px solid rgba(249,115,22,0.45)"
          : `1px solid ${border}`,
        background: cardBg,
        isolation: "isolate",
        boxShadow: hovered
          ? isDiscounted
            ? "0 8px 30px rgba(249,115,22,0.18)"
            : "0 8px 30px rgba(0,0,0,0.12)"
          : isDiscounted
            ? "0 2px 10px rgba(249,115,22,0.08)"
            : "0 1px 4px rgba(0,0,0,0.06)",
        transition: "box-shadow 0.2s ease, border-color 0.2s ease",
      }}
    >
      {/* Fav button */}
      <button
        onClick={() => toggleFavorite(product)}
        className="absolute top-2 right-2 z-10 w-8 h-8 flex items-center justify-center rounded-full shadow-md transition-transform hover:scale-110"
        style={{ background: favBg }}
        title={fav ? "Remove from bookmarks" : "Add to bookmarks"}
      >
        <svg
          className="w-5 h-5"
          fill={fav ? "#F97316" : "none"}
          stroke={fav ? "#F97316" : "#9ca3af"}
          strokeWidth={2}
          viewBox="0 0 24 24"
        >
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
      </button>

      {/* ── Top-left badges ── */}
      <div className="absolute top-2 left-2 z-10 flex flex-col gap-1">
        {/* Admin product badge — ticket tag */}
        {badge && (
          <div
            className="flex items-center gap-1 font-black shadow-lg"
            style={{
              position: "relative",
              fontSize: 11,
              letterSpacing: 0.5,
              padding: "4px 10px 4px 18px",
              background: badge.bg || "#F97316",
              border: badge.border ? `1.5px solid ${badge.border}` : "none",
              color: badge.color || "#fff",
              boxShadow: "0 2px 8px rgba(0,0,0,0.25)",
              clipPath: "polygon(12px 0%, 100% 0%, 100% 100%, 12px 100%, 0% 50%)",
            }}
          >
            <span
              style={{
                position: "absolute",
                left: 6,
                top: "50%",
                transform: "translateY(-50%)",
                width: 4,
                height: 4,
                borderRadius: "50%",
                background: dark ? "#1f2937" : "#fff",
                opacity: 0.9,
                zIndex: 1,
              }}
            />
            {badge.icon || <PriceTagIcon />} {badge.text}
          </div>
        )}

        {/* Featured / Hot item flags — plain rounded pills */}
        {(product.is_featured || product.is_hot) && (
          <div className="flex flex-col gap-1">
            {product.is_featured && (
              <span className="flex items-center gap-1 font-black rounded-full shadow-lg"
                style={{
                  fontSize: 10,
                  letterSpacing: 0.5,
                  padding: "2px 8px",
                  background: "#eab308",
                  border: "none",
                  color: "#fff",
                  boxShadow: "0 2px 8px rgba(0,0,0,0.25)",
                  lineHeight: 1.2,
                }}>
                FEATURED
              </span>
            )}
            {product.is_hot && (
              <span className="flex items-center gap-1 font-black rounded-full shadow-lg"
                style={{
                  fontSize: isKhmer ? 10 : 12,
                  letterSpacing: 0.5,
                  padding: "2px 8px",
                  background: "#EE8100",
                  border: "none",
                  color: "white",
                  boxShadow: "0 2px 8px rgba(0,0,0,0.25)",
                  lineHeight: 1.2,
                }}>
                HOT PRICE
              </span>
            )}
          </div>
        )}

        {/* One badge per discount that applies to this product — ticket tag */}
        {itemDiscounts.map((d, idx) => {
          const bc = d.badge_config;
          const bgStyle = bc
            ? {
              background: bc.bg || "#F97316",
              border: bc.border ? `1.5px solid ${bc.border}` : "none",
            }
            : d.source === "public"
              ? {
                background: "#7c3aed",
                border: "none",
              }
              : {
                background: "#F97316",
                border: "none",
              };
          const badgeColor = bc ? bc.color || "#F97316" : "#fff";
          const badgeIcon = bc ? bc.icon || null : null;
          const badgeText = bc
            ? bc.text
            : d.type === "percentage"
              ? `${d.value}% OFF`
              : `-$${Number(d.value).toFixed(2)}`;
          const shadowStyle = bc
            ? {}
            : d.source === "public"
              ? { boxShadow: "0 2px 8px rgba(124,58,237,0.5)" }
              : { boxShadow: "0 2px 8px rgba(249,115,22,0.5)" };

          return (
            <div key={idx}>
              <div
                className="flex items-center gap-1 font-black shadow-lg"
                style={{
                  position: "relative",
                  fontSize: 11,
                  letterSpacing: 0.5,
                  padding: "4px 10px 4px 18px",
                  color: badgeColor,
                  ...bgStyle,
                  ...shadowStyle,
                  clipPath: "polygon(12px 0%, 100% 0%, 100% 100%, 12px 100%, 0% 50%)",
                }}
              >
                <span
                  style={{
                    position: "absolute",
                    left: 6,
                    top: "50%",
                    transform: "translateY(-50%)",
                    width: 4,
                    height: 4,
                    borderRadius: "50%",
                    background: dark ? "#1f2937" : "#fff",
                    opacity: 0.9,
                    zIndex: 1,
                  }}
                />
                {badgeIcon || <PriceTagIcon />} {badgeText}
                {d.source === "code" && !bc && (
                  <span style={{ fontSize: 9, opacity: 0.8, marginLeft: 2 }}>
                    ({d.code})
                  </span>
                )}
              </div>
              {/* Savings sub-badge — only show when no custom badge text is set */}
              {/* {!bc && d.type === "percentage" && product.price && (
                <div
                  className="flex items-center gap-1 rounded-full font-bold"
                  style={{
                    fontSize: 10,
                    padding: "2px 8px",
                    background: "rgba(34,197,94,0.15)",
                    color: "#22c55e",
                    border: "1px solid rgba(34,197,94,0.35)",
                    width: "fit-content",
                  }}
                >
                  save ${((product.price * d.value) / 100).toFixed(2)}
                </div>
              )} */}
            </div>
          );
        })}
      </div>

      {/* Image — fixed aspect ratio ensures equal card height in rows */}
      <Link to={`/product/${product.slug || product.id}`} className="block">
        <div className="relative overflow-hidden" style={{ background: imgBg, aspectRatio: "1 / 1" }}>
          {imageUrl ? (
            <>
              <img
                src={imageUrl}
                alt={product.name}
                className="w-full h-full object-cover block transition-all duration-300"
                style={{
                  opacity: hovered && hasHoverImg ? 0 : 1,
                  transform:
                    hovered && hasHoverImg
                      ? "scale(1.05) translateY(-4px)"
                      : "scale(1) translateY(0)",
                }}
                onError={(e) => {
                  e.target.style.display = "none";
                }}
              />
              {hasHoverImg && (
                <img
                  src={image2Url}
                  alt={`${product.name} hover`}
                  className="absolute inset-0 w-full h-full object-cover transition-all duration-300"
                  style={{
                    opacity: hovered ? 1 : 0,
                    transform: hovered
                      ? "scale(1.05) translateY(-4px)"
                      : "scale(0.95) translateY(4px)",
                  }}
                  onError={(e) => {
                    e.target.style.display = "none";
                  }}
                />
              )}
            </>
          ) : (
            <div
              className="flex items-center justify-center w-full h-full"
              style={{ minHeight: 160 }}
            >
              <PlaceholderImg name={product.name} dark={dark} />
            </div>
          )}
        </div>
      </Link>

      <div className="p-3 text-center flex flex-col flex-1">
        <Link to={`/product/${product.slug || product.id}`} className="flex flex-col">
          {/* Fixed-height title zone (2 lines max, clamped) — keeps price/button aligned across every card */}
          <h3
            className="product-card-title font-bold mb-1 transition-colors"
            style={{
              color: hovered ? "#F97316" : text,
              letterSpacing: isKhmer ? 0 : undefined,
              fontSize: isKhmer ? 18 : 22,
              lineHeight: 1.25,
              minHeight: "2.5em",
              display: "-webkit-box",
              WebkitLineClamp: 2,
              WebkitBoxOrient: "vertical",
              overflow: "hidden",
            }}
          >
            {product.name}
          </h3>
          {product.caption && (
            <p
              className="mb-2"
              style={{
                fontSize: isKhmer ? 13 : 16,
                fontWeight: isKhmer ? 500 : 600,
                color: text,
                opacity: 0.6,
                lineHeight: 1.3,
                wordBreak: "break-word",
              }}
            >
              {product.caption}
            </p>
          )}
        </Link>

        {/* Fixed-height price block — reserves room for the tallest case (discount price + strike-through row)
            so ADD TO CART stays aligned whether or not this card has a discount */}
        <div
          className="flex flex-col items-center justify-end mb-3"
          style={{ minHeight: 20 }}
        >
          {isAskPrice ? (
            <div
              className="product-card-price font-black transition-colors"
              style={{
                fontSize: 20,
                color: "#F97316",
              }}
            >
              $$$
            </div>
          ) : discountedPrice !== null ? (
            <>
              <div
                className="product-card-price font-black transition-colors"
                style={{
                  fontSize: 20,
                  color: "#F97316",
                }}
              >
                ${discountedPrice.toFixed(2)}
              </div>
              <div className="flex items-center justify-center gap-2 flex-wrap">
                <span
                  className="line-through font-semibold"
                  style={{ fontSize: 18, color: dark ? "#6b7280" : "#9ca3af" }}
                >
                  {displayPrice(product.price)}
                </span>
                {/* <span
                  className="font-black rounded-full px-1.5 py-0.5"
                  style={{
                    fontSize: 14,
                    background: "rgba(34,197,94,0.12)",
                    color: "#22c55e",
                    border: "1px solid rgba(34,197,94,0.3)",
                  }}
                >
                  −
                  {bestDiscount.type === "percentage"
                    ? `${bestDiscount.value}%`
                    : `${Number(bestDiscount.value).toFixed(2)}`}
                </span> */}
              </div>
            </>
          ) : (
            <div
              className="product-card-price font-bold transition-colors"
              style={{
                fontSize: 20,
                color: hovered ? "#F97316" : text,
                letterSpacing: isKhmer ? 0 : undefined,
              }}
            >
              {displayPrice(product.price)}
            </div>
          )}
        </div>

        {isAskPrice ? (
          <Link
            to={`/product/${product.slug || product.id}`}
            className="product-card-cta mt-auto w-full font-bold rounded transition-all duration-200 flex items-center justify-center gap-2"
            style={{
              fontSize: 16,
              height: 42,
              background: hovered ? "#F97316" : "#111827",
              color: "#fff",
              border: "none",
              transform: hovered ? "scale(1.02)" : "scale(1)",
              boxShadow: hovered ? "0 4px 14px rgba(249,115,22,0.4)" : "none",
            }}
          >
            {isKhmer ? "មើលព័ត៌មានលម្អិត" : "VIEW DETAIL"}
          </Link>
        ) : (
          <AddToCartBtn
            onAdd={() => addItem(product)}
            dark={dark}
            cardHovered={hovered}
            btnFont={btnFont}
            isKhmer={isKhmer}
          />
        )}
      </div>
    </div>
  );
}

import { useState, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import ProductCard from "../components/ProductCard";
import { useTheme } from "../context/ThemeContext";
import axios from "../lib/axios";
import { useLang } from "../context/LanguageContext";

const LARAVEL_URL = (import.meta.env.VITE_API_URL || "").replace(/\/$/, "");

const FALLBACK_BANNERS = [
  {
    id: 1,
    title: "WHITE SET\nHIGH END PC BUILD",
    subtitle: "AMD RYZEN 9950X3D / RTX5080",
    badge: "NEW ARRIVAL",
    bg_color: "#f5f0e8",
    text_color: "#c8860a",
    image: null,
  },
  {
    id: 2,
    title: "PC BUILD BUDGET 3K\nFOR GAMING",
    subtitle: "Best price guaranteed",
    badge: "HOT DEAL",
    bg_color: "#111111",
    text_color: "#F97316",
    image: null,
  },
];

const categories = [
  "CPU",
  "RAM",
  "MAINBOARD",
  "MONITOR",
  "COOLING",
  "M2",
  "VGA",
  "POWER SUPPLY",
];

// Sub-categories that map to each header category.
// For MONITOR we send multiple `category[]` values so the backend
// can use whereIn — single `cats` string param is non-standard.
const CAT_SUBS = {
  MONITOR: [
    "MONITOR 25INCH",
    "MONITOR 27INCH",
    "MONITOR 32INCH",
    "MONITOR 34INCH",
    "MONITOR 39INCH",
    "MONITOR 42INCH",
    "MONITOR 48INCH",
    "MONITOR 49INCH",
  ],
  CPU: ["CPU"],
  RAM: ["RAM"],
  MAINBOARD: ["MAINBOARD"],
  COOLING: ["COOLING"],
  M2: ["M2"],
  VGA: ["VGA"],
  "POWER SUPPLY": ["POWER SUPPLY"],
};

export function resolveImage(path) {
  if (!path || typeof path !== "string") return null;
  const t = path.trim();
  if (!t) return null;
  if (t.startsWith("http://") || t.startsWith("https://")) return t;
  return LARAVEL_URL + (t.startsWith("/") ? t : "/" + t);
}

export default function HomePage() {
  const [slide, setSlide] = useState(0);
  const [banners, setBanners] = useState(FALLBACK_BANNERS);
  const [videos, setVideos] = useState([]);
  const [products, setProducts] = useState({}); // { CPU: { items, total, page, error } }
  const [pageLoading, setPageLoading] = useState(true);
  const [newProducts, setNewProducts] = useState([]);
  const [catPage, setCatPage] = useState({});
  const newProdRef = useRef(null);
  const catRefs = useRef({});
  const { dark } = useTheme();
  const { t, isKhmer } = useLang();

  // Font stacks that auto-switch based on language
  const headingFont = isKhmer
    ? "Kh_Jrung_Thom, Khmer OS, sans-serif"
    : "HurstBagod, Rajdhani, sans-serif";
  const bodyFont = isKhmer
    ? "Kdam Thmor Pro, Khmer OS, sans-serif"
    : "Rajdhani, sans-serif";

  const bg = dark ? "#111827" : "#fff";
  const text = dark ? "#f9fafb" : "#1f2937";
  const headerL = dark ? "#1f2937" : "#000";
  const navBtn = dark ? "#374151" : "#fff";
  const navBrd = dark ? "#4b5563" : "#d1d5db";

  // ── Banners ────────────────────────────────────────────────────────────────
  useEffect(() => {
    axios
      .get("/api/banners")
      .then((res) => {
        const data = Array.isArray(res.data)
          ? res.data
          : (res.data?.data ?? []);
        const active = data.filter((b) => b.active !== false);
        if (active.length > 0) setBanners(active);
      })
      .catch((err) => {
        console.warn(
          "[HomePage] banners fetch failed:",
          err?.response?.status,
          err?.message,
        );
        // Keep FALLBACK_BANNERS — no user-visible error needed
      });
  }, []);

  // ── Videos (separate feature from banners) ──────────────────────────────────
  useEffect(() => {
    axios
      .get("/api/videos")
      .then((res) => {
        const data = Array.isArray(res.data)
          ? res.data
          : (res.data?.data ?? []);
        setVideos(data);
      })
      .catch((err) => {
        console.warn(
          "[HomePage] videos fetch failed:",
          err?.response?.status,
          err?.message,
        );
        // No fallback needed — section simply doesn't render if empty
      });
  }, []);

  // Auto-advance banner
  useEffect(() => {
    if (banners.length <= 1) return;
    const t = setInterval(
      () => setSlide((s) => (s + 1) % banners.length),
      5000,
    );
    return () => clearInterval(t);
  }, [banners.length]);

  useEffect(() => {
    setSlide((s) => Math.min(s, Math.max(banners.length - 1, 0)));
  }, [banners.length]);

  // ── New Products ───────────────────────────────────────────────────────────
  useEffect(() => {
    axios
      .get("/api/products", {
        params: { sort: "newest", per_page: 12, page: 1 },
      })
      .then((res) => {
        const raw = res.data;
        const items = Array.isArray(raw) ? raw : (raw?.data ?? []);
        setNewProducts(items);
      })
      .catch((err) => {
        console.warn(
          "[HomePage] new products fetch failed:",
          err?.response?.status,
          err?.message,
        );
      });
  }, []);

  // ── Category fetch ─────────────────────────────────────────────────────────
  const fetchCatPage = async (cat, page) => {
    const subs = CAT_SUBS[cat] ?? [cat];

    // Build URLSearchParams so array values are properly serialized as
    // repeated keys: category[]=X&category[]=Y — Laravel reads these correctly.
    const qs = new URLSearchParams();
    if (subs.length > 1) {
      subs.forEach((s) => qs.append("category[]", s));
    } else {
      qs.append("category", subs[0]);
    }
    qs.append("per_page", 10);
    qs.append("page", page);

    try {
      const res = await axios.get(`/api/products?${qs.toString()}`);
      const raw = res.data;
      const items = Array.isArray(raw) ? raw : (raw?.data ?? []);
      const total = raw?.total ?? items.length;

      setProducts((prev) => ({
        ...prev,
        [cat]: { items, total, page, error: false },
      }));
    } catch (err) {
      const status = err?.response?.status;
      console.error(
        `[HomePage] category "${cat}" fetch failed:`,
        status,
        err?.message,
      );
      setProducts((prev) => ({
        ...prev,
        [cat]: { items: [], total: 0, page, error: true },
      }));
    }
  };

  useEffect(() => {
    const init = async () => {
      const pages = {};
      categories.forEach((cat) => {
        pages[cat] = 1;
      });
      setCatPage(pages);
      // Use allSettled so one failing category (e.g. 500 on MONITOR)
      // never blocks the rest from rendering.
      await Promise.allSettled(categories.map((cat) => fetchCatPage(cat, 1)));
      setPageLoading(false);
    };
    init();
  }, []); // eslint-disable-line

  // ── Derived banner values ──────────────────────────────────────────────────
  const b = banners[slide] ?? FALLBACK_BANNERS[0];
  const bgColor = b.bg_color || "#111";
  const txtColor = b.text_color || "#fff";
  const imgUrl = resolveImage(b.image);
  const hasVideo = b.has_video || !!b.video;
  const videoType = b.video_type;
  const videoSrc = b.video;
  const hasMedia = imgUrl || hasVideo;
  const detailLink =
    b.link ||
    (b.category ? `/category/${b.category.toLowerCase()}` : null) ||
    `/category/search?q=${encodeURIComponent((b.title || "").replace("\n", " ").split(" ").slice(0, 3).join(" "))}`;

  return (
    <div style={{ background: bg }}>
      {/* ── BANNER SLIDER ──────────────────────────────────────────────── */}
      <style>{`
  .banner-video::-webkit-media-controls,
  .banner-video::-webkit-media-controls-enclosure,
  .banner-video::-webkit-media-controls-panel,
  .banner-video::-webkit-media-controls-play-button,
  .banner-video::-webkit-media-controls-timeline,
  .banner-video::-webkit-media-controls-start-playback-button,
  .banner-video::-webkit-media-controls-overlay-play-button { display: none !important; }
  .banner-video { -webkit-appearance: none; }

  /* ── Responsive banner height ───────────────────────────────────── */
  .banner-wrap {
    aspect-ratio: 16 / 6;        /* desktop */
  }
  @media (max-width: 1440px) {
    .banner-wrap { aspect-ratio: 16 / 7; } /* macbook/large laptop */
  }
  @media (max-width: 1024px) {
    .banner-wrap { aspect-ratio: 16 / 9; } /* tablet/iPad */
  }

  @media (max-width: 640px) {
    .banner-wrap {
      aspect-ratio: unset;
      max-height: unset;
      min-height: 300px;          /* mobile */
    }
    .banner-arrow {
      width: clamp(32px, 4vw, 40px) !important;
      height: clamp(32px, 4vw, 40px) !important;
      font-size: clamp(18px, 2.5vw, 22px) !important;
    }
  }

  /* ── Dots ───────────────────────────────────────────────────────── */
  .banner-dot { height: 8px; border-radius: 9999px; transition: all 0.5s; }

  /* ── Hover zoom on banner media ────────────────────────────────── */
  .banner-media {
    transition: transform 0.6s ease;
  }
  .banner-wrap:hover .banner-media {
    transform: scale(1.05);
  }

  /* ── Fast shimmer skeleton ──────────────────────────────────────── */
  @keyframes shimmer {
    0%   { background-position: -600px 0; }
    100% { background-position:  600px 0; }
  }
  .skeleton-shimmer {
    background: linear-gradient(
      90deg,
      var(--sk-base) 25%,
      var(--sk-shine) 50%,
      var(--sk-base) 75%
    );
    background-size: 1200px 100%;
    animation: shimmer 1s linear infinite;
  }
`}</style>

      <div className="w-full mb-8">
        <div
          className="banner-wrap relative overflow-hidden"
          style={{
            background: "#000",
          }}
        >
          {banners.map((banner, index) => {
            const isActive = index === slide;
            const imgUrl = resolveImage(banner.image);
            const bgColor = banner.bg_color || "#111";
            const txtColor = banner.text_color || "#fff";
            const hasVideo = banner.has_video || !!banner.video;
            const videoSrc = banner.video;
            const hasMedia = imgUrl || hasVideo;
            const detailLink = banner.product_id
              ? `/product/${banner.product_id}`
              : banner.link ||
                (banner.category
                  ? `/category/${banner.category.toLowerCase()}`
                  : `/category/search?q=${encodeURIComponent((banner.title || "").replace("\n", " "))}`);

            return (
              <div
                key={banner.id}
                className="absolute inset-0 transition-opacity duration-700 ease-in-out"
                style={{ opacity: isActive ? 1 : 0, zIndex: isActive ? 1 : 0 }}
              >
                {/* Background Layer */}
                <div
                  className="absolute inset-0"
                  style={{ background: hasMedia ? "#000" : bgColor }}
                />

                {/* ── Media ──────────────────────────────────────────────── */}
                {hasVideo && banner.video_type === "upload" && videoSrc && (
                  <video
                    className="absolute inset-0 w-full h-full object-cover banner-video banner-media"
                    style={{ opacity: 0.4, pointerEvents: "none" }}
                    src={videoSrc}
                    autoPlay
                    muted
                    loop
                    playsInline
                  />
                )}
                {imgUrl && (
                  <img
                    src={imgUrl}
                    alt={banner.title}
                    className="absolute inset-0 w-full h-full object-cover banner-media"
                    style={{ opacity: hasVideo ? 0.25 : 0.7 }}
                  />
                )}

                {/* ── Gradient overlay (Desktop only) ─────────────────────────────────── */}
                <div
                  className="absolute inset-0 hidden lg:block"
                  style={{
                    background:
                      "linear-gradient(to right, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.55) 50%, rgba(0,0,0,0.15) 100%)",
                  }}
                />

                <Link
                  to={detailLink}
                  className="absolute inset-0 z-[5]"
                  style={{ display: "block" }}
                />

                {/* ── Banner Content (Desktop only) ──────────────────────────────────── */}
                <div className="absolute inset-0 z-10 hidden lg:flex items-center px-12 pointer-events-none">
                  <div className="max-w-2xl text-white pointer-events-auto">
                    {banner.badge && (
                      <div className="inline-block px-3 py-1 mb-3 text-xs font-black tracking-widest text-orange-500 bg-white/95 rounded-full">
                        {banner.badge}
                      </div>
                    )}
                    <h2
                      className="mb-3 font-black tracking-tighter"
                      style={{
                        fontSize: "clamp(28px, 5vw, 60px)",
                        lineHeight: 1.1,
                      }}
                    >
                      {banner.title.split("\n").map((line, i) => (
                        <div key={i}>{line}</div>
                      ))}
                    </h2>
                    <p
                      className="mb-10 font-medium opacity-90 tracking-tighter"
                      style={{ fontSize: "clamp(16px, 2vw, 24px)" }}
                    >
                      {banner.subtitle}
                    </p>
                    <Link
                      to={detailLink}
                      className="px-8 py-3 font-black text-white transition-transform bg-primary rounded-xl hover:scale-105"
                    >
                      {isKhmer
                        ? "មើលផលិតផល"
                        : t("home.viewProduct") || "VIEW PRODUCT"}
                    </Link>
                  </div>
                </div>
              </div>
            );
          })}
          {/* ── Prev / Next buttons ─────────────────────────────────────── */}
          {banners.length > 1 && (
            <>
              <button
                onClick={() =>
                  setSlide((s) => (s - 1 + banners.length) % banners.length)
                }
                className="absolute top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white flex items-center justify-center rounded-full transition-colors"
                style={{
                  zIndex: 3,
                  left: "clamp(8px,2vw,16px)",
                  width: "clamp(28px,4vw,40px)",
                  height: "clamp(28px,4vw,40px)",
                  fontSize: "clamp(16px,2.5vw,22px)",
                }}
              >
                ‹
              </button>
              <button
                onClick={() => setSlide((s) => (s + 1) % banners.length)}
                className="absolute top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white flex items-center justify-center rounded-full transition-colors"
                style={{
                  zIndex: 3,
                  right: "clamp(8px,2vw,16px)",
                  width: "clamp(28px,4vw,40px)",
                  height: "clamp(28px,4vw,40px)",
                  fontSize: "clamp(16px,2.5vw,22px)",
                }}
              >
                ›
              </button>
            </>
          )}

          {/* ── Dot indicators ─────────────────────────────────────────── */}
          {banners.length > 1 && (
            <div
              className="absolute bottom-3 flex gap-2"
              style={{ zIndex: 3, left: "50%", transform: "translateX(-50%)" }}
            >
              {banners.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setSlide(i)}
                  className={`banner-dot ${i === slide ? "bg-primary w-6" : "bg-white/50 w-2"}`}
                />
              ))}
            </div>
          )}
        </div>
      </div>

      <div className="max-w-[1280px] mx-auto px-4 pt-2 pb-2">
        {/* NEW ARRIVAL heading */}
        <div className="flex items-center gap-1 justify-center">
          <span
            className="text-primary font-black tracking-widest"
            style={{
              fontFamily: headingFont,
              fontSize: "clamp(22px, 4vw, 32px)",
              letterSpacing: isKhmer ? 0 : undefined,
            }}
          >
            {isKhmer ? t("home.newArrival") : "NEW"}
          </span>
          {!isKhmer && (
            <span
              className="font-black tracking-widest"
              style={{
                fontFamily: headingFont,
                fontSize: "clamp(22px, 4vw, 32px)",
                color: text,
              }}
            >
              ARRIVAL
            </span>
          )}
        </div>
      </div>

      {/* ── NEW PRODUCTS carousel ────────────────────────────────────────────── */}
      {newProducts.length > 0 && (
        <div className="max-w-[1280px] mx-auto px-4 mb-10">
          <div className="flex flex-wrap items-center justify-between gap-y-2 mb-5">
            <div className="flex items-center gap-2 min-w-0">
              <div className="w-1 h-8 bg-primary rounded-full flex-shrink-0" />
              <span
                className="font-black tracking-widest truncate"
                style={{
                  fontFamily: headingFont,
                  fontSize: "clamp(15px, 2vw, 20px)",
                  color: text,
                  letterSpacing: isKhmer ? 0 : undefined,
                }}
              >
                {isKhmer ? t("home.newProducts") : "NEW PRODUCTS"}
              </span>
              <span
                className="inline-flex items-center justify-center font-bold px-2 rounded-full flex-shrink-0"
                style={{
                  fontFamily: bodyFont,
                  background: "rgba(249,115,22,0.12)",
                  color: "#F97316",
                  border: "1px solid rgba(249,115,22,0.3)",
                  fontSize: 11,
                  height: 26,
                  lineHeight: 1,
                  whiteSpace: "nowrap",
                }}
              >
                {isKhmer ? "ទើបបន្ថែមថ្មីៗ" : "just Added"}
              </span>
            </div>
            <div className="flex items-center gap-2 flex-shrink-0">
              <div className="hidden sm:flex gap-2">
                {["‹", "›"].map((a, i) => (
                  <button
                    key={i}
                    onClick={() =>
                      newProdRef.current?.scrollBy({
                        left: i === 0 ? -300 : 300,
                        behavior: "smooth",
                      })
                    }
                    className="w-8 h-8 flex items-center justify-center font-bold hover:border-primary hover:text-primary transition-colors rounded"
                    style={{
                      border: `1px solid ${navBrd}`,
                      color: text,
                      background: navBtn,
                      fontSize: 16,
                    }}
                  >
                    {a}
                  </button>
                ))}
              </div>
              <Link
                to="/search?sort=newest"
                className="text-primary font-bold hover:underline whitespace-nowrap"
                style={{
                  fontFamily: bodyFont,
                  fontSize: "clamp(12px, 1.5vw, 15px)",
                }}
              >
                {isKhmer ? "មើលផលិតផលថ្មី" : "View All New Product"} →
              </Link>
            </div>
          </div>
          <div
            ref={newProdRef}
            className="new-prod-scroll flex gap-4 overflow-x-auto pb-2"
            style={{ scrollbarWidth: "none", msOverflowStyle: "none" }}
          >
            <style>{`.new-prod-scroll::-webkit-scrollbar{display:none}`}</style>
            {newProducts.map((p, i) => (
              <div
                key={p.id || i}
                style={{ minWidth: 200, maxWidth: 200, flexShrink: 0 }}
              >
                <ProductCard product={p} />
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ── CATEGORY ROWS ────────────────────────────────────────────────────── */}
      {categories.map((cat) => {
        const catData = products[cat];
        const catItems = catData?.items ?? [];
        const hasError = catData?.error === true;
        const isLoading = pageLoading || catData === undefined;
        const catSlug = cat.toLowerCase().replace(/ /g, "-");
        const scrollId = "cat-" + cat.replace(/ /g, "-");

        return (
          <div key={cat} className="max-w-[1280px] mx-auto px-4 mb-10">
            {/* Row header */}
            <div className="flex items-center mb-4">
              <div
                className="flex-1 h-12 rounded-l"
                style={{ background: headerL }}
              />
              <div className="hidden lg:flex gap-2 ml-4 mr-2">
                {["‹", "›"].map((a, i) => (
                  <button
                    key={i}
                    onClick={() =>
                      catRefs.current[cat]?.scrollBy({
                        left: i === 0 ? -500 : 500,
                        behavior: "smooth",
                      })
                    }
                    className="w-8 h-8 flex items-center justify-center font-bold rounded transition-colors bg-primary text-white hover:bg-orange-600"
                    style={{ fontSize: 16 }}
                  >
                    {a}
                  </button>
                ))}
              </div>
              <Link
                to={`/category/${catSlug}`}
                className="bg-primary text-white font-bold px-10 py-3 hover:bg-orange-600 transition-colors"
                style={{
                  fontFamily: headingFont,
                  fontSize: 18,
                  letterSpacing: isKhmer ? 0 : 2,
                  clipPath:
                    "polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0% 100%)",
                }}
              >
                {cat}
              </Link>
            </div>

            <style>{`
              .${scrollId}::-webkit-scrollbar { height: 4px; }
              .${scrollId}::-webkit-scrollbar-track { background: rgba(249,115,22,0.10); border-radius: 2px; }
              .${scrollId}::-webkit-scrollbar-thumb { background: #F97316; border-radius: 2px; }
            `}</style>

            {/* Error state */}
            {hasError && (
              <div
                className="py-6 text-center"
                style={{ color: "#ef4444", fontSize: 13 }}
              >
                ⚠️ Failed to load {cat} products.{" "}
                <button
                  className="underline"
                  onClick={() => fetchCatPage(cat, 1)}
                >
                  Retry
                </button>
              </div>
            )}

            {/* Empty state */}
            {!isLoading && !hasError && catItems.length === 0 && (
              <div
                className="py-8 text-center"
                style={{ color: dark ? "#6b7280" : "#9ca3af" }}
              >
                <div style={{ fontSize: 28, marginBottom: 4 }}>📦</div>
                <div style={{ fontSize: 13, fontWeight: 600 }}>
                  No {cat} products yet
                </div>
              </div>
            )}

            {/* Product rows */}
            {(isLoading || catItems.length > 0) && (
              <>
                {/* Desktop: single-row horizontal scroll */}
                <div
                  ref={(el) => {
                    catRefs.current[cat] = el;
                  }}
                  className={`hidden lg:flex gap-4 overflow-x-auto pb-3 ${scrollId}`}
                  style={{
                    scrollbarWidth: "thin",
                    scrollbarColor: "#F97316 rgba(249,115,22,0.10)",
                  }}
                >
                  {isLoading
                    ? Array(6)
                        .fill(null)
                        .map((_, i) => (
                          <div
                            key={i}
                            className="rounded-xl skeleton-shimmer flex-shrink-0"
                            style={{
                              width: 210,
                              height: 300,
                              "--sk-base": dark ? "#1f2937" : "#f3f4f6",
                              "--sk-shine": dark ? "#374151" : "#e9eaec",
                            }}
                          />
                        ))
                    : catItems.map((p, i) => (
                        <div
                          key={p.id || i}
                          style={{
                            minWidth: 210,
                            maxWidth: 210,
                            flexShrink: 0,
                          }}
                        >
                          <ProductCard product={p} />
                        </div>
                      ))}
                </div>

                {/* Mobile: 2-row grid horizontal scroll */}
                <div
                  className={`lg:hidden overflow-x-auto pb-2 ${scrollId}`}
                  style={{
                    scrollbarWidth: "thin",
                    scrollbarColor: "#F97316 rgba(249,115,22,0.10)",
                    WebkitOverflowScrolling: "touch",
                  }}
                >
                  <div
                    style={{
                      display: "grid",
                      gridTemplateRows: "repeat(2, auto)",
                      gridAutoFlow: "column",
                      gridAutoColumns: "200px",
                      gap: "8px",
                      width: "max-content",
                    }}
                  >
                    {isLoading
                      ? Array(8)
                          .fill(null)
                          .map((_, i) => (
                            <div
                              key={i}
                              className="rounded-xl skeleton-shimmer"
                              style={{
                                width: 200,
                                height: 220,
                                "--sk-base": dark ? "#1f2937" : "#f3f4f6",
                                "--sk-shine": dark ? "#374151" : "#e9eaec",
                              }}
                            />
                          ))
                      : catItems.map((p, i) => (
                          <div key={p.id || i} style={{ width: 200 }}>
                            <ProductCard product={p} />
                          </div>
                        ))}
                  </div>
                </div>
              </>
            )}

            <div className="flex justify-end mt-3">
              <Link
                to={`/category/${catSlug}`}
                className="text-primary font-bold hover:underline"
                style={{ fontFamily: bodyFont, fontSize: 15 }}
              >
                {isKhmer ? "មើលទាំងអស់នៃ" : "View All"} {cat} →
              </Link>
            </div>
          </div>
        );
      })}
      <VideoSection videos={videos} isKhmer={isKhmer} dark={dark} />
      <TelegramBanner isKhmer={isKhmer} />
    </div>
  );
}

// ── Video Showcase Section (separate feature from banners) ─────────────────
function getYouTubeId(url) {
  if (!url) return null;
  const m = url.match(
    /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/,
  );
  return m ? m[1] : null;
}

function getTikTokId(url) {
  if (!url) return null;
  const m = url.match(/\/(?:video|photo)\/(\d+)/);
  return m ? m[1] : null;
}

function loadScriptOnce(src, id) {
  if (document.getElementById(id)) return;
  const js = document.createElement("script");
  js.id = id;
  js.async = true;
  js.src = src;
  document.body.appendChild(js);
}

// Facebook's SDK throws "init not called with valid version" if XFBML.parse()
// runs before FB.init() — just loading sdk.js isn't enough. fbAsyncInit is
// the SDK's own ready-callback, called exactly once when it finishes loading,
// so this guarantees init happens before any card tries to parse its embed.
let fbInitPromise = null;
function loadFacebookSdk() {
  if (window.FB) return Promise.resolve();
  if (fbInitPromise) return fbInitPromise;

  fbInitPromise = new Promise((resolve) => {
    window.fbAsyncInit = function () {
      window.FB.init({
        xfbml: false, // we call XFBML.parse() ourselves per-card instead
        version: "v21.0",
      });
      resolve();
    };
    loadScriptOnce(
      "https://connect.facebook.net/en_US/sdk.js",
      "fb-jssdk-video-section",
    );
  });

  return fbInitPromise;
}

// Platform-specific display config — aspect ratio + badge label/color.
// TikTok and Facebook Reels are portrait (9:16); YouTube/upload/normal FB
// video are landscape (16:9). Forcing all into one ratio is what squishes
// or stretches the embed, so each type gets its own.
const VIDEO_TYPE_CONFIG = {
  tiktok: { label: "TikTok", color: "#fe2c55", ratio: "9 / 16" },
  facebook_reel: { label: "Reel", color: "#0866ff", ratio: "9 / 16" },
  facebook: { label: "Facebook", color: "#0866ff", ratio: "16 / 9" },
  youtube: { label: "YouTube", color: "#ff0000", ratio: "16 / 9" },
  upload: { label: "Video", color: "#6b7280", ratio: "16 / 9" },
};

function getVideoTypeConfig(video) {
  // Facebook Reels share video_type "facebook" but the URL contains /reel/
  if (video.video_type === "facebook" && video.video?.includes("/reel/")) {
    return VIDEO_TYPE_CONFIG.facebook_reel;
  }
  return VIDEO_TYPE_CONFIG[video.video_type] || VIDEO_TYPE_CONFIG.upload;
}

function getOriginalVideoUrl(video) {
  if (video.video_type === "youtube") {
    const id = getYouTubeId(video.video);
    return id ? `https://www.youtube.com/watch?v=${id}` : video.video;
  }
  // facebook, tiktok, and upload links are already the direct/original URL
  return video.video;
}

// YouTube exposes a public, no-key-needed thumbnail URL for every video ID.
// hqdefault always exists; maxresdefault is higher quality but missing for
// some older/low-res videos, so hqdefault is the safe default.
function getYouTubeThumbnail(video) {
  if (video.video_type !== "youtube") return null;
  const id = getYouTubeId(video.video);
  return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null;
}

function VideoCard({ video, isKhmer, dark }) {
  const [playing, setPlaying] = useState(false);
  const [embedReady, setEmbedReady] = useState(false);
  const wrapRef = useRef(null);

  const card = dark ? "#1f2937" : "#f3f4f6";
  const text = dark ? "#f9fafb" : "#1f2937";
  const sub = dark ? "#9ca3af" : "#6b7280";

  // Priority: CMS-uploaded thumbnail (any type) → YouTube's own public
  // thumbnail (auto-derived from the video ID, no upload needed) → null,
  // which then falls through to the upload-frame poster or text placeholder.
  const thumb = resolveImage(video.thumbnail) || getYouTubeThumbnail(video);
  const wrapped = video.product_id
    ? (children) => <Link to={`/product/${video.product_id}`}>{children}</Link>
    : null;

  const typeConfig = getVideoTypeConfig(video);
  const isPortrait = typeConfig.ratio === "9 / 16";
  const originalUrl = getOriginalVideoUrl(video);

  // Upload/YouTube render instantly via native <video>/<iframe>, so they
  // never need the loading overlay. Facebook/TikTok rely on an external
  // script that takes a moment, so cover the raw skeleton until it fires.
  const needsEmbedScript =
    video.video_type === "facebook" || video.video_type === "tiktok";

  // No thumbnail at all (CMS or auto-derived) → use the video's own first
  // frame as the poster instead of a blank/placeholder card. Only
  // meaningful for "upload" since Facebook/TikTok don't expose a frame
  // grab without their own API (YouTube is already covered above).
  const showVideoFramePoster = !thumb && video.video_type === "upload";

  // Load the right embed SDK only when this card actually plays (FB/TikTok)
  useEffect(() => {
    if (!playing) return;
    if (!needsEmbedScript) return;
    setEmbedReady(false);
    if (video.video_type === "facebook") {
      let cancelled = false;
      loadFacebookSdk().then(() => {
        if (cancelled) return;
        if (wrapRef.current) window.FB.XFBML.parse(wrapRef.current);
        setEmbedReady(true);
      });
      return () => {
        cancelled = true;
      };
    }
    if (video.video_type === "tiktok") {
      loadScriptOnce(
        "https://www.tiktok.com/embed.js",
        "tiktok-embed-js-section",
      );
      // TikTok's script renders async with no onload callback we can hook
      // into reliably, so give it a moment before revealing the embed.
      const t = setTimeout(() => setEmbedReady(true), 800);
      return () => clearTimeout(t);
    }
  }, [playing, video.video_type, needsEmbedScript]);

  const renderPlayer = () => {
    if (video.video_type === "upload") {
      return (
        <video
          src={video.video}
          poster={thumb || undefined}
          className="w-full h-full object-cover"
          controls
          autoPlay
          loop
          playsInline
        />
      );
    }
    if (video.video_type === "youtube") {
      const id = getYouTubeId(video.video);
      if (!id) return null;
      return (
        <iframe
          className="w-full h-full"
          src={`https://www.youtube.com/embed/${id}?autoplay=1&loop=1&playlist=${id}`}
          title={video.title}
          frameBorder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
        />
      );
    }
    if (video.video_type === "facebook") {
      if (video.video?.includes("/reel/")) {
        return (
          <div className="w-full h-full flex flex-col items-center justify-center text-center px-6 text-white">
            <p className="font-bold mb-1">Video unavailable</p>
            <a
              href={video.video}
              target="_blank"
              rel="noopener noreferrer"
              className="underline text-sm opacity-80"
            >
              Watch on Facebook
            </a>
          </div>
        );
      }
      return (
        <div
          className="fb-video w-full"
          data-href={video.video}
          data-width="auto"
          data-show-text="false"
        />
      );
    }
    if (video.video_type === "tiktok") {
      const id = getTikTokId(video.video);
      if (!id) {
        return (
          <div className="w-full h-full flex flex-col items-center justify-center text-center px-6 text-white">
            <p className="font-bold mb-1">Video unavailable</p>
            <a
              href={video.video}
              target="_blank"
              rel="noopener noreferrer"
              className="underline text-sm opacity-80"
            >
              Watch on TikTok
            </a>
          </div>
        );
      }
      return (
        <blockquote
          className="tiktok-embed"
          cite={video.video}
          data-video-id={id}
          style={{ maxWidth: "100%", minWidth: 200, margin: 0 }}
        >
          <section />
        </blockquote>
      );
    }
    return (
      <div className="w-full h-full flex items-center justify-center text-white">
        <a
          href={originalUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="underline text-sm opacity-80"
        >
          Watch video
        </a>
      </div>
    );
  };

  const thumbBlock = (
    <div
      className="relative w-full rounded-2xl overflow-hidden cursor-pointer group"
      style={{ background: "#000", aspectRatio: typeConfig.ratio }}
      onClick={() => setPlaying(true)}
    >
      <span
        className="absolute top-2 left-2 z-10 px-2 py-0.5 rounded-full text-[11px] font-bold text-white"
        style={{ background: typeConfig.color }}
      >
        {typeConfig.label}
      </span>
      {thumb ? (
        <img
          src={thumb}
          alt={video.title}
          className="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-95 transition-opacity"
        />
      ) : showVideoFramePoster ? (
        // No thumbnail in CMS — render the video itself (paused on first
        // frame) so its own frame becomes the poster image.
        <video
          src={video.video}
          className="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-95 transition-opacity"
          muted
          playsInline
          preload="metadata"
        />
      ) : (
        <div
          className="absolute inset-0 flex items-center justify-center"
          style={{ background: card }}
        >
          <span style={{ color: sub, fontSize: 13 }}>{video.title}</span>
        </div>
      )}
      <div className="absolute inset-0 flex items-center justify-center">
        <div className="w-16 h-16 rounded-full bg-primary/90 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
            <path d="M8 5v14l11-7z" />
          </svg>
        </div>
      </div>
    </div>
  );

  const playerBlock = (
    <div
      ref={wrapRef}
      className="relative w-full rounded-2xl overflow-hidden"
      style={{ background: "#000", aspectRatio: typeConfig.ratio }}
    >
      {renderPlayer()}
      {needsEmbedScript && !embedReady && (
        <div
          className="absolute inset-0 flex items-center justify-center"
          style={{ background: "#000" }}
        >
          <div
            className="w-8 h-8 rounded-full border-2 border-white/30"
            style={{
              borderTopColor: "#fff",
              animation: "videoSpin 0.8s linear infinite",
            }}
          />
        </div>
      )}
      <style>{`@keyframes videoSpin { to { transform: rotate(360deg) } }`}</style>
    </div>
  );

  return (
    <div
      style={{
        minWidth: isPortrait ? 160 : 420,
        maxWidth: isPortrait ? 190 : 560,
      }}
      className="flex-shrink-0"
    >
      {playing ? playerBlock : thumbBlock}
      <div className="mt-3">
        {wrapped ? (
          wrapped(
            <h3
              className="font-bold hover:text-primary transition-colors"
              style={{ color: text, fontSize: 15 }}
            >
              {video.title}
            </h3>,
          )
        ) : (
          <h3 className="font-bold" style={{ color: text, fontSize: 15 }}>
            {video.title}
          </h3>
        )}
        {video.description && (
          <p style={{ color: sub, fontSize: 13, marginTop: 2 }}>
            {video.description}
          </p>
        )}
      </div>
    </div>
  );
}

// One scrollable row of same-orientation video cards, with its own
// left/right scroll buttons.
function VideoRow({ videos, label, isKhmer, dark }) {
  const scrollRef = useRef(null);
  if (!videos || videos.length === 0) return null;

  const text = dark ? "#f9fafb" : "#1f2937";

  return (
    <div className="mb-8">
      <div className="flex items-center justify-between mb-3">
        <h3
          className="text-base md:text-lg font-bold opacity-70"
          style={{ color: text }}
        >
          {label}
        </h3>
        {videos.length > 1 && (
          <div className="hidden lg:flex gap-1">
            {["‹", "›"].map((a, i) => (
              <button
                key={i}
                onClick={() =>
                  scrollRef.current?.scrollBy({
                    left: i === 0 ? -400 : 400,
                    behavior: "smooth",
                  })
                }
                className="w-8 h-8 flex items-center justify-center font-bold rounded-full transition-colors bg-primary text-white hover:bg-orange-600"
              >
                {a}
              </button>
            ))}
          </div>
        )}
      </div>

      <div
        ref={scrollRef}
        className="flex gap-5 overflow-x-auto pb-2"
        style={{ scrollbarWidth: "thin" }}
      >
        {videos.map((v) => (
          <VideoCard key={v.id} video={v} isKhmer={isKhmer} dark={dark} />
        ))}
      </div>
    </div>
  );
}

function VideoSection({ videos, isKhmer, dark }) {
  if (!videos || videos.length === 0) return null;

  const text = dark ? "#f9fafb" : "#1f2937";

  // Group by orientation so portrait (TikTok/Reels) and landscape
  // (YouTube/Facebook/Upload) never share the same row — mixing them
  // is what made the landscape cards look small next to tall ones.
  const portraitVideos = videos.filter(
    (v) => getVideoTypeConfig(v).ratio === "9 / 16",
  );
  const landscapeVideos = videos.filter(
    (v) => getVideoTypeConfig(v).ratio !== "9 / 16",
  );

  return (
    <div className="my-12 mx-4 lg:mx-auto max-w-7xl">
      <h2
        className="text-2xl md:text-3xl font-black mb-5"
        style={{ color: text }}
      >
        {isKhmer ? "វីដេអូ" : "VIDEOS"}
      </h2>

      <VideoRow
        videos={landscapeVideos}
        label={isKhmer ? "វីដេអូផ្ដេក" : "Videos"}
        isKhmer={isKhmer}
        dark={dark}
      />
      <VideoRow
        videos={portraitVideos}
        label={
          isKhmer ? "វីដេអូបញ្ឈរ (TikTok / Reels)" : "Shorts (TikTok / Reels)"
        }
        isKhmer={isKhmer}
        dark={dark}
      />
    </div>
  );
}

// ── Join Telegram Banner ──────────────────────────────────────────────────
function TelegramBanner({ isKhmer }) {
  return (
    <div
      className="my-12 mx-4 lg:mx-auto max-w-5xl rounded-3xl overflow-hidden relative"
      style={{
        background: "linear-gradient(135deg, #0088cc 0%, #005588 100%)",
      }}
    >
      <div
        className="absolute inset-0 opacity-10"
        style={{
          backgroundImage:
            "radial-gradient(circle at 2px 2px, white 1px, transparent 0)",
          backgroundSize: "24px 24px",
        }}
      ></div>
      <div className="relative z-10 flex flex-col md:flex-row items-center justify-between p-8 md:p-12 gap-6 text-white">
        <div className="flex items-center gap-6">
          <div className="hidden md:block">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="white">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.07-.19-.04-.27-.02-.12.08-2 1.28-5.65 3.74-.53.36-1.01.54-1.44.53-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.41-.88.03-.25.37-.51 1.03-.78 4.04-1.76 6.74-2.93 8.09-3.5 3.84-1.6 4.63-1.88 5.16-1.89.11 0 .37.02.54.16.14.12.18.28.2.45-.01.07-.01.17-.03.27z" />
            </svg>
          </div>
          <div className="text-center md:text-left">
            <h3
              className="text-2xl md:text-3xl font-black mb-2"
              style={{ fontFamily: "Kdam Thmor Pro, sans-serif" }}
            >
              {isKhmer
                ? "តាមដាន TronmatixComputer លើ Telegram"
                : "Follow TronmatixComputer on Telegram"}
            </h3>
            <p
              className="opacity-90"
              style={{ fontFamily: "Kdam Thmor Pro, sans-serif" }}
            >
              {isKhmer
                ? "ទទួលបានព័ត៌មានចុងក្រោយ និងការផ្តល់ជូនពិសេស!"
                : "Get the latest news and special offers!"}
            </p>
          </div>
        </div>
        <a
          href="https://t.me/+VZScFi_U95PsFk0M"
          target="_blank"
          rel="noopener noreferrer"
          className="px-8 py-4 bg-white text-blue-600 font-bold rounded-2xl shadow-lg hover:scale-105 transition-transform flex items-center gap-2"
          style={{ fontFamily: "Kdam Thmor Pro, sans-serif" }}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.07-.19-.04-.27-.02-.12.08-2 1.28-5.65 3.74-.53.36-1.01.54-1.44.53-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.41-.88.03-.25.37-.51 1.03-.78 4.04-1.76 6.74-2.93 8.09-3.5 3.84-1.6 4.63-1.88 5.16-1.89.11 0 .37.02.54.16.14.12.18.28.2.45-.01.07-.01.17-.03.27z" />
          </svg>
          {isKhmer ? "ចូលគ្រុបឥឡូវនេះ" : "JOIN NOW"}
        </a>
      </div>
    </div>
  );
}

import { useEffect, useState, useMemo } from "react";
import { useTheme } from "../context/ThemeContext";
import api from "../lib/axios";

const LARAVEL_URL = (import.meta.env.VITE_API_URL || "").replace(/\/$/, "");

function resolveImage(path) {
  if (!path || typeof path !== "string") return null;
  const t = path.trim();
  if (!t) return null;
  if (t.startsWith("http://") || t.startsWith("https://")) return t;
  return LARAVEL_URL + (t.startsWith("/") ? t : "/" + t);
}

export default function BrandMarquee() {
  const { dark } = useTheme();
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get("/api/brands")
      .then((res) => {
        const data = Array.isArray(res.data)
          ? res.data
          : (res.data?.data ?? []);
        setBrands(data);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  // Duplicate the list so the scroll loops seamlessly.
  const track = useMemo(() => {
    if (brands.length === 0) return [];
    return [...brands, ...brands];
  }, [brands]);

  const bg = dark ? "#111827" : "#f8fafc";
  const border = dark ? "rgba(255,255,255,0.06)" : "rgba(15,23,42,0.08)";
  const textSub = dark ? "#9ca3af" : "#6b7280";

  if (loading) {
    return (
      <div
        className="w-full"
        style={{
          background: bg,
          borderTop: `1px solid ${border}`,
          borderBottom: `1px solid ${border}`,
          padding: "18px 0",
        }}
      >
        <div className="flex justify-center gap-6">
          {Array.from({ length: 8 }).map((_, i) => (
            <div
              key={i}
              className="rounded-lg"
              style={{
                width: 90,
                height: 36,
                background: dark ? "#1f2937" : "#e5e7eb",
                opacity: 0.5,
              }}
            />
          ))}
        </div>
      </div>
    );
  }

  if (brands.length === 0) return null;

  return (
    <div
      className="w-full"
      style={{
        background: bg,
        borderTop: `1px solid ${border}`,
        borderBottom: `1px solid ${border}`,
        overflow: "hidden",
        padding: "14px 0",
        cursor: "default",
      }}
    >
      <style>{`
        @keyframes brandScroll {
          0%   { transform: translateX(0); }
          100% { transform: translateX(-50%); }
        }
        .brand-track {
          display: flex;
          gap: 32px;
          width: max-content;
          animation: brandScroll var(--scroll-duration, 35s) linear infinite;
        }
        .brand-track:hover {
          animation-play-state: paused;
        }
        .brand-item {
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          height: 42px;
          padding: 0 6px;
          opacity: 0.7;
          transition: opacity 0.2s;
          text-decoration: none;
        }
        .brand-item:hover {
          opacity: 1;
        }
        .brand-item img {
          max-height: 38px;
          max-width: 96px;
          object-fit: contain;
          filter: ${dark
            ? "brightness(0) invert(1)"
            : "brightness(0)"};
          transition: filter 0.2s;
        }
        .brand-item:hover img {
          filter: ${dark
            ? "brightness(0) invert(1) drop-shadow(0 0 4px rgba(249,115,22,0.5))"
            : "brightness(0) drop-shadow(0 0 4px rgba(249,115,22,0.4))"};
        }
        .brand-name-fallback {
          font-family: 'Rajdhani', 'Kdam Thmor Pro', sans-serif;
          font-size: 14px;
          font-weight: 700;
          letter-spacing: 1.5px;
          color: ${textSub};
          white-space: nowrap;
        }
        @media (max-width: 640px) {
          .brand-track { gap: 20px; }
          .brand-item img { max-height: 28px; max-width: 70px; }
          .brand-name-fallback { font-size: 12px; }
        }
      `}</style>

      <div className="brand-track" style={{ "--scroll-duration": `${Math.max(25, brands.length * 3)}s` }}>
        {track.map((brand, idx) => {
          const imgUrl = resolveImage(brand.image);
          const href = `/category/search?q=${encodeURIComponent(brand.name)}`;

          return (
            <a
              key={`${brand.id}-${idx}`}
              href={href}
              className="brand-item"
              title={brand.name}
              onClick={(e) => e.preventDefault()}
            >
              {imgUrl ? (
                <img
                  src={imgUrl}
                  alt={brand.name}
                  loading="lazy"
                  onError={(e) => {
                    e.target.style.display = "none";
                    if (e.target.nextSibling) e.target.nextSibling.style.display = "block";
                  }}
                />
              ) : null}
              <span
                className="brand-name-fallback"
                style={{ display: imgUrl ? "none" : "block" }}
              >
                {brand.name}
              </span>
            </a>
          );
        })}
      </div>
    </div>
  );
}

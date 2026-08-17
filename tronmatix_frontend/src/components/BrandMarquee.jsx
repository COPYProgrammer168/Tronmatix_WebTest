import { useEffect, useState, useMemo, useRef, useCallback } from "react";
import { Link } from "react-router-dom";
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

  // Clear the swipe auto-resume timer if the component unmounts.
  useEffect(() => {
    return () => {
      if (resumeTimerRef.current) clearTimeout(resumeTimerRef.current);
    };
  }, []);

  // ── Manual drag / swipe ──────────────────────────────────────────────────
  // Dependency-free pointer drag on top of the CSS auto-scroll. The CSS
  // keyframe animation owns `transform`, so while dragging we kill it
  // (style.animation = "none") and drive the transform manually. Wrapping is
  // modulo half the track width so the duplicated-half illusion never shows a
  // seam. After a real drag the strip stays paused until the pointer leaves
  // the marquee (user preference — no auto-resume timer).
  const trackRef = useRef(null);
  const dragState = useRef({ active: false, startX: 0, baseX: 0, moved: false, pointerId: null, pointerType: "" });
  const resumeTimerRef = useRef(null);

  const wrapX = useCallback((x) => {
    const el = trackRef.current;
    if (!el) return x;
    const halfPx = Math.max(el.scrollWidth / 2, 1);
    while (x > 0) x -= halfPx;
    while (x < -halfPx) x += halfPx;
    return x;
  }, []);

  const onPointerDown = useCallback((e) => {
    const el = trackRef.current;
    if (!el || dragState.current.active) return;
    // Ignore non-primary buttons (e.g. right-click).
    if (e.button !== undefined && e.button !== 0) return;

    const style = getComputedStyle(el);
    const m = style.transform.match(/matrix\(([^)]*)\)/);
    const baseX = m ? parseFloat(m[1].split(",")[4] || "0") : 0;

    dragState.current = {
      active: true,
      startX: e.clientX,
      baseX,
      moved: false,
      pointerId: e.pointerId,
      pointerType: e.pointerType || "mouse",
    };
    // NOTE: no setPointerCapture here on purpose. Grabbing capture during
    // pointerdown and releasing it during pointerup suppresses the compatible
    // `click` event in desktop browsers — the brand <Link> would never
    // navigate. Capture is only taken once the drag threshold is crossed
    // (see onPointerMove), so plain clicks work like normal links.
  }, []);

  const onPointerMove = useCallback((e) => {
    const d = dragState.current;
    const el = trackRef.current;
    if (!d.active || !el) return;

    const dx = e.clientX - d.startX;
    if (!d.moved && Math.abs(dx) > 5) {
      d.moved = true;
      // A real drag starts: pause the animation AND kill the keyframes —
      // a paused keyframe animation would still override the inline
      // transform, so it must be removed while dragging. Only now take
      // pointer capture so the drag keeps tracking outside the strip.
      try { el.setPointerCapture(e.pointerId); } catch (_) {}
      el.style.animation = "none";
      el.classList.add("dragging");
    }
    if (!d.moved) return;

    el.style.transform = `translateX(${wrapX(d.baseX + dx)}px)`;
  }, [wrapX]);

  const endDrag = useCallback(() => {
    const d = dragState.current;
    const el = trackRef.current;
    if (!d.active) return;
    d.active = false;

    if (d.moved) {
      try { el.releasePointerCapture(d.pointerId); } catch (_) {}

      // Real drag: keep the manual position and stay paused. `moved` stays
      // set on purpose — the `click` event fires AFTER pointerup, so
      // onTrackClick can still see it and suppress the brand-link navigation.
      el.classList.remove("dragging");
      el.classList.add("drag-paused");

      if (d.pointerType === "touch") {
        // Touch has no hover-out, so auto-resume a couple of seconds after
        // the swipe ends unless the user grabs the strip again.
        if (resumeTimerRef.current) clearTimeout(resumeTimerRef.current);
        resumeTimerRef.current = setTimeout(() => {
          if (!trackRef.current) return;
          trackRef.current.classList.remove("drag-paused");
          trackRef.current.style.transform = "";
          trackRef.current.style.animation = "";
        }, 2000);
      }
    } else {
      // Plain click (no drag): release nothing, `moved` stays false, and the
      // natural click on the <Link> proceeds — desktop navigation works.
    }
  }, []);

  const onPointerUp = useCallback((e) => {
    endDrag();
  }, [endDrag]);

  const onPointerCancel = useCallback((e) => {
    endDrag();
  }, [endDrag]);

  const resumeScroll = useCallback(() => {
    const el = trackRef.current;
    if (!el) return;
    if (resumeTimerRef.current) { clearTimeout(resumeTimerRef.current); resumeTimerRef.current = null; }
    if (el.classList.contains("drag-paused")) {
      // Resume the auto-scroll. Removing the inline transform + animation
      // hands control back to the keyframes (we wrapped into a duplicated
      // region, so the jump is invisible).
      el.classList.remove("drag-paused");
      el.style.transform = "";
      el.style.animation = "";
    }
  }, []);

  const onPointerLeave = useCallback(() => {
    resumeScroll();
  }, [resumeScroll]);

  const onTrackClick = useCallback((e) => {
    // Suppress the click right after a drag so the brand link doesn't
    // navigate (pointerup comes before click, so `moved` is still set).
    if (dragState.current.moved) {
      e.preventDefault();
      e.stopPropagation();
      dragState.current.moved = false;
    }
  }, []);

  // Duplicate the list so the scroll loops seamlessly.
  // If the brands are still too few to fill a wide viewport (each item is
  // ~150px wide), keep doubling — the loop still needs 2× the single-half
  // width to translate -50% without showing empty space. The width must be
  // recomputed AFTER every doubling, otherwise the loop stops on a stale
  // width and the seam of the -50% loop shows a gap.
  const track = useMemo(() => {
    if (brands.length === 0) return [];
    let half = [...brands];
    const halfWidth = () => half.reduce((sum, b) => sum + (b.name.length + 4) * 8 + 12, 0);
    while (halfWidth() * 2 < (typeof window !== "undefined" ? window.innerWidth : 0)) {
      half = [...half, ...half];
    }

    return [...half, ...half];
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
          /* Trailing padding equals the gap so the two duplicated halves are
             pixel-identical → the -50% translateX loop is perfectly seamless. */
          padding-right: 32px;
          width: max-content;
          will-change: transform;
          /* Longhand animation props — the animation shorthand with a var()
             inside silently fails to animate on some Safari / older Android
             browsers, leaving the logos static. Longhands are bulletproof. */
          animation-name: brandScroll;
          animation-duration: var(--scroll-duration, 35s);
          animation-timing-function: linear;
          animation-iteration-count: infinite;
          animation-play-state: running;
          /* Manual drag / swipe: grab cursor, allow vertical page scroll only
             (horizontal drags go to the marquee), no text selection. */
          touch-action: pan-y;
          user-select: none;
          -webkit-user-select: none;
          cursor: grab;
        }
        .brand-track:hover {
          animation-play-state: paused;
        }
        .brand-track.dragging {
          cursor: grabbing;
        }
        /* After a real drag the strip stays paused until the pointer leaves
           the marquee — removed from JS on pointerleave. */
        .brand-track.drag-paused {
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
          .brand-track { gap: 20px; padding-right: 20px; }
          .brand-item img { max-height: 28px; max-width: 70px; }
          .brand-name-fallback { font-size: 12px; }
        }
      `}</style>

      <div
        ref={trackRef}
        className="brand-track"
        style={{ "--scroll-duration": `${Math.max(25, brands.length * 3)}s` }}
        onPointerDown={onPointerDown}
        onPointerMove={onPointerMove}
        onPointerUp={onPointerUp}
        onPointerCancel={onPointerCancel}
        onPointerLeave={onPointerLeave}
        onClick={onTrackClick}
      >
        {track.map((brand, idx) => {
          const imgUrl = resolveImage(brand.image);
          // Navigate to the category page filtered by this brand. CategoryPage
          // reads ?brand= and filters /api/products by it, with a fallback that
          // treats the brand as an exact category (Table/Chair brands like
          // SECRETLAB are stored in the product category column).
          const href = `/category/all?brand=${encodeURIComponent(brand.name)}`;

          return (
            <Link
              key={`${brand.id}-${idx}`}
              to={href}
              className="brand-item"
              title={`${brand.name} — view products`}
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
            </Link>
          );
        })}
      </div>
    </div>
  );
}

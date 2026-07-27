import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import logo from "../assets/logo.png";
import { useTheme } from "../context/ThemeContext";
import { useLang } from "../context/LanguageContext";
import { useCart } from "../context/CartContext";

const socials = [
  {
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
      </svg>
    ),
    href: "https://www.facebook.com/TronmatixComputer",
    brand: "#1877f2",
  },
  {
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
      </svg>
    ),
    href: "https://t.me/+VZScFi_U95PsFk0M",
    brand: "#0088cc",
  },
  {
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z" />
      </svg>
    ),
    href: "https://www.tiktok.com/@tronmatixcomputer",
    brand: "#fe2c55",
  },
];

/* ── Phone icon ───────────────────────────────────────────────────────── */
function PhoneIcon({ className, ...props }) {
  return (
    <svg
      width="14"
      height="14"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={2.5}
      strokeLinecap="round"
      strokeLinejoin="round"
      className={className}
      {...props}
    >
      <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
    </svg>
  );
}

/* ── Scroll-to-top button ────────────────────────────────────────────── */
function ScrollToTopBtn() {
  const { cartOpen } = useCart();
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY > 400);
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const show = visible && !cartOpen;

  return (
    <button
      onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
      aria-label="Scroll to top"
      className={`fixed bottom-6 right-[80px] z-[300] w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 active:scale-90 ${
        show
          ? "opacity-100 translate-y-0"
          : "opacity-0 translate-y-4 pointer-events-none"
      }`}
      style={{ background: "#F97316" }}
    >
      <svg
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="white"
        strokeWidth={3}
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <path d="M18 15l-6-6-6 6" />
      </svg>
    </button>
  );
}

/* ── Footer ──────────────────────────────────────────────────────────── */
export default function Footer() {
  const { dark } = useTheme();
  const { isKhmer } = useLang();

  const bg = dark ? "#0f172a" : "#f3f4f6";
  const border = dark ? "#1f2937" : "#e5e7eb";
  const heading = dark ? "#e67e23" : "#e67e23";
  const text = dark ? "#ffffff" : "#000000";
  const bottomBg = dark ? "#0a0f1a" : "#e9eaec";
  const cardBg = dark ? "#1e293b" : "#ffffff";
  const bodyFont = isKhmer
    ? "Kdam_Thmor_Pro, sans-serif"
    : "Rajdhani, sans-serif";
  const headFont = isKhmer
    ? "Kh_Jrung_Thom, sans-serif"
    : "HurstBagod, sans-serif";

  /* font size helper — headings */
  const fs = isKhmer ? 20 : 22;
  /* body text */
  const fb = isKhmer ? 18 : 20;
  /* menu/contact list items */
  const fl = isKhmer ? 18 : 20;

  return (
    <footer
      style={{
        background: bg,
        borderTop: `1px solid ${border}`,
        marginTop: 48,
        fontFamily: headFont,
      }}
    >
      {/* Scoped hover styles */}
      <style>{`
        .f-hover-link { transition: color 0.2s; }
        .f-hover-link:hover { color: #F97316 !important; }
        .f-hover-phone { transition: color 0.2s; }
        .f-hover-phone:hover { color: #F97316 !important; }
        .f-hover-phone:hover .f-phone-icon { color: #F97316 !important; }

        .f-social-btn {
          transition: all 0.2s;
          border-width: 1px;
        }
      `}</style>

      <ScrollToTopBtn />

      <div className="max-w-[1280px] mx-auto px-4 py-5">
        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
          {/* ─── Col 1: LOGO ─── */}
          <div className="flex items-start justify-center sm:justify-start sm:pt-2">
            <Link to="/">
              <img
                src={logo}
                alt="Tronmatix"
                className="h-24 md:h-28 object-contain hover:scale-105 transition-transform duration-300"
              />
            </Link>
          </div>

          {/* ─── Col 2: ADDRESS ─── */}
          <div>
            <div
              className="font-bold mb-2"
              style={{ fontSize: fs, color: heading, fontFamily: bodyFont }}
            >
              {isKhmer ? "អាសយដ្ឋាន" : "ADDRESS"}
            </div>
            <Link
              to="/contact"
              target="_blank"
              rel="noopener noreferrer"
              className="block f-hover-link"
              style={{
                fontSize: fb,
                color: text,
                lineHeight: 1.6,
                fontWeight: 600,
                fontFamily: bodyFont,
              }}
            >
              {isKhmer
                ? "ផ្លូវលេខ: ១៦២ | ផ្ទះលេខ: ២៣២ | កែងផ្លូវ: ២៣៧ | ផ្សារដេប៉ូ១ | ទួលគោក"
                : "Street 162, House 232, Corner 237, Phsar Depo 1, Toul Kork"}
            </Link>
          </div>

          {/* <div>
            <div
              className="rounded-lg overflow-hidden mt-3"
              style={{ width: "100%", height: "90px" }}
            >
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3908.8449116589763!2d104.8995165!3d11.562973499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310951b12cb1dcc7%3A0xb36c6119eb9b2c23!2sTronmatix%20Computer%20Store!5e0!3m2!1sen!2skh!4v1783668991024!5m2!1sen!2skh"
                width="100%"
                height="100%"
                style={{ border: 0 }}
                allowFullScreen=""
                loading="lazy"
                referrerPolicy="strict-origin-when-cross-origin"
              />
            </div>
          </div> */}

          {/* ─── Col 3: MENU ─── */}
          <div>
            <div
              className="ml-10 font-bold mb-3"
              style={{ fontSize: fs, color: heading, fontFamily: headFont }}
            >
              {isKhmer ? "ម៉ឺនុយ" : "MENU"}
            </div>
            <ul
              className="ml-10 space-y-1.5"
              style={{ fontSize: fl, fontWeight: 700 }}
            >
              <li>
                <Link
                  to="/"
                  className="f-hover-link inline-flex items-center gap-1.5"
                  style={{ color: text, fontFamily: bodyFont }}
                >
                  <span style={{ color: "#F97316" }}>•</span>
                  {isKhmer ? "ទំព័រដើម" : "HOME"}
                </Link>
              </li>
              <li>
                <Link
                  to="/contact"
                  className="f-hover-link inline-flex items-center gap-1.5"
                  style={{ color: text, fontFamily: bodyFont }}
                >
                  <span style={{ color: "#F97316" }}>•</span>
                  {isKhmer ? "ទំនាក់ទំនង" : "CONTACT US"}
                </Link>
              </li>
            </ul>
          </div>

          {/* ─── Col 4: CONTACT ─── */}
          <div>
            <div
              className="font-bold mb-2"
              style={{ fontSize: fs, color: heading, fontFamily: headFont }}
            >
              {isKhmer ? "ទំនាក់ទំនង" : "CONTACT"}
            </div>
            <ul className="space-y-1" style={{ fontSize: fl, fontWeight: 700 }}>
              <li>
                <a
                  href="tel:+85577711126"
                  className="f-hover-phone inline-flex items-center gap-1.5"
                  style={{ fontFamily: bodyFont, color: text }}
                >
                  <PhoneIcon
                    className="f-phone-icon"
                    style={{ color: "#F97316", transition: "color 0.2s" }}
                  />
                  {isKhmer ? "០៧៧ ៧១១ ១២៦" : "077 711 126"}
                </a>
              </li>
              <li>
                <a
                  href="tel:+855967333725"
                  className="f-hover-phone inline-flex items-center gap-1.5"
                  style={{ fontFamily: bodyFont, color: text }}
                >
                  <PhoneIcon
                    className="f-phone-icon"
                    style={{ color: "#F97316", transition: "color 0.2s" }}
                  />
                  {isKhmer ? "០៩៦ ៧៣៣ ៣៧២៥" : "096 733 3725"}
                </a>
              </li>
            </ul>
          </div>

          {/* ─── Col 6: SOCIAL ─── */}
          <div>
            <div
              className="font-bold mb-3"
              style={{ fontSize: fs, color: heading, fontFamily: headFont }}
            >
              {isKhmer ? "បណ្ដាញសង្គម" : "SOCIAL"}
            </div>
            <div className="flex gap-2 flex-wrap">
              {socials.map((s, i) => (
                <a
                  key={i}
                  href={s.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="f-social-btn w-9 h-9 rounded-full flex items-center justify-center hover:text-white"
                  style={{
                    background: dark ? "#1f2937" : "#ffffff",
                    borderColor: dark ? "#374151" : "#d1d5db",
                    color: dark ? "#9ca3af" : "#4b5563",
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = s.brand;
                    e.currentTarget.style.color = "#ffffff";
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = dark
                      ? "#1f2937"
                      : "#ffffff";
                    e.currentTarget.style.color = dark ? "#9ca3af" : "#4b5563";
                  }}
                >
                  {s.icon}
                </a>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* ═══════════ BOTTOM BAR ═══════════ */}
      <div style={{ borderTop: `1px solid ${border}`, background: bottomBg }}>
        <div
          className="max-w-[1280px] mx-auto px-4 py-3 text-center"
          style={{ fontSize: isKhmer ? 20 : 23, fontWeight: 700, color: text }}
        >
          © TRONMATIX. 2026. ALL RIGHTS RESERVED
        </div>
      </div>
    </footer>
  );
}

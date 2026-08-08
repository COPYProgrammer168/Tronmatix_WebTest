import { useLocation, Link } from "react-router-dom";
import { useBottomNavVisible } from "../hooks/useBottomNavVisible";
import { useMobileMenu } from "../context/MobileMenuContext";

const ICONS = {
  home: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  ),
  contact: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
    </svg>
  ),
  facebook: (
    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
    </svg>
  ),
  telegram: (
    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
    </svg>
  ),
  tiktok: (
    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z" />
    </svg>
  ),
};

const SOCIAL_EXTERNAL = {
  facebook: "https://www.facebook.com/TronmatixComputer",
  telegram: "https://t.me/+VZScFi_U95PsFk0M",
  tiktok: "https://www.tiktok.com/@tronmatixcomputer",
};

const NAV_ITEMS = [
  { key: "home", label: "HOME", to: "/", icon: ICONS.home },
  { key: "contact", label: "CONTACT", to: "/contact", icon: ICONS.contact },
  { key: "facebook", label: "FACEBOOK", href: SOCIAL_EXTERNAL.facebook, icon: ICONS.facebook },
  { key: "telegram", label: "TELEGRAM", href: SOCIAL_EXTERNAL.telegram, icon: ICONS.telegram },
  { key: "tiktok", label: "TIKTOK", href: SOCIAL_EXTERNAL.tiktok, icon: ICONS.tiktok },
];

export default function BottomNav() {
  const location = useLocation();
  const { isVisible } = useBottomNavVisible();
  const { isMobileMenuOpen } = useMobileMenu();

  // Show on the homepage and the contact page.
  const showOn = ["/", "/contact"];
  if (!showOn.includes(location.pathname) || isMobileMenuOpen) return null;

  return (
    <nav
      className={`md:hidden fixed bottom-0 left-0 right-0 z-50 transition-transform duration-300 ${
        isVisible ? "translate-y-0" : "translate-y-full"
      }`}
      style={{
        background: "#2d2d2e",
        borderTop: "1px solid rgba(255,255,255,0.06)",
        paddingBottom: "env(safe-area-inset-bottom)",
      }}
    >
      <div className="flex items-stretch">
        {NAV_ITEMS.map(({ key, label, to, href, icon }) => {
          const isActive = to ? location.pathname === to : false;
          const isLink = !!to;
          const color = isActive ? "#F97316" : "#9ca3af";

          const inner = (
            <>
              <span style={{ color, display: "flex", alignItems: "center", justifyContent: "center" }}>
                {icon}
              </span>
              <span
                style={{
                  fontSize: 12,
                  fontWeight: 800,
                  letterSpacing: 0.8,
                  color,
                  marginTop: 3,
                  textTransform: "uppercase",
                }}
              >
                {label}
              </span>
            </>
          );

          if (isLink) {
            return (
              <Link
                key={key}
                to={to}
                className="flex-1 flex flex-col items-center justify-center py-2.5"
                style={{ textDecoration: "none" }}
              >
                {inner}
              </Link>
            );
          }

          return (
            <a
              key={key}
              href={href}
              target="_blank"
              rel="noopener noreferrer"
              className="flex-1 flex flex-col items-center justify-center py-2.5"
              style={{ textDecoration: "none" }}
            >
              {inner}
            </a>
          );
        })}
      </div>
    </nav>
  );
}

import { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";

const MOBILE_BREAKPOINT = 768;
const SCROLL_THRESHOLD = 150;

export function useBottomNavVisible() {
  const location = useLocation();
  // Show on the homepage and the contact page.
  const isOnNavPage = ["/", "/contact"].includes(location.pathname);

  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    if (!isOnNavPage || typeof window === "undefined") return;

    // Re-check the mobile breakpoint on every scroll + on resize, so the nav
    // appears correctly even if the viewport width changes (e.g. devtools).
    const handleScroll = () => {
      const isMobile = window.innerWidth < MOBILE_BREAKPOINT;
      const y = window.scrollY;
      setIsVisible(isMobile && y > SCROLL_THRESHOLD);
    };

    handleScroll();

    window.addEventListener("scroll", handleScroll, { passive: true });
    window.addEventListener("resize", handleScroll, { passive: true });
    return () => {
      window.removeEventListener("scroll", handleScroll);
      window.removeEventListener("resize", handleScroll);
    };
  }, [isOnNavPage]);

  return { isVisible };
}

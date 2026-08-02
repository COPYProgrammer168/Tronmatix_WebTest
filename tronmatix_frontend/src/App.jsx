import { useState, lazy, Suspense, useEffect } from "react";
import { BrowserRouter, Routes, Route, useLocation } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import { CartProvider } from "./context/CartContext";
import { FavoritesProvider } from "./context/FavoritesContext";
import { LocationProvider } from "./context/LocationContext";
import { DiscountProvider } from "./context/DiscountContext";
import { ThemeProvider } from "./context/ThemeContext";
import { LanguageProvider } from "./context/LanguageContext";
import { MobileMenuProvider, useMobileMenu } from "./context/MobileMenuContext";
import { useTheme } from "./context/ThemeContext";
import { useLang } from "./context/LanguageContext";

import Navbar from "./components/Navbar";
import Footer from "./components/Footer";
import ErrorBoundary from "./components/ErrorBoundary";
import BottomNav from "./components/BottomNav";
import CartSlider from "./components/CartSlider";
import SupportChat from "./components/SupportChat";
import Notification from "./components/Notification";
import { useCart } from "./context/CartContext";

import { StaffGuard, DevGuard } from "./components/guards/PortalGuards";

const AuthModal      = lazy(() => import("./components/AuthModal"));
const StaffLoginPage = lazy(() => import("./pages/auth/StaffLoginPage"));
const DevLoginPage   = lazy(() => import("./pages/auth/DevLoginPage"));

//These were used but never imported — caused the crash
const StaffDashboard = lazy(() => import("./pages/StaffDashboard"));
const DevDashboard   = lazy(() => import("./pages/DevDashboard"));

const HomePage          = lazy(() => import("./pages/HomePage"));
const CartPage          = lazy(() => import("./pages/CartPage"));
const CheckoutPage      = lazy(() => import("./pages/CheckoutPage"));
const CategoryPage      = lazy(() =>import("./pages/CategoryPage").then((m) => ({ default: m.CategoryPage })));
const ProductDetailPage = lazy(() => import("./pages/ProductDetailPage"));
const OrdersPage        = lazy(() => import("./pages/OrdersPage"));
const FavoritesPage     = lazy(() => import("./pages/FavoritesPage"));
const UserProfilePage   = lazy(() => import("./pages/UserProfilePage"));
const ContactPage       = lazy(() => import("./pages/ContactPage"));

function PageSpinner() {
  return (
    <div style={{ display: "flex", alignItems: "center", justifyContent: "center", minHeight: "60vh" }}>
      <div style={{
        width: 44, height: 44,
        border: "4px solid rgba(249,115,22,0.2)",
        borderTopColor: "#F97316",
        borderRadius: "50%",
        animation: "spin 0.7s linear infinite",
      }} />
      <style>{"@keyframes spin{to{transform:rotate(360deg)}}"}</style>
    </div>
  );
}

function AppContent() {
  const [authMode, setAuthMode] = useState(null);
  const [resetToken, setResetToken] = useState(null);
  const [resetEmail, setResetEmail] = useState(null);
  const { dark }    = useTheme();
  const { isKhmer } = useLang();
  const location    = useLocation();
  const { notification, setNotification } = useCart();
  const { setIsLoginModalOpen } = useMobileMenu();

  // Sync the login/register modal open state into shared context so floating
  // buttons (scroll-to-top, chat bubble) can hide while the modal is open.
  useEffect(() => {
    setIsLoginModalOpen(!!authMode);
  }, [authMode, setIsLoginModalOpen]);

  const isPortal =
    location.pathname.startsWith("/staff") ||
    location.pathname.startsWith("/dev")   ||
    location.pathname.startsWith("/admin");

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token  = params.get("token");
    const email  = params.get("email");
    if (token && email) {
      setResetToken(token);
      setResetEmail(email);
      setAuthMode("reset-password");
      window.history.replaceState({}, "", window.location.pathname + window.location.hash);
    }
  }, []);

  useEffect(() => {
    document.body.classList.toggle("lang-km", isKhmer);
    document.documentElement.lang = isKhmer ? "km" : "en";
  }, [isKhmer]);

  return (
    <div
      className="min-h-screen flex flex-col transition-colors duration-300"
      style={{
        background: dark ? "#111827" : "#ffffff",
        color:      dark ? "#f9fafb" : "#111827",
      }}
    >
      {!isPortal && <Navbar onAuthOpen={(mode) => setAuthMode(mode)} />}
      {!isPortal && <CartSlider />}
      {!isPortal && <SupportChat />}
      {!isPortal && notification && (
        <Notification
          message={notification}
          onClose={() => setNotification(null)}
        />
      )}

      {authMode && (
        <Suspense fallback={null}>
          <AuthModal
            mode={authMode}
            resetToken={resetToken}
            resetEmail={resetEmail}
            onClose={() => {
              setAuthMode(null);
              setResetToken(null);
              setResetEmail(null);
            }}
            onSwitch={(m) => setAuthMode(m)}
          />
        </Suspense>
      )}

      <main className="flex-1">
        <ErrorBoundary>
        <Suspense fallback={<PageSpinner />}>
          <Routes>
            <Route path="/"                        element={<HomePage />} />
            <Route path="/cart"                    element={<CartPage />} />
            <Route path="/checkout"                element={<CheckoutPage />} />
            <Route path="/orders"                  element={<OrdersPage />} />
            <Route path="/favorites"               element={<FavoritesPage />} />
            <Route path="/category/:category"      element={<CategoryPage />} />
            <Route path="/category/:category/:sub" element={<CategoryPage />} />
            <Route path="/product/:id"             element={<ProductDetailPage />} />
            <Route path="/search"                  element={<CategoryPage />} />
            <Route path="/contact"                 element={<ContactPage />} />
            <Route path="/profile"                 element={<UserProfilePage />} />
            <Route path="/staff/login"             element={<StaffLoginPage />} />
            <Route path="/dev/login"               element={<DevLoginPage />} />
            <Route
              path="/staff/dashboard"
              element={
                <StaffGuard>
                  <StaffDashboard />
                </StaffGuard>
              }
            />
            <Route
              path="/dev/dashboard"
              element={
                <DevGuard>
                  <DevDashboard />
                </DevGuard>
              }
            />
          </Routes>
        </Suspense>
        </ErrorBoundary>
      </main>

      {!isPortal && <BottomNav />}
      {!isPortal && <Footer />}
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <ThemeProvider>
        <LanguageProvider>
          <AuthProvider>
            <CartProvider>
              <FavoritesProvider>
                <LocationProvider>
                  <DiscountProvider>
                    <MobileMenuProvider>
                      <AppContent />
                    </MobileMenuProvider>
                  </DiscountProvider>
                </LocationProvider>
              </FavoritesProvider>
            </CartProvider>
          </AuthProvider>
        </LanguageProvider>
      </ThemeProvider>
    </BrowserRouter>
  );
}
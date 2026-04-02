import { useState, lazy, Suspense } from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import { CartProvider } from "./context/CartContext";
import { FavoritesProvider } from "./context/FavoritesContext";
import { LocationProvider } from "./context/LocationContext";
import { DiscountProvider } from "./context/DiscountContext";
import { ThemeProvider } from "./context/ThemeContext";
import { useTheme } from "./context/ThemeContext";
import { useAuth } from "./context/AuthContext";

// Eager — present on every page, must load immediately
import Navbar from "./components/Navbar";
import Footer from "./components/Footer";
import CartSlider from "./components/CartSlider";
import SupportChat from "./components/SupportChat";

// AuthModal is lazy — only needed when the user clicks login
// ProfileSetupModal is also exported from the same chunk
const AuthModal          = lazy(() => import("./components/AuthModal"));
const ProfileSetupModal  = lazy(() =>
  import("./components/AuthModal").then((m) => ({ default: m.ProfileSetupModal }))
);

// Pages — each becomes its own chunk, downloaded only when navigated to
const HomePage          = lazy(() => import("./pages/HomePage"));
const CartPage          = lazy(() => import("./pages/CartPage"));
const CheckoutPage      = lazy(() => import("./pages/CheckoutPage"));
const CategoryPage      = lazy(() => import("./pages/CategoryPage").then(m => ({ default: m.CategoryPage })));
const ProductDetailPage = lazy(() => import("./pages/ProductDetailPage"));
const OrdersPage        = lazy(() => import("./pages/OrdersPage"));
const FavoritesPage     = lazy(() => import("./pages/FavoritesPage"));
const UserProfilePage   = lazy(() => import("./pages/UserProfilePage"));
const ContactPage       = lazy(() => import("./pages/ContactPage"));

function PageSpinner() {
  return (
    <div style={{ display:"flex", alignItems:"center", justifyContent:"center", minHeight:"60vh" }}>
      <div style={{
        width:44, height:44,
        border:"4px solid rgba(249,115,22,0.2)",
        borderTopColor:"#F97316",
        borderRadius:"50%",
        animation:"spin 0.7s linear infinite",
      }}/>
      <style>{"@keyframes spin{to{transform:rotate(360deg)}}"}</style>
    </div>
  )
}

function AppContent() {
  const [authMode, setAuthMode] = useState(null);
  const { dark } = useTheme();

  // ── Post-OAuth profile completion modal ────────────────────────────────────
  // Shown globally (outside the route tree) so it overlays any page.
  // AuthContext sets needsProfileSetup=true when Google/Telegram creates a new user.
  const { needsProfileSetup, setNeedsProfileSetup } = useAuth();

  return (
    <div
      className="min-h-screen flex flex-col transition-colors duration-300"
      style={{
        background: dark ? "#111827" : "#ffffff",
        color: dark ? "#f9fafb" : "#111827",
      }}
    >
      <Navbar onAuthOpen={(mode) => setAuthMode(mode)} />
      <CartSlider />
      <SupportChat />

      {/* Login / Register / Forgot Password modal */}
      {authMode && (
        <Suspense fallback={null}>
          <AuthModal
            mode={authMode}
            onClose={() => setAuthMode(null)}
            onSwitch={(m) => setAuthMode(m)}
          />
        </Suspense>
      )}

      {/* Global post-OAuth profile setup modal (username + phone for new Google/Telegram users) */}
      {needsProfileSetup && (
        <Suspense fallback={null}>
          <ProfileSetupModal onClose={() => setNeedsProfileSetup(false)} />
        </Suspense>
      )}

      <main className="flex-1">
        <Suspense fallback={<PageSpinner />}>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/cart" element={<CartPage />} />
            <Route path="/checkout" element={<CheckoutPage />} />
            <Route path="/orders" element={<OrdersPage />} />
            <Route path="/favorites" element={<FavoritesPage />} />
            <Route path="/category/:category" element={<CategoryPage />} />
            <Route path="/category/:category/:sub" element={<CategoryPage />} />
            <Route path="/product/:id" element={<ProductDetailPage />} />
            <Route path="/search" element={<CategoryPage />} />
            <Route path="/contact" element={<ContactPage />} />
            <Route path="/profile" element={<UserProfilePage />} />
          </Routes>
        </Suspense>
      </main>
      <Footer />
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <ThemeProvider>
        <AuthProvider>
          <CartProvider>
            <FavoritesProvider>
              <LocationProvider>
                <DiscountProvider>
                  <AppContent />
                </DiscountProvider>
              </LocationProvider>
            </FavoritesProvider>
          </CartProvider>
        </AuthProvider>
      </ThemeProvider>
    </BrowserRouter>
  );
}

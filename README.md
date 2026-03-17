# TRONMATIX COMPUTER — Full Stack E-Commerce

## Tech Stack
- **Frontend**: React 18 + Tailwind CSS
- **Backend**: Laravel 11 + PHP 8.2
- **Database**: PostgreSQL

---

## 🚀 Frontend Setup (React)

```bash
cd tronmatix

# Install dependencies
npm install

# Start dev server (runs on http://localhost:3000)
npm start

# Production build
npm run build
```

### Environment Variables
Create `tronmatix/.env`:
```
REACT_APP_API_URL=http://localhost:8000/api
```

---

## 🛠 Backend Setup (Laravel)

### Prerequisites
- PHP 8.2+
- Composer
- PostgreSQL 14+

```bash
cd tronmatix-backend

# Install Laravel
composer create-project laravel/laravel . --prefer-dist

# Copy files from this folder into the Laravel project:
# - app/Http/Controllers/ → your Controllers
# - routes/api.php → api routes
# - database/migrations/ → migrations
# - database/seeders/ → seeders

# Copy environment file
cp .env.example .env

# Edit .env — fill in DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Generate app key
php artisan key:generate

# Install Laravel Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Create PostgreSQL database
psql -U postgres -c "CREATE DATABASE tronmatix_db;"

# Run migrations + seed
php artisan migrate --seed

# Start server
php artisan serve  # http://localhost:8000
```

### CORS Configuration
In `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

---

## 📁 Project Structure

```
tronmatix/                          ← React Frontend
├── public/
│   ├── index.html
│   └── logo.png                   ← Tronmatix logo
├── src/
│   ├── assets/
│   │   └── HurstBagod.otf         ← Custom font
│   ├── components/
│   │   ├── Navbar.jsx             ← Sticky navbar, dropdowns, search
│   │   ├── Footer.jsx
│   │   ├── ProductCard.jsx        ← Reusable product card
│   │   ├── SlideCart.jsx          ← Slide-in cart panel
│   │   ├── LoadingScreen.jsx      ← Animated loading screen
│   │   └── Spinner.jsx
│   ├── context/
│   │   └── AppContext.jsx         ← Cart + Auth global state
│   ├── pages/
│   │   ├── HomePage.jsx           ← Banner carousel + product sections
│   │   ├── AuthPage.jsx           ← Login + Register
│   │   ├── CategoryPage.jsx       ← Product listing + filters + pagination
│   │   ├── ProductDetailPage.jsx  ← Product detail + related products
│   │   ├── CartPage.jsx           ← Shopping cart
│   │   ├── CheckoutPage.jsx       ← Multi-step checkout + payment
│   │   ├── ReceiptPage.jsx        ← Order confirmation + print
│   │   └── ContactPage.jsx
│   ├── utils/
│   │   └── api.js                 ← API layer (swap mocks for real calls)
│   ├── App.jsx                    ← Router + Layout
│   └── index.css                  ← Global styles + animations

tronmatix-backend/                  ← Laravel API Backend
├── app/Http/Controllers/
│   ├── AuthController.php         ← Register, login, logout
│   ├── ProductController.php      ← Products + banners
│   ├── OrderController.php        ← Place + list orders
│   └── CategoryController.php
├── database/
│   ├── migrations/                ← users, products, orders, order_items
│   └── seeders/DatabaseSeeder.php
└── routes/api.php                 ← All API routes
```

---

## 🔌 Connecting Frontend to Real Backend

1. Set `REACT_APP_API_URL=http://localhost:8000/api` in `.env`
2. In `src/utils/api.js`, replace mock functions with real axios calls:

```javascript
// Example: replace getProducts mock with real call
export const getProducts = async (category, page, filters) => {
  const res = await api.get('/products', {
    params: { category, page, ...filters }
  });
  return res.data;
};
```

---

## 🎨 Design System

| Token | Value |
|-------|-------|
| Orange | `#F97316` |
| Dark BG | `#0A0A0A` |
| Card BG | `#111111` |
| Font (headings) | HurstBagod (custom) |
| Font (body) | Rajdhani (Google) |

---

## 📱 Responsive Breakpoints
- Mobile: < 640px (single column, hamburger menu)
- Tablet: 640px–1024px (2-col grid)
- Desktop: > 1024px (full navbar, 4-6 col grid)

---

## ✅ Features Implemented

- [x] Custom loading screen with logo animation
- [x] Sticky navbar that hides on scroll down, shows on scroll up
- [x] Dropdowns with hover transitions
- [x] Search with debounce and live results
- [x] Mobile hamburger drawer
- [x] Login / Register with tab toggle
- [x] Cart with localStorage persistence
- [x] Slide-in cart panel
- [x] Product grid with hover animations
- [x] Category pages with filters (brand, price) + pagination
- [x] Product detail page with image carousel + related products
- [x] Cart page with qty controls
- [x] Multi-step checkout (shipping → payment)
- [x] Receipt page with print support
- [x] Contact form
- [x] Laravel API (auth, products, orders, PostgreSQL)
- [x] Fully responsive (mobile, tablet, desktop)

<!-- ============================================================
     TRONMATIX COMPUTER — Full-Stack E-Commerce README
     ============================================================ -->

<div align="center">

<!-- ─── ANIMATED HERO SVG ───────────────────────────────────── -->
<svg viewBox="0 0 900 300" width="100%" height="auto" style="max-width:900px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="heroGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%"   stop-color="#F97316" />
      <stop offset="50%"  stop-color="#FB923C" />
      <stop offset="100%" stop-color="#F97316" />
    </linearGradient>
    <linearGradient id="heroGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%"   stop-color="#F97316" stop-opacity="0" />
      <stop offset="50%"  stop-color="#F97316" stop-opacity="0.6" />
      <stop offset="100%" stop-color="#F97316" stop-opacity="0" />
    </linearGradient>
    <filter id="glow">
      <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <!-- Background glow circle -->
  <circle cx="450" cy="110" r="80" fill="url(#heroGrad2)" opacity="0.3">
  </circle>

  <!-- Logo icon -->
  <text x="130" y="150" font-size="72" font-family="monospace" font-weight="bold"
        fill="url(#heroGrad)" filter="url(#glow)" text-anchor="middle">
    ⚡
  </text>

  <!-- Title -->
  <text x="360" y="130" font-size="52" font-family="'Segoe UI','Rajdhani',sans-serif"
        font-weight="900" fill="#F97316" filter="url(#glow)">
    TRONMATIX
  </text>

  <!-- Subtitle -->
  <text x="360" y="172" font-size="22" font-family="'Segoe UI','Rajdhani',sans-serif"
        fill="#9CA3AF" font-weight="600">
    Computer E-Commerce Platform
  </text>

  <!-- Tagline in Khmer -->
  <text x="360" y="200" font-size="16" font-family="'Kdam Thmor Pro','Rajdhani',sans-serif"
        fill="#6B7280" font-style="italic">
    ប្រព័ន្ធលក់គ្រឿងកុំព្យូទ័រគ្រប់គ្រងពេញលេញ
  </text>

  <!-- Animated divider line -->
  <line x1="40" y1="230" x2="860" y2="230"
        stroke="url(#heroGrad2)" stroke-width="2" stroke-linecap="round" />

  <!-- Pulsing dot on divider -->
  <circle cx="450" cy="230" r="5" fill="#F97316">
  </circle>

  <!-- Stack labels -->
  <g transform="translate(0, 15)">
    <rect x="40"   y="235" width="120" height="28" rx="14" fill="#1F2937" />
    <text x="100" y="254" font-size="12" fill="#34D399" text-anchor="middle" font-family="monospace">React 18 SPA</text>

    <rect x="175"  y="235" width="130" height="28" rx="14" fill="#1F2937" />
    <text x="240" y="254" font-size="12" fill="#F97316" text-anchor="middle" font-family="monospace">Laravel 12 API</text>

    <rect x="320"  y="235" width="110" height="28" rx="14" fill="#1F2937" />
    <text x="375" y="254" font-size="12" fill="#60A5FA" text-anchor="middle" font-family="monospace">PostgreSQL</text>

    <rect x="445"  y="235" width="110" height="28" rx="14" fill="#1F2937" />
    <text x="500" y="254" font-size="12" fill="#A78BFA" text-anchor="middle" font-family="monospace">Blade Admin</text>

    <rect x="570"  y="235" width="120" height="28" rx="14" fill="#1F2937" />
    <text x="630" y="254" font-size="12" fill="#FBBF24" text-anchor="middle" font-family="monospace">ABA PayWay</text>

    <rect x="705"  y="235" width="110" height="28" rx="14" fill="#1F2937" />
    <text x="760" y="254" font-size="12" fill="#38BDF8" text-anchor="middle" font-family="monospace">Telegram</text>
  </g>
</svg>

<br />

<!-- ─── SHIELDS.IO BADGES ───────────────────────────────────── -->
[![React](https://img.shields.io/badge/React-18.2-61DAFB?logo=react&logoColor=white&style=for-the-badge)](https://react.dev)
[![Vite](https://img.shields.io/badge/Vite-8-646CFF?logo=vite&logoColor=white&style=for-the-badge)](https://vite.dev)
[![Laravel](https://img.shields.io/badge/Laravel-12-F93208?logo=laravel&logoColor=white&style=for-the-badge)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white&style=for-the-badge)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white&style=for-the-badge)](https://postgresql.org)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-3-06B6D4?logo=tailwindcss&logoColor=white&style=for-the-badge)](https://tailwindcss.com)

[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-4A5568?style=for-the-badge&logo=laravel)](https://laravel.com/docs/sanctum)
[![Bakong KHQR](https://img.shields.io/badge/Payments-Bakong%20KHQR-16A34A?style=for-the-badge)]()
[![Leaflet](https://img.shields.io/badge/Maps-Leaflet-199900?style=for-the-badge&logo=leaflet)](https://leafletjs.com)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)](LICENSE)

</div>

<!-- ─── ANIMATED SECTION DIVIDER ────────────────────────────── -->
<svg viewBox="0 0 800 20" width="100%" height="20" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F97316" stop-opacity="0" />
      <stop offset="50%" stop-color="#F97316" stop-opacity="0.8" />
      <stop offset="100%" stop-color="#F97316" stop-opacity="0" />
    </linearGradient>
  </defs>
  <line x1="0" y1="10" x2="800" y2="10" stroke="url(#divGrad1)" stroke-width="2" stroke-dasharray="8 6"/>
  <circle cx="400" cy="10" r="4" fill="#F97316">
  </circle>
</svg>

<br />

# 📋 Table of Contents

1. [Overview](#-overview)
2. [Architecture](#-architecture)
3. [Tech Stack](#️-tech-stack)
4. [Features](#-features)
5. [Design System](#-design-system)
6. [Pages & Routes](#-pages--routes)
7. [Database Schema](#️-database-schema)
8. [API Endpoints](#-api-endpoints)
9. [Setup Guide](#-setup-guide)
10. [Project Structure](#-project-structure)
11. [Security](#-security)
12. [Localization](#-localization)
13. [Screenshots](#-screenshots)
14. [License](#-license)

---

## 📖 Overview

**Tronmatix Computer** is a full-stack e-commerce platform built for a computer and PC parts shop in **Phnom Penh, Cambodia**. It serves three distinct user groups through a single, cohesive system:

| User | Interface | Purpose |
|------|-----------|---------|
| 🛒 **Customer** | React SPA Storefront | Browse products, cart, checkout with KHQR/Cash payment |
| 👨‍💼 **Staff / Admin** | Blade Dashboard + React SPA | Manage products, orders, users, discounts, reports |
| 👨‍🔧 **Developer** | React Dev Portal | System health, logs, environment monitoring |

The platform supports **dual-language** (English & Khmer), **multi-role RBAC**, **ABA PayWay / Bakong KHQR** payment integration, **dual Telegram bot** notifications, and full order lifecycle management with delivery tracking.

> 🎓 This project was developed as a **Bachelor's / Master's Thesis** project at a Cambodian university, demonstrating a production-grade monolithic e-commerce system.

---

## 🏗 Architecture

<svg viewBox="0 0 800 380" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="boxClient" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#2DD4BF"/>
      <stop offset="100%" stop-color="#14B8A6"/>
    </linearGradient>
    <linearGradient id="boxReact" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#61DAFB"/>
      <stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
    <linearGradient id="boxLaravel" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#F97316"/>
      <stop offset="100%" stop-color="#EA580C"/>
    </linearGradient>
    <linearGradient id="boxDB" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#6366F1"/>
      <stop offset="100%" stop-color="#4F46E5"/>
    </linearGradient>
    <linearGradient id="boxExt" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#A78BFA"/>
      <stop offset="100%" stop-color="#7C3AED"/>
    </linearGradient>
    <filter id="archGlow">
      <feGaussianBlur stdDeviation="2" result="blur"/>
      <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
    <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 0 L 10 5 L 0 10 z" fill="#9CA3AF"/>
    </marker>
  </defs>

  <!-- Connection lines -->
  <line x1="230" y1="90" x2="340" y2="150" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="6 3" marker-end="url(#arrow)">
  </line>
  <line x1="490" y1="150" x2="490" y2="220" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="6 3" marker-end="url(#arrow)">
  </line>
  <line x1="410" y1="290" x2="230" y2="320" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="6 3" marker-end="url(#arrow)">
  </line>
  <line x1="570" y1="290" x2="700" y2="150" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="6 3" marker-end="url(#arrow)">
  </line>

  <!-- Layer labels -->
  <text x="400" y="25" font-size="14" fill="#9CA3AF" text-anchor="middle" font-family="monospace">┌─ CLIENT LAYER ──────────────────────┐</text>
  <text x="400" y="105" font-size="14" fill="#9CA3AF" text-anchor="middle" font-family="monospace">┌─ APPLICATION LAYER (REST API) ──────┐</text>
  <text x="400" y="190" font-size="14" fill="#9CA3AF" text-anchor="middle" font-family="monospace">┌─ SERVICE LAYER ─────────────────────┐</text>
  <text x="400" y="260" font-size="14" fill="#9CA3AF" text-anchor="middle" font-family="monospace">┌─ DATA LAYER ───────────────────────┐</text>

  <!-- Client layer boxes -->
  <rect x="60" y="50" width="170" height="45" rx="8" fill="url(#boxClient)" filter="url(#archGlow)"/>
  <text x="145" y="68" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">🛒 React SPA</text>
  <text x="145" y="84" font-size="10" fill="#fff" text-anchor="middle">Storefront (Port 5173)</text>

  <rect x="260" y="50" width="170" height="45" rx="8" fill="url(#boxClient)" filter="url(#archGlow)"/>
  <text x="345" y="68" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">📊 Blade Dashboard</text>
  <text x="345" y="84" font-size="10" fill="#fff" text-anchor="middle">Admin Panel (Port 8000)</text>

  <rect x="460" y="50" width="170" height="45" rx="8" fill="url(#boxClient)" filter="url(#archGlow)"/>
  <text x="545" y="68" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">⚙️ React Dashboard</text>
  <text x="545" y="84" font-size="10" fill="#fff" text-anchor="middle">Staff / Dev Portals</text>

  <rect x="660" y="50" width="120" height="45" rx="8" fill="url(#boxClient)" filter="url(#archGlow)"/>
  <text x="720" y="68" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">🤖 Telegram Bots</text>
  <text x="720" y="84" font-size="10" fill="#fff" text-anchor="middle">Admin &amp; Customer</text>

  <!-- Application layer -->
  <rect x="200" y="120" width="400" height="50" rx="8" fill="url(#boxLaravel)" filter="url(#archGlow)"/>
  <text x="400" y="142" font-size="14" fill="#fff" text-anchor="middle" font-weight="bold">Laravel 12 REST API</text>
  <text x="400" y="158" font-size="10" fill="#fff" text-anchor="middle">Sanctum Auth · RBAC Middleware · Rate Limiting · Session Fingerprinting</text>

  <!-- Service layer -->
  <rect x="60" y="210" width="150" height="50" rx="8" fill="url(#boxReact)"/>
  <text x="135" y="232" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">Controllers</text>
  <text x="135" y="248" font-size="10" fill="#fff" text-anchor="middle">Auth · Product · Order · Payment</text>

  <rect x="230" y="210" width="150" height="50" rx="8" fill="url(#boxReact)"/>
  <text x="305" y="232" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">Services</text>
  <text x="305" y="248" font-size="10" fill="#fff" text-anchor="middle">Telegram · Image · Metric · Export</text>

  <rect x="400" y="210" width="150" height="50" rx="8" fill="url(#boxReact)"/>
  <text x="475" y="232" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">Middleware</text>
  <text x="475" y="248" font-size="10" fill="#fff" text-anchor="middle">Security · Locale · Role · Throttle</text>

  <rect x="570" y="210" width="150" height="50" rx="8" fill="url(#boxReact)"/>
  <text x="645" y="232" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">Providers</text>
  <text x="645" y="248" font-size="10" fill="#fff" text-anchor="middle">Auth · Event · Route · Broadcast</text>

  <!-- Data layer -->
  <rect x="110" y="280" width="180" height="60" rx="8" fill="url(#boxDB)" filter="url(#archGlow)"/>
  <text x="200" y="303" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">🗄 PostgreSQL</text>
  <text x="200" y="320" font-size="10" fill="#fff" text-anchor="middle">Users · Orders · Products · Payments</text>
  <text x="200" y="333" font-size="9" fill="#C7D2FE" text-anchor="middle">18 tables · 28 migrations</text>

  <rect x="320" y="280" width="180" height="60" rx="8" fill="url(#boxDB)" filter="url(#archGlow)"/>
  <text x="410" y="303" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">💾 Cloud Storage</text>
  <text x="410" y="320" font-size="10" fill="#fff" text-anchor="middle">Cloudflare R2 / S3</text>
  <text x="410" y="333" font-size="9" fill="#C7D2FE" text-anchor="middle">Product images · Assets</text>

  <rect x="530" y="280" width="180" height="60" rx="8" fill="url(#boxDB)" filter="url(#archGlow)"/>
  <text x="620" y="303" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">📁 Local Storage</text>
  <text x="620" y="320" font-size="10" fill="#fff" text-anchor="middle">Logs · Cache · Sessions</text>
  <text x="620" y="333" font-size="9" fill="#C7D2FE" text-anchor="middle">public/storage</text>

  <!-- External integrations -->
  <rect x="60" y="355" width="130" height="22" rx="11" fill="url(#boxExt)" opacity="0.7"/>
  <text x="125" y="370" font-size="11" fill="#fff" text-anchor="middle">ABA PayWay</text>
  <rect x="210" y="355" width="130" height="22" rx="11" fill="url(#boxExt)" opacity="0.7"/>
  <text x="275" y="370" font-size="11" fill="#fff" text-anchor="middle">Google OAuth</text>
  <rect x="360" y="355" width="130" height="22" rx="11" fill="url(#boxExt)" opacity="0.7"/>
  <text x="425" y="370" font-size="11" fill="#fff" text-anchor="middle">Mailtrap SMTP</text>
  <rect x="510" y="355" width="130" height="22" rx="11" fill="url(#boxExt)" opacity="0.7"/>
  <text x="575" y="370" font-size="11" fill="#fff" text-anchor="middle">OpenAI API</text>
  <rect x="660" y="355" width="100" height="22" rx="11" fill="url(#boxExt)" opacity="0.7"/>
  <text x="710" y="370" font-size="11" fill="#fff" text-anchor="middle">EmailJS</text>
</svg>

### Design Patterns

| Pattern | Implementation |
|---------|---------------|
| **MVC** | Controllers (HTTP) → Services (Business Logic) → Models (Data) |
| **Service Layer** | `TelegramService`, `ImageStorageService`, `MetricComparisonService` |
| **Middleware Chain** | Request → Throttle → Auth → Role → Security → Locale → Controller |
| **Repository (partial)** | `MetricComparisonService` abstracts aggregation logic |
| **Factory** | Product `getAllImagesAttribute` bridges legacy & modern storage |
| **Strategy** | `dateFormatExpr()` selects SQL dialect per database driver |
| **Observer** | Eloquent model events (`creating`, `saved`, `deleted`) |
| **Dependency Injection** | All services injected via constructor or method injection |

---

## 🛠️ Tech Stack

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F97316"/>
      <stop offset="100%" stop-color="#EA580C"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="180" height="32" fill="url(#badgeGrad)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🚀 Frontend</text>
</svg>

| Technology | Version | Purpose |
|------------|---------|---------|
| **React** | 18.2 | UI library for SPA |
| **Vite** | 8 | Build tool & dev server |
| **React Router** | 6.20 | Client-side routing |
| **Tailwind CSS** | 3.4 | Utility-first styling |
| **Axios** | 1.13 | HTTP client |
| **Swiper** | 14 | Touch carousels & sliders |
| **Leaflet** | 1.9 | Interactive maps |
| **React-Leaflet** | 4.2 | React map bindings |
| **QRCode.react** | 4.2 | QR code rendering |
| **SweetAlert2** | 11.26 | Modal dialogs |
| **EmailJS** | 4.4 | Client-side email |
| **Bakong KHQR** | 1.0 | Cambodia QR payment |

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EA580C"/>
      <stop offset="100%" stop-color="#F97316"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="170" height="32" fill="url(#badgeGrad2)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">⚙️ Backend</text>
</svg>

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12 | PHP MVC framework |
| **PHP** | 8.4 | Runtime language |
| **Laravel Sanctum** | 4 | API token auth |
| **Laravel Fortify** | 1.30 | Login scaffolding |
| **Laravel Excel** | 3.1 | Excel/CSV exports |
| **Bakong KHQR (PHP)** | 1.0 | Server-side QR generation |
| **Flysystem S3** | 3.0 | Cloud storage (R2/S3) |
| **CommonMark** | 2.8 | Markdown parsing |
| **PostgreSQL** | 16 | Primary database |

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#7C3AED"/>
      <stop offset="100%" stop-color="#A78BFA"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="190" height="32" fill="url(#badgeGrad3)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🔌 Integrations</text>
</svg>

| Service | Type | Usage |
|---------|------|-------|
| **ABA PayWay** | Payment Gateway | Credit/debit card processing |
| **Bakong KHQR** | Mobile Payment | Cambodia QR code payments |
| **Telegram Bot 1** | Notification | Admin alerts (sales, orders, low stock) |
| **Telegram Bot 2** | Notification | Customer order updates & receipts |
| **Google OAuth** | Authentication | Social login |
| **Cloudflare R2** | Storage | Product image hosting |
| **Mailtrap** | Email | SMTP email delivery |
| **OpenAI API** | AI | AI-powered product descriptions |
| **EmailJS** | Email | Client-side contact form |

---

## ✨ Features

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad4" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669"/>
      <stop offset="100%" stop-color="#10B981"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="200" height="32" fill="url(#badgeGrad4)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🛒 Customer Storefront</text>
</svg>

- **Product Catalog** — Browse by categories: PC Builds, Monitors, PC Parts, Accessories, Furniture, Resell Items with sub-category filtering
- **Advanced Search** — Real-time search with debounce, filter by brand, price range, sort by newest/price/name/rating
- **Interactive Product Cards** — Hover animations, quick-add to cart, discount badges, wishlist toggle
- **Product Detail Page** — Image carousel/gallery, full specs, brand info, stock status, related products carousel
- **Shopping Cart** — Slide-out panel, quantity adjustment, persistent localStorage, discount application
- **Multi-Step Checkout** — Step 1: Delivery/Pickup selection with map address picker & date/time scheduling. Step 2: Payment method + order review
- **Multiple Payment Methods** — Cash on Delivery (COD), Pay at Store, Bakong KHQR (Cambodian QR payment with live polling)
- **Order Management** — Full order history, status tracking pipeline (pending → confirmed → processing → shipped → delivered), cancellation, printed receipts
- **Wishlist / Favorites** — Heart-toggle on products, dedicated favorites page with persistent storage
- **User Profile** — Edit personal info, avatar upload, VIP progress tracking, saved addresses management, Telegram connect
- **Live Support Chat** — In-app AI assistant "TRX" for PC build advice, compatibility checks, order help
- **Contact Form** — EmailJS-powered contact form with validation
- **Responsive Design** — Mobile-first responsive layout (mobile, tablet, desktop)
- **Dark/Light Theme** — Full dark mode with persistent preference, smooth transitions
- **English/Khmer Localization** — Complete bilingual UI with font switching

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad5" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/>
      <stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="210" height="32" fill="url(#badgeGrad5)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">📊 Admin Dashboard</text>
</svg>

- **Product Management (CRUD)** — Add/edit/delete products, multi-image gallery (up to 8 images), AI-generated descriptions, stock tracking with low-stock alerts
- **Order Management** — View all orders, update status workflow, verify payments, print packing slips, delivery management
- **User Management** — View/manage customers, staff accounts, role assignments
- **Staff Management** — CRUD for staff accounts, real-time online/offline heartbeat monitoring
- **Discount System** — Create coupon codes (percentage/fixed), badge discounts (auto-applied), usage tracking, expiration management
- **Banner Management** — Create/edit promotional banners with product linking and display ordering
- **Video Management** — Upload promotional videos with titles and product links
- **Reports & Analytics** — Revenue charts (monthly/quarterly/yearly), order statistics, top products, Excel/CSV export
- **Role-Based Permissions** — Granular permission matrix (9 features × 6 roles), superadmin bypass
- **Settings** — System configuration, permission matrix UI, theme preferences
- **Feedback Viewer** — View customer feedback and ratings

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad6" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626"/>
      <stop offset="100%" stop-color="#EF4444"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="170" height="32" fill="url(#badgeGrad6)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🔒 Security Features</text>
</svg>

- **Multi-Guard Authentication** — Web (session), Admin (session), Sanctum (token) for different user types
- **Session Fingerprinting** — HMAC-SHA256 of User-Agent + IP against APP_KEY prevents session hijacking
- **Role-Based Access Control** — 6 roles: `superadmin`, `admin`, `editor`, `seller`, `delivery`, `developer`
- **Rate Limiting** — Login (5/min), staff/dev login (10/min), API (configurable)
- **CSRF Protection** — Sanctum for SPA, built-in for Blade
- **Security Headers** — HSTS, X-Frame-Options, X-Content-Type-Options
- **Ban Detection** — `not_banned` middleware on protected routes
- **Absolute Session Timeout** — 8 hours with 15-minute rotation intervals
- **Payment Security** — HMAC-SHA512 signing, RSA encryption, idempotent webhook handling

---

## 🎨 Design System

| Token | Value | Usage |
|-------|-------|-------|
| **Primary** | `#F97316` | Buttons, links, accents, scrollbar |
| **Dark BG** | `#0A0A0A` / `#111827` | App background (dark mode) |
| **Card BG** | `#111111` / `#1F2937` | Cards, panels (dark mode) |
| **Light BG** | `#FFFFFF` | App background (light mode) |
| **Success** | `#10B981` | Confirmed, paid states |
| **Warning** | `#F59E0B` | Pending states |
| **Error** | `#EF4444` | Cancelled, error states |

| Font | Typeface | Usage |
|------|----------|-------|
| **Heading (EN)** | HurstBagod | Brand headers, titles |
| **Body (EN)** | Rajdhani | Paragraphs, buttons |
| **Heading (KM)** | Kh_Jrung_Thom | Khmer titles |
| **Body (KM)** | Kdam Thmor Pro | Khmer body text |

| Breakpoint | Width | Layout |
|------------|-------|--------|
| **Mobile** | < 640px | Single column, hamburger menu |
| **Tablet** | 640–1024px | 2–3 column grid |
| **Desktop** | > 1024px | Full navbar, 4–6 column grid |

---

## 📄 Pages & Routes

| Route | Page | Access |
|-------|------|--------|
| `/` | HomePage — Banner carousel, featured products, category sections | Public |
| `/category/:category` | CategoryPage — Product grid, filters, pagination | Public |
| `/category/:category/:sub` | CategoryPage — Sub-category filtered | Public |
| `/product/:id` | ProductDetailPage — Gallery, specs, related items | Public |
| `/search` | CategoryPage — Search results | Public |
| `/cart` | CartPage — Full cart with qty controls | Public |
| `/checkout` | CheckoutPage — Multi-step (Delivery → Payment) | Auth required |
| `/orders` | OrdersPage — Order history & tracking | Auth required |
| `/favorites` | FavoritesPage — Saved products | Public |
| `/profile` | UserProfilePage — Edit profile, avatar, saved addresses | Auth required |
| `/contact` | ContactPage — Form & support chat | Public |
| `/staff/login` | StaffLoginPage — Staff portal login | Public |
| `/dev/login` | DevLoginPage — Developer portal login | Public |
| `/staff/dashboard` | StaffDashboard — Staff/admin operations (6 tabs) | Staff+ roles |
| `/dev/dashboard` | DevDashboard — System health, logs, env | Developer only |
| `/dashboard/*` | Blade Dashboard — Laravel server-rendered admin | Admin roles |

### Frontend Route Map (React Router v6)

```mermaid
graph LR
    subgraph Public
        A["/"] --> B["/category/:cat"]
        B --> C["/product/:id"]
        A --> D["/cart"]
        D --> E["/checkout"]
        A --> F["/contact"]
        A --> G["/search"]
    end
    subgraph Authenticated
        E --> H["/orders"]
        A --> I["/favorites"]
        A --> J["/profile"]
    end
    subgraph Portals
        K["/staff/login"] --> L["/staff/dashboard"]
        M["/dev/login"] --> N["/dev/dashboard"]
    end
```

---

## 🗄️ Database Schema

The system uses **18 database tables** with 28 migration files. Here's the entity overview:

```mermaid
erDiagram
    User ||--o{ Order : places
    User ||--o{ UserLocation : saves
    User ||--o{ Feedback : submits
    User ||--o{ TelegramConnectionToken : has
    Order ||--|{ OrderItem : contains
    Order ||--|| Payment : has
    Product ||--|{ OrderItem : includes
    Product ||--o{ Banner : featured_in
    Product ||--o{ Discount : discounted_by
    Product ||--o{ Video : promoted_in
    Category ||--|{ Product : categorizes
    Admin ||--o{ AdminSetting : configures
    Staff ||--o{ Order : manages
    Discount ||--o{ Product : applies_to
    DeliverySchedule ||--o{ Order : scheduled_for

    User {
        int id PK
        string name
        string email UK
        string password
        string phone
        string role
        string telegram_chat_id
        string avatar
        boolean is_vip
        timestamp banned_until
    }

    Order {
        string order_id PK
        int user_id FK
        string status
        string payment_status
        float subtotal
        float discount_amount
        float grand_total
        string fulfillment_type
        string delivery_address
        date delivery_date
        json map_coordinates
    }

    Product {
        int id PK
        string name
        string price
        string category
        string brand
        string pc_part
        string caption
        json images
        int stock
        int low_stock_qty
    }

    Payment {
        int id PK
        string tran_id UK
        string qr_md5 UK
        string status
        float amount
        json response_data
    }
```

### Key Business Entities

| Entity | Table | Description |
|--------|-------|-------------|
| **User** | `users` | Customers with roles (`customer`, `vip`), avatar, Telegram connect |
| **Admin** | `admins` | System administrators (`superadmin`, `admin`) |
| **Staff** | `staff` | Staff members (editor, seller, delivery, developer) |
| **Product** | `products` | Computer parts & accessories with multi-image, stock, brand, PC part |
| **Order** | `orders` | Orders with fulfillment type, status workflow, delivery tracking |
| **OrderItem** | `order_items` | Individual items in each order with warranty date |
| **Payment** | `payments` | Payment records for ABA PayWay & Bakong KHQR |
| **Discount** | `discounts` | Coupon codes & badge discounts with usage tracking |
| **Banner** | `banners` | Promotional banners with product linking |
| **Video** | `videos` | Promotional videos with product linking |
| **AdminSetting** | `admin_settings` | System config & role permissions (54 preset entries) |
| **UserLocation** | `user_locations` | Saved delivery addresses with map coordinates |
| **DeliverySchedule** | `delivery_schedules` | Available delivery time slots |
| **Feedback** | `feedback` | Customer feedback submissions |
| **ChatMessage** | `chat_messages` | Customer support chat messages |
| **ChatSession** | `chat_sessions` | Customer support chat sessions |

---

## 🔌 API Endpoints

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad7" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#0D9488"/>
      <stop offset="100%" stop-color="#14B8A6"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="170" height="32" fill="url(#badgeGrad7)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🌐 Public Endpoints</text>
</svg>

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/login` | Customer login |
| `POST` | `/api/auth/register` | Customer registration |
| `POST` | `/api/auth/forgot-password` | Password reset request |
| `POST` | `/api/auth/reset-password` | Password reset |
| `POST` | `/api/auth/google` | Google OAuth callback |
| `POST` | `/api/auth/telegram` | Telegram auth callback |
| `GET` | `/api/products` | List products (category, search, filters, pagination) |
| `GET` | `/api/products/{id}` | Product detail |
| `GET` | `/api/categories` | List categories |
| `GET` | `/api/banners` | List active banners |
| `GET` | `/api/videos` | List promotional videos |
| `GET` | `/api/delivery-schedules` | Available delivery slots |
| `GET` | `/api/discounts/public` | Public discount badges |
| `POST` | `/api/apply-discount` | Validate & apply coupon code |
| `POST` | `/api/chat/message` | Submit support chat message |
| `POST` | `/api/payment/webhook` | ABA PayWay webhook |
| `POST` | `/api/telegram/bot-webhook` | Telegram Bot 2 webhook |

<svg viewBox="0 0 800 36" width="100%" height="36" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="badgeGrad8" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#CA8A04"/>
      <stop offset="100%" stop-color="#EAB308"/>
    </linearGradient>
  </defs>
  <rect rx="6" width="190" height="32" fill="url(#badgeGrad8)">
  </rect>
  <text x="16" y="22" font-size="14" fill="#fff" font-weight="bold">🔐 Protected (Auth Required)</text>
</svg>

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/logout` | Logout & revoke token |
| `GET` | `/api/auth/me` | Current user info |
| `GET` | `/api/orders` | List user orders |
| `POST` | `/api/orders` | Create order |
| `GET` | `/api/orders/{order}` | Order detail |
| `POST` | `/api/orders/{order}/cancel` | Cancel order |
| `DELETE` | `/api/orders/{order}` | Delete order |
| `POST` | `/api/orders/{order}/confirm-delivery` | Confirm delivery |
| `POST` | `/api/payment/generate-qr` | Generate Bakong KHQR |
| `POST` | `/api/payment/verify` | Verify payment |
| `GET` | `/api/discounts` | List discounts |
| `GET` | `/api/user/profile` | Get profile |
| `PUT` | `/api/user/profile` | Update profile |
| `POST` | `/api/user/avatar` | Upload avatar |
| `GET` | `/api/user/locations` | Saved addresses |
| `POST` | `/api/user/locations` | Save address |
| `GET` | `/api/telegram/status` | Telegram connection status |

### Staff & Admin Endpoints

| Method | Endpoint | Role |
|--------|----------|------|
| `GET` | `/api/admin/stats` | Dashboard stats | admin+ |
| `GET` | `/api/admin/users` | All users | admin+ |
| `POST` | `/api/products` | Create product | staff+ |
| `PUT` | `/api/products/{id}` | Update product | staff+ |
| `DELETE` | `/api/products/{id}` | Delete product | staff+ |
| `PUT` | `/api/orders/{order}/status` | Update status | staff+ |
| `POST` | `/api/orders/{order}/verify-payment` | Verify payment | staff+ |
| `CRUD` | `/api/discounts/*` | Discount management | staff+ |
| `GET` | `/api/dev/health` | System health | developer |
| `GET` | `/api/dev/logs` | Application logs | developer |
| `GET` | `/api/dev/env` | Environment vars | developer |

### Staff & Portals Auth

| Endpoint | Rate Limit | Description |
|----------|------------|-------------|
| `POST /api/staff/login` | 10/min | Staff portal login |
| `POST /api/dev/login` | 10/min | Developer portal login |

> Full API documentation available in [`routes/api.php`](tronmatix_backend/routes/api.php).

---

## 🚀 Setup Guide

### Prerequisites

| Requirement | Version |
|-------------|---------|
| **PHP** | 8.2+ |
| **Composer** | Latest |
| **Node.js** | 20+ |
| **PostgreSQL** | 14+ |

### 1. Clone & Install

```bash
# Clone the repository
git clone https://github.com/COPYProgrammer168/tronmatix-fullstack.git
cd tronmatix-fullstack

# --- Frontend ---
cd tronmatix_frontend
npm install
cp .env.example .env    # Edit with your API URL

# --- Backend ---
cd ../tronmatix_backend
composer install
cp .env.example .env    # Edit with your database credentials
php artisan key:generate
```

### 2. Database Setup

```bash
# Create PostgreSQL database
psql -U postgres -c "CREATE DATABASE tronmatix_db;"

# Run migrations & seeders
php artisan migrate --seed

# (Optional) Install Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 4. Run Development Servers

```bash
# From project root — runs both frontend & backend concurrently
npm run dev

# Or run individually:
# Terminal 1: Laravel API (http://localhost:8000)
cd tronmatix_backend && php artisan serve

# Terminal 2: React SPA (http://localhost:5173)
cd tronmatix_frontend && npm run dev
```

### 5. Production Build

```bash
# Frontend production build
cd tronmatix_frontend && npm run build

# Backend optimization
cd tronmatix_backend
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> ⚠️ **Vite Dev Proxy** automatically routes `/api/*` and `/storage/*` requests to the Laravel backend during development. In production, configure your web server (Nginx/Apache) to serve both.

---

## 📁 Project Structure

```
tronmatix-fullstack/
├── tronmatix_frontend/              # React SPA (Vite + Tailwind)
│   ├── src/
│   │   ├── components/              # Shared UI components
│   │   │   ├── checkout/            # Checkout steps
│   │   │   ├── dashboard/           # Dashboard state views
│   │   │   ├── guards/              # Route protection
│   │   │   ├── orders/              # Order components
│   │   │   ├── profile/             # User profile components
│   │   │   ├── Navbar.jsx           # Sticky nav with mega-dropdown
│   │   │   ├── Footer.jsx           # Site footer
│   │   │   ├── AuthModal.jsx        # Login/Register modal
│   │   │   ├── CartSlider.jsx       # Slide-in cart panel
│   │   │   ├── ProductCard.jsx      # Reusable product card
│   │   │   ├── SupportChat.jsx      # AI support chat
│   │   │   └── ...
│   │   ├── pages/                   # Route pages
│   │   │   ├── auth/                # Staff & Dev login pages
│   │   │   ├── HomePage.jsx         # Landing page
│   │   │   ├── CheckoutPage.jsx     # Multi-step checkout
│   │   │   ├── StaffDashboard.jsx   # Staff operations portal
│   │   │   └── ...
│   │   ├── context/                 # React Context providers
│   │   │   ├── AuthContext.jsx       # Auth state
│   │   │   ├── CartContext.jsx       # Cart state (localStorage)
│   │   │   ├── LanguageContext.jsx   # i18n (EN/KM)
│   │   │   ├── ThemeContext.jsx      # Dark/Light mode
│   │   │   └── ...
│   │   ├── locales/                 # Translation files
│   │   │   ├── en.json              # English (~330 keys)
│   │   │   └── km.json              # Khmer (~336 keys)
│   │   ├── lib/                     # API layer
│   │   ├── hooks/                   # Custom React hooks
│   │   ├── security/                # Enhanced auth module
│   │   ├── App.jsx                  # Router + providers
│   │   ├── main.jsx                 # Entry point
│   │   └── index.css                # Global styles + animations
│   ├── vite.config.js               # Vite config (proxy, plugins)
│   ├── tailwind.config.js           # Tailwind config
│   └── package.json
│
├── tronmatix_backend/               # Laravel API + Blade admin
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/             # REST API controllers (18 files)
│   │   │   │   ├── Auth/            # Staff & Dev auth
│   │   │   │   ├── Dashboard/       # Blade admin controllers (12 files)
│   │   │   │   └── ...
│   │   │   └── Middleware/          # 9 custom middleware classes
│   │   ├── Models/                  # 18 Eloquent models
│   │   ├── Services/                # Business logic layer
│   │   │   ├── TelegramService.php          # Bot 1: Admin alerts
│   │   │   ├── TelegramBotService.php       # Bot 2: Customer notifs
│   │   │   ├── TelegramUserService.php      # User Telegram messages
│   │   │   ├── ImageStorageService.php      # Image upload/storage
│   │   │   └── MetricComparisonService.php  # Trend analysis
│   │   └── Exports/                 # Excel/CSV export classes
│   ├── database/
│   │   ├── migrations/              # 28 migration files
│   │   └── seeders/                 # Database seeders
│   ├── resources/views/dashboard/   # Blade admin views (18 files)
│   ├── routes/
│   │   ├── api.php                  # REST API routes
│   │   ├── web.php                  # Web routes (dashboard)
│   │   └── ...
│   └── composer.json
│
├── package.json                     # Root orchestrator (concurrently)
├── PROJECT_ARCHITECTURE_EXPLANATION.txt
├── THESIS_PRESENTATION_QA.txt
├── SECURITY.md
└── README.md                        # ← You are here
```

---

## 🔒 Security

### Authentication Guards

```mermaid
graph TD
    subgraph "Authentication System"
        A[Web Guard] -->|Session| B[Customer Storefront]
        C[Admin Guard] -->|Session| D[Blade Dashboard]
        E[Sanctum Guard] -->|Token| F[React SPA API]
        E -->|Token| G[Mobile/3rd Party]
    end
    subgraph "Access Control"
        H[RoleMiddleware] --> I[superadmin]
        H --> J[admin]
        H --> K[editor]
        H --> L[seller]
        H --> M[delivery]
        H --> N[developer]
    end
```

| Layer | Mechanism |
|-------|-----------|
| **Password Hashing** | Bcrypt (12 rounds) |
| **Session Fingerprinting** | HMAC-SHA256 of User-Agent + IP + Accept-Language |
| **Session Timeout** | Absolute 8-hour timeout, rotation every 15 minutes |
| **Rate Limiting** | Login 5/min, Staff/Dev 10/min, API configurable |
| **CSRF** | Sanctum for SPA, built-in CSRF for Blade |
| **Security Headers** | HSTS, X-Frame-Options, X-Content-Type-Options |
| **Ban System** | `not_banned` middleware on all protected routes |
| **Payment** | HMAC-SHA512 signing, RSA encryption, idempotent webhooks |

### Role Permissions Matrix

The permission system defines **54 entries** (9 features × 6 roles):

| Feature | superadmin | admin | editor | seller | delivery | developer |
|---------|:----------:|:-----:|:------:|:------:|:--------:|:---------:|
| Settings | ✅ Locked | ✅ Locked | ❌ | ❌ | ❌ | ❌ |
| Staff | ✅ Locked | ✅ Locked | ❌ | ❌ | ❌ | ❌ |
| Orders Edit | ✅ Locked | ✅ Locked | ✅ | ✅ | ❌ | ❌ |
| Users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Products | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Discounts | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Banners | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Feedback | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |

---

## 🌐 Localization

The platform supports **English** and **Khmer (ភាសាខ្មែរ)** with full UI translation:

```
LanguageContext (React Context)
├── en.json (330 translation keys)
├── km.json (336 translation keys)
├── t(key, vars) → dot-path lookup with variable interpolation
├── toggle() → switch between languages
└── localStorage('tronmatix_lang') → persistent preference
```

- **Backend**: `SetLocale` middleware reads from session → cookie → header → default
- **Blade**: Laravel's `__()` helper with PHP translation strings
- **Fonts**: Khmer mode switches to `Kh_Jrung_Thom` (headings) and `Kdam Thmor Pro` (body) with adjusted line-height for diacritics
- **URL-based switching**: `/lang/{locale}` route

---

## 📸 Screenshots

> _Add your screenshots here. Replace the placeholder paths with actual images._

<!-- Placeholder for screenshots - replace URLs with your actual image paths -->
<details>
<summary><b>🖥️ Click to expand screenshots</b></summary>
<br />

| Storefront | Dashboard |
|:-----------:|:----------:|
| ![Homepage](https://via.placeholder.com/400x250/F97316/fff?text=Homepage) | ![Dashboard](https://via.placeholder.com/400x250/1F2937/F97316?text=Admin+Dashboard) |
| ![Checkout](https://via.placeholder.com/400x250/F97316/fff?text=Checkout) | ![Orders](https://via.placeholder.com/400x250/1F2937/F97316?text=Order+Management) |
| ![Products](https://via.placeholder.com/400x250/F97316/fff?text=Product+Listing) | ![Settings](https://via.placeholder.com/400x250/1F2937/F97316?text=Settings) |

</details>

---

## 📜 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<div align="center">

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="footerGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F97316" stop-opacity="0"/>
      <stop offset="50%" stop-color="#F97316" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#F97316" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#footerGrad)" stroke-width="2"/>
  <circle cx="400" cy="15" r="4" fill="#F97316">
  </circle>
</svg>

<br />

**Built with ⚡ for the Cambodian PC market**  
_Phnom Penh, Cambodia · 2026_

[![Made with Laravel](https://img.shields.io/badge/Made%20with-Laravel-F93208?style=flat-square&logo=laravel)](https://laravel.com)
[![Made with React](https://img.shields.io/badge/Made%20with-React-61DAFB?style=flat-square&logo=react)](https://react.dev)

</div>

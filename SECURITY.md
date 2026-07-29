<!-- ============================================================
     TRONMATIX COMPUTER — គោលការណ៍សន្តិសុខ និងស្ថាបត្យកម្ម
     ============================================================ -->

<div align="center">

<svg viewBox="0 0 800 160" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="secGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%"   stop-color="#DC2626" />
      <stop offset="50%"  stop-color="#EF4444" />
      <stop offset="100%" stop-color="#DC2626" />
    </linearGradient>
    <linearGradient id="secGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%"   stop-color="#DC2626" stop-opacity="0" />
      <stop offset="50%"  stop-color="#EF4444" stop-opacity="0.5" />
      <stop offset="100%" stop-color="#DC2626" stop-opacity="0" />
    </linearGradient>
    <filter id="shieldGlow">
      <feGaussianBlur stdDeviation="4" result="blur"/>
      <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
  </defs>
  <circle cx="130" cy="80" r="60" fill="url(#secGrad2)" opacity="0.4"></circle>
  <text x="130" y="100" font-size="64" text-anchor="middle" fill="url(#secGrad)" filter="url(#shieldGlow)">🛡️</text>
  <text x="280" y="75" font-size="44" font-family="'Segoe UI','Rajdhani',sans-serif"
        font-weight="900" fill="#DC2626" filter="url(#shieldGlow)">សន្តិសុខ</text>
  <text x="280" y="108" font-size="20" font-family="'Segoe UI','Rajdhani',sans-serif"
        fill="#9CA3AF" font-weight="600">Security Architecture &amp; Policy</text>
  <text x="280" y="132" font-size="14" font-family="'Segoe UI','Rajdhani',sans-serif"
        fill="#6B7280" font-style="italic">ការពារជាស្រទាប់ៗសម្រាប់ពាណិជ្ជកម្មអេឡិចត្រូនិកកម្ពុជា</text>
  <line x1="30" y1="150" x2="770" y2="150" stroke="url(#secGrad2)" stroke-width="2" stroke-linecap="round" />
  <circle cx="400" cy="150" r="4" fill="#EF4444"></circle>
</svg>

<br />

[![Security](https://img.shields.io/badge/សន្តិសុខ-Defense%20in%20Depth-DC2626?style=for-the-badge&logo=security)](https://laravel.com/docs/security)
[![Auth](https://img.shields.io/badge/Auth-Multi--Guard-4A5568?style=for-the-badge)](https://laravel.com/docs/authentication)
[![Sanctum](https://img.shields.io/badge/API%20Auth-Sanctum-4A5568?style=for-the-badge)](https://laravel.com/docs/sanctum)

</div>

---

## 📋 តារាងមាតិកា

1. [ទិដ្ឋភាពទូទៅ](#-ទិដ្ឋភាពទូទៅ)
2. [ស្ថាបត្យកម្មការផ្ទៀងផ្ទាត់](#-ស្ថាបត្យកម្មការផ្ទៀងផ្ទាត់)
3. [សន្តិសុខសម័យ (Session)](#-សន្តិសុខសម័យ-session)
4. [ការគ្រប់គ្រងការចូលប្រើតាមតួនាទី (RBAC)](#-ការគ្រប់គ្រងការចូលប្រើតាមតួនាទី-rbac)
5. [កំណត់អត្រា និងការពារការរំលោភបំពាន](#-កំណត់អត្រា-និងការពារការរំលោភបំពាន)
6. [សន្តិសុខ API](#-សន្តិសុខ-api)
7. [សន្តិសុខការទូទាត់](#-សន្តិសុខការទូទាត់)
8. [បឋមកថាសន្តិសុខ HTTP](#-បឋមកថាសន្តិសុខ-http)
9. [ការពារទិន្នន័យ](#-ការពារទិន្នន័យ)
10. [សន្តិសុខ Telegram](#-សន្តិសុខ-telegram)
11. [សន្តិសុខផតថលអ្នកអភិវឌ្ឍន៍](#-សន្តិសុខផតថលអ្នកអភិវឌ្ឍន៍)
12. [រាយការណ៍ពីភាពងាយរងគ្រោះ](#-រាយការណ៍ពីភាពងាយរងគ្រោះ)

---

## 📖 ទិដ្ឋភាពទូទៅ

**Tronmatix Computer** អនុវត្ត **ការពារជាស្រទាប់ៗ (defense-in-depth)** លើគ្រប់ស្រទាប់ទាំងអស់ — ការផ្ទៀងផ្ទាត់, ការគ្រប់គ្រងសម័យ (session), ការអនុញ្ញាត, ការត្រួតពិនិត្យសំណើ, និងការពារទិន្នន័យ។ ប្រព័ន្ធត្រូវបានរចនាឡើងដើម្បីការពារទិន្នន័យអតិថិជន, ប្រតិបត្តិការទូទាត់, និងការចូលប្រើរបស់អ្នកគ្រប់គ្រងសម្រាប់វេទិកាពាណិជ្ជកម្មអេឡិចត្រូនិកកម្ពុជា។

<!-- ─── Defense-in-Depth Layers Diagram ─────────────────────────── -->
<svg viewBox="0 0 800 200" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="layerGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669"/><stop offset="100%" stop-color="#10B981"/>
    </linearGradient>
    <linearGradient id="layerGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/><stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
    <linearGradient id="layerGrad3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#7C3AED"/><stop offset="100%" stop-color="#A78BFA"/>
    </linearGradient>
    <linearGradient id="lg1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#EF4444"/>
    </linearGradient>
    <linearGradient id="lg2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EA580C"/><stop offset="100%" stop-color="#F97316"/>
    </linearGradient>
    <filter id="shadow"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/></filter>
  </defs>

  <!-- Outer wall -->
  <rect x="20" y="10" width="760" height="180" rx="12" fill="none" stroke="rgba(239,68,68,0.3)" stroke-width="2" stroke-dasharray="8,4"/>
  <text x="40" y="35" font-size="11" fill="#EF4444" font-weight="bold" letter-spacing="1">⛔ ATTACK SURFACE</text>

  <!-- Layer 1: Network -->
  <rect x="40" y="48" width="720" height="30" rx="8" fill="url(#lg1)" opacity="0.9" filter="url(#shadow)"/>
  <text x="400" y="68" font-size="14" fill="#fff" text-anchor="middle" font-weight="bold">🌐 ស្រទាប់បណ្តាញ — Network Security</text>
  <text x="660" y="68" font-size="11" fill="#fca5a5" text-anchor="middle">HTTPS · HSTS · CORS · TrustProxies</text>

  <!-- Layer 2: Application Security -->
  <rect x="40" y="86" width="720" height="30" rx="8" fill="url(#lg2)" opacity="0.9" filter="url(#shadow)"/>
  <text x="400" y="106" font-size="14" fill="#fff" text-anchor="middle" font-weight="bold">🔐 ស្រទាប់កម្មវិធី — Application Security</text>
  <text x="660" y="106" font-size="11" fill="#fdba74" text-anchor="middle">Multi-Guard · RBAC · CSRF · Rate Limit</text>

  <!-- Layer 3: Session Security -->
  <rect x="40" y="124" width="720" height="30" rx="8" fill="url(#layerGrad2)" opacity="0.9" filter="url(#shadow)"/>
  <text x="400" y="144" font-size="14" fill="#fff" text-anchor="middle" font-weight="bold">🔒 ស្រទាប់សម័យ — Session Security</text>
  <text x="660" y="144" font-size="11" fill="#93c5fd" text-anchor="middle">Fingerprint · Rotation · Encryption · Timeout</text>

  <!-- Layer 4: Data -->
  <rect x="40" y="162" width="720" height="22" rx="6" fill="url(#layerGrad3)" opacity="0.9"/>
  <text x="400" y="177" font-size="13" fill="#fff" text-anchor="middle" font-weight="bold">🗄️ ស្រទាប់ទិន្នន័យ — Data Protection</text>
  <text x="660" y="177" font-size="10" fill="#c4b5fd" text-anchor="middle">Bcrypt · Eloquent ORM · Audit Logs</text>
</svg>

<br />

<!-- ─── Request Security Flow Diagram ───────────────────────────── -->
<svg viewBox="0 0 800 160" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#6B7280"/>
    </marker>
    <marker id="arrow-g" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#10B981"/>
    </marker>
    <marker id="arrow-r" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#EF4444"/>
    </marker>
    <linearGradient id="boxGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1F2937"/><stop offset="100%" stop-color="#111827"/>
    </linearGradient>
  </defs>

  <text x="400" y="18" font-size="13" fill="#9CA3AF" text-anchor="middle" font-weight="bold" letter-spacing="1">🛡️ REQUEST SECURITY FLOW — លំហូរការពារសំណើ</text>

  <!-- Box 1: Incoming Request -->
  <rect x="30" y="35" width="120" height="40" rx="8" fill="url(#boxGrad)" stroke="#6B7280" stroke-width="1.5"/>
  <text x="90" y="60" font-size="11" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🌍 សំណើចូល</text>

  <!-- Arrow 1 -->
  <line x1="150" y1="55" x2="200" y2="55" stroke="#6B7280" stroke-width="2" marker-end="url(#arrow)"/>

  <!-- Box 2: SecurityHeaders -->
  <rect x="205" y="35" width="110" height="40" rx="8" fill="url(#boxGrad)" stroke="#10B981" stroke-width="1.5"/>
  <text x="260" y="53" font-size="10" fill="#6EE7B7" text-anchor="middle" font-weight="bold">🔧 Security</text>
  <text x="260" y="67" font-size="10" fill="#6EE7B7" text-anchor="middle">Headers</text>

  <!-- Arrow 2 -->
  <line x1="315" y1="55" x2="365" y2="55" stroke="#6B7280" stroke-width="2" marker-end="url(#arrow)"/>

  <!-- Box 3: SecurityMiddleware -->
  <rect x="370" y="30" width="120" height="50" rx="8" fill="url(#boxGrad)" stroke="#3B82F6" stroke-width="1.5"/>
  <text x="430" y="48" font-size="10" fill="#93C5FD" text-anchor="middle" font-weight="bold">🔒 Security</text>
  <text x="430" y="62" font-size="10" fill="#93C5FD" text-anchor="middle">Middleware</text>

  <!-- Branch arrow up (granted) -->
  <line x1="490" y1="45" x2="540" y2="45" stroke="#10B981" stroke-width="2" marker-end="url(#arrow-g)"/>
  <text x="515" y="38" font-size="9" fill="#10B981" text-anchor="middle">✅</text>

  <!-- Branch arrow down (denied) -->
  <line x1="490" y1="65" x2="540" y2="105" stroke="#EF4444" stroke-width="2" marker-end="url(#arrow-r)"/>

  <!-- Box 4: Allowed -->
  <rect x="545" y="28" width="120" height="34" rx="8" fill="#064E3B" stroke="#10B981" stroke-width="1.5"/>
  <text x="605" y="50" font-size="11" fill="#6EE7B7" text-anchor="middle" font-weight="bold">✅ អនុញ្ញាត</text>

  <!-- Box 5: Terminated -->
  <rect x="545" y="92" width="120" height="34" rx="8" fill="#7F1D1D" stroke="#EF4444" stroke-width="1.5"/>
  <text x="605" y="114" font-size="11" fill="#FCA5A5" text-anchor="middle" font-weight="bold">❌ បដិសេធ</text>

  <!-- Checks list -->
  <rect x="30" y="90" width="165" height="55" rx="6" fill="rgba(255,255,255,0.03)" stroke="rgba(255,255,255,0.08)"/>
  <text x="112" y="106" font-size="9" fill="#9CA3AF" text-anchor="middle">🔍 ការត្រួតពិនិត្យ</text>
  <text x="40" y="120" font-size="9" fill="#6EE7B7">✓ Fingerprint match</text>
  <text x="40" y="133" font-size="9" fill="#6EE7B7">✓ Session not expired</text>
  <text x="40" y="140" font-size="9" fill="#6EE7B7" visibility="hidden">✓</text>
</svg>

<br />

<!-- ─── Security Stats Bar ──────────────────────────────────────── -->
<svg viewBox="0 0 800 60" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg1" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#1F2937"/><stop offset="100%" stop-color="#111827"/>
    </linearGradient>
    <linearGradient id="bg2" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#064E3B"/><stop offset="100%" stop-color="#022C22"/>
    </linearGradient>
    <linearGradient id="bg3" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#1E3A5F"/><stop offset="100%" stop-color="#0F1F3A"/>
    </linearGradient>
    <linearGradient id="bg4" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#4C1D95"/><stop offset="100%" stop-color="#2D0A5E"/>
    </linearGradient>
  </defs>
  <rect x="20" y="8" width="175" height="44" rx="10" fill="url(#bg1)" stroke="#374151"/>
  <text x="108" y="26" font-size="18" fill="#F97316" text-anchor="middle" font-weight="bold">៤</text>
  <text x="108" y="43" font-size="9" fill="#9CA3AF" text-anchor="middle">Auth Guards</text>

  <rect x="210" y="8" width="175" height="44" rx="10" fill="url(#bg2)" stroke="#065F46"/>
  <text x="298" y="26" font-size="18" fill="#34D399" text-anchor="middle" font-weight="bold">៧</text>
  <text x="298" y="43" font-size="9" fill="#9CA3AF" text-anchor="middle">Rate Limit Tiers</text>

  <rect x="400" y="8" width="175" height="44" rx="10" fill="url(#bg3)" stroke="#1E40AF"/>
  <text x="488" y="26" font-size="18" fill="#60A5FA" text-anchor="middle" font-weight="bold">៦</text>
  <text x="488" y="43" font-size="9" fill="#9CA3AF" text-anchor="middle">Security Middleware</text>

  <rect x="590" y="8" width="175" height="44" rx="10" fill="url(#bg4)" stroke="#5B21B6"/>
  <text x="678" y="26" font-size="18" fill="#A78BFA" text-anchor="middle" font-weight="bold">៩</text>
  <text x="678" y="43" font-size="9" fill="#9CA3AF" text-anchor="middle">Security Headers</text>
</svg>

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669" stop-opacity="0"/>
      <stop offset="50%" stop-color="#10B981" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#059669" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#10B981"/>
</svg>

## 🔐 ស្ថាបត្យកម្មការផ្ទៀងផ្ទាត់

### ប្រព័ន្ធ Multi-Guard

<!-- ─── Multi-Guard Architecture SVG ─────────────────────────────── -->
<svg viewBox="0 0 800 250" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="grd1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#10B981"/><stop offset="100%" stop-color="#059669"/>
    </linearGradient>
    <linearGradient id="grd2" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#2563EB"/>
    </linearGradient>
    <linearGradient id="grd3" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#F97316"/><stop offset="100%" stop-color="#EA580C"/>
    </linearGradient>
    <linearGradient id="grd4" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#A78BFA"/><stop offset="100%" stop-color="#7C3AED"/>
    </linearGradient>
    <filter id="sg"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/></filter>
  </defs>

  <text x="400" y="22" font-size="14" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🏗️ MULTI-GUARD AUTHENTICATION ARCHITECTURE</text>

  <!-- Users row -->
  <text x="50" y="55" font-size="11" fill="#9CA3AF" font-weight="bold">👥 USERS</text>
  <rect x="30" y="62" width="140" height="36" rx="18" fill="url(#grd1)" filter="url(#sg)"/>
  <text x="100" y="85" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">👤 អតិថិជន</text>
  <rect x="200" y="62" width="140" height="36" rx="18" fill="url(#grd2)" filter="url(#sg)"/>
  <text x="270" y="85" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">🔑 អ្នកគ្រប់គ្រង</text>
  <rect x="370" y="62" width="140" height="36" rx="18" fill="url(#grd3)" filter="url(#sg)"/>
  <text x="440" y="85" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">👔 បុគ្គលិក</text>
  <rect x="540" y="62" width="140" height="36" rx="18" fill="url(#grd4)" filter="url(#sg)"/>
  <text x="610" y="85" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">💻 អភិវឌ្ឍន៍</text>

  <!-- Down arrows -->
  <line x1="100" y1="98" x2="100" y2="125" stroke="#10B981" stroke-width="2" marker-end="url(#arrow-g)"/>
  <line x1="270" y1="98" x2="270" y2="125" stroke="#3B82F6" stroke-width="2" marker-end="url(#arrow)"/>
  <line x1="440" y1="98" x2="440" y2="125" stroke="#F97316" stroke-width="2" marker-end="url(#arrow)"/>
  <line x1="610" y1="98" x2="610" y2="125" stroke="#A78BFA" stroke-width="2" marker-end="url(#arrow)"/>

  <!-- Guards row -->
  <rect x="30" y="128" width="120" height="36" rx="6" fill="#1F2937" stroke="#10B981" stroke-width="1.5"/>
  <text x="90" y="145" font-size="10" fill="#6EE7B7" text-anchor="middle">web</text>
  <text x="90" y="157" font-size="9" fill="#9CA3AF" text-anchor="middle">Session</text>

  <rect x="180" y="128" width="120" height="36" rx="6" fill="#1F2937" stroke="#3B82F6" stroke-width="1.5"/>
  <text x="240" y="145" font-size="10" fill="#93C5FD" text-anchor="middle">admin</text>
  <text x="240" y="157" font-size="9" fill="#9CA3AF" text-anchor="middle">Session</text>

  <rect x="330" y="128" width="120" height="36" rx="6" fill="#1F2937" stroke="#F97316" stroke-width="1.5"/>
  <text x="390" y="145" font-size="10" fill="#FDBA74" text-anchor="middle">staff</text>
  <text x="390" y="157" font-size="9" fill="#9CA3AF" text-anchor="middle">Session</text>

  <rect x="480" y="128" width="120" height="36" rx="6" fill="#1F2937" stroke="#A78BFA" stroke-width="1.5"/>
  <text x="540" y="145" font-size="10" fill="#C4B5FD" text-anchor="middle">sanctum</text>
  <text x="540" y="157" font-size="9" fill="#9CA3AF" text-anchor="middle">Bearer Token</text>

  <!-- Arrow down -->
  <line x1="90" y1="164" x2="90" y2="185" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrow)"/>
  <line x1="240" y1="164" x2="240" y2="185" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrow)"/>
  <line x1="390" y1="164" x2="390" y2="185" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrow)"/>
  <line x1="540" y1="164" x2="540" y2="185" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Access row -->
  <rect x="20" y="188" width="750" height="48" rx="10" fill="rgba(255,255,255,0.03)" stroke="rgba(255,255,255,0.08)"/>
  <text x="400" y="206" font-size="11" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🔐 ACCESS LEVELS</text>
  <text x="100" y="224" font-size="10" fill="#6EE7B7" text-anchor="middle">Storefront</text>
  <text x="240" y="224" font-size="10" fill="#93C5FD" text-anchor="middle">Full Dashboard</text>
  <text x="390" y="224" font-size="10" fill="#FDBA74" text-anchor="middle">Role-based Dashboard</text>
  <text x="540" y="224" font-size="10" fill="#C4B5FD" text-anchor="middle">SPA + Dev Portal</text>
</svg>

ប្រព័ន្ធប្រើប្រាស់ **ការផ្ទៀងផ្ទាត់ ៤ ប្រភេទដាច់ដោយឡែក** ដើម្បីបំបែកកង្វល់ និងកំណត់ផលប៉ះពាល់៖

| Guard | ប្រភេទ | អ្នកប្រើ | គោលបំណង |
|-------|---------|-----------|------------|
| `web` | Session + Cookie | `User` | ការចូលរបស់អតិថិជន (storefront) |
| `admin` | Session + Cookie | `Admin` | ការចូល Dashboard តាម Blade |
| `staff` | Session + Cookie | `Staff` | ការចូលផតថលបុគ្គលិក |
| `sanctum` | Token (Bearer) | គ្រប់ម៉ូដែល | ការផ្ទៀងផ្ទាត់ API សម្រាប់ React SPA |

### សុវត្ថិភាពពាក្យសម្ងាត់

| ការកំណត់ | តម្លៃ |
|------------|--------|
| **ក្បួនដោះស្រាយ Hash** | Bcrypt |
| **Cost Factor** | 12 rounds |
| **ប្រវែងអប្បបរមា** | 8 តួអក្សរ |
| **កំណត់អត្រា** | ៥ ដងក្នុងមួយនាទី |
| **ការប្តូរពាក្យសម្ងាត់** | Token-based ផុតកំណត់តាម Fortify |

### ការផ្ទៀងផ្ទាត់តាមបណ្តាញសង្គម

- **Google OAuth 2.0**: ការផ្ទៀងផ្ទាត់ដោយគ្មានស្ថានភាព (stateless) ជាមួយ token exchange
- **Telegram Login**: ការផ្ទៀងផ្ទាត់តាម Telegram Login Widget

### ការរកឃើញអ្នកប្រើដែលត្រូវបានហាមឃាត់ (Ban Detection)

Middleware `not_banned` ដំណើរការលើគ្រប់ API route ដែលត្រូវការការផ្ទៀងផ្ទាត់៖

```php
// app/Http/Middleware/EnsureNotBanned.php
public function handle($request, $next) {
    if ($request->user() && $request->user()->isBanned()) {
        // API: ឆ្លើយតប 403 JSON
        // Web: ចាកចេញពីប្រព័ន្ធ និងបញ្ជូនទៅកាន់ទំព័រចូល
    }
    return $next($request);
}
```

### ការតាមដានសកម្មភាព (Audit Trail)

រាល់សកម្មភាពសំខាន់ៗត្រូវបានកត់ត្រានៅក្នុងតារាង `activity_logs`៖

| សកម្មភាព | ព័ត៌មានលម្អិត |
|-----------|------------------|
| **ចូលប្រព័ន្ធ (Login)** | ជោគជ័យ, បរាជ័យ, rate-limited — កត់ត្រា IP, User-Agent, guard |
| **ប្តូរស្ថានភាពការបញ្ជាទិញ** | ស្ថានភាពចាស់ → ថ្មី, អ្នកកែប្រែ, IP |
| **ការទូទាត់** | បញ្ជាក់ការទូទាត់, បញ្ជាក់ការដឹកជញ្ជូន |
| **គ្រប់គ្រងបុគ្គលិក** | អញ្ជើញ, ប្តូរតួនាទី, បើក/បិទគណនី |

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#3B82F6" stop-opacity="0"/>
      <stop offset="50%" stop-color="#2563EB" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#3B82F6" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad2)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#2563EB"/>
</svg>

## 🔒 សន្តិសុខសម័យ (Session)

### ការបោះពុម្ពស្នាមម្រាមដៃសម័យ (Session Fingerprinting)

<!-- ─── Session Fingerprinting Flow SVG ─────────────────────────── -->
<svg viewBox="0 0 800 280" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <marker id="arr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#6B7280"/>
    </marker>
    <marker id="arr-g" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#10B981"/>
    </marker>
    <marker id="arr-r" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#EF4444"/>
    </marker>
    <linearGradient id="fg1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1E3A5F"/><stop offset="100%" stop-color="#0F1F3A"/>
    </linearGradient>
  </defs>

  <text x="400" y="22" font-size="13" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🔎 SESSION FINGERPRINTING — លំហូរការផ្ទៀងផ្ទាត់ស្នាមម្រាមដៃ</text>

  <!-- Step 1: Login -->
  <rect x="30" y="38" width="160" height="44" rx="8" fill="url(#fg1)" stroke="#3B82F6" stroke-width="1.5"/>
  <text x="110" y="55" font-size="10" fill="#93C5FD" text-anchor="middle" font-weight="bold">១. ចូលប្រព័ន្ធ</text>
  <text x="110" y="70" font-size="9" fill="#6B7280" text-anchor="middle">Login</text>

  <line x1="190" y1="60" x2="240" y2="60" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arr)"/>

  <!-- Step 2: Capture fingerprint -->
  <rect x="245" y="38" width="180" height="44" rx="8" fill="url(#fg1)" stroke="#F97316" stroke-width="1.5"/>
  <text x="335" y="53" font-size="10" fill="#FDBA74" text-anchor="middle" font-weight="bold">២. ប្រមូលស្នាមម្រាមដៃ</text>
  <text x="335" y="70" font-size="9" fill="#6B7280" text-anchor="middle">UA + IP + Accept-Language</text>

  <line x1="425" y1="60" x2="475" y2="60" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arr)"/>

  <!-- Step 3: HMAC -->
  <rect x="480" y="38" width="160" height="44" rx="8" fill="url(#fg1)" stroke="#A78BFA" stroke-width="1.5"/>
  <text x="560" y="53" font-size="10" fill="#C4B5FD" text-anchor="middle" font-weight="bold">៣. HMAC-SHA256</text>
  <text x="560" y="70" font-size="9" fill="#6B7280" text-anchor="middle">ជាមួយ APP_KEY</text>

  <line x1="640" y1="60" x2="690" y2="60" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arr)"/>

  <!-- Step 4: Store -->
  <rect x="695" y="38" width="90" height="44" rx="8" fill="url(#fg1)" stroke="#10B981" stroke-width="1.5"/>
  <text x="740" y="60" font-size="10" fill="#6EE7B7" text-anchor="middle" font-weight="bold">💾 រក្សាទុក</text>

  <!-- Branch: every request -->
  <line x1="400" y1="82" x2="400" y2="120" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arr)"/>
  <text x="410" y="105" font-size="9" fill="#9CA3AF">រាល់សំណើ →</text>

  <!-- Step 5: Verify -->
  <rect x="245" y="123" width="310" height="44" rx="8" fill="url(#fg1)" stroke="#3B82F6" stroke-width="1.5"/>
  <text x="400" y="138" font-size="10" fill="#93C5FD" text-anchor="middle" font-weight="bold">៥. ផ្ទៀងផ្ទាត់ — hash_equals(ស្នាមដើម, ស្នាមបច្ចុប្បន្ន)</text>
  <text x="400" y="155" font-size="9" fill="#6B7280" text-anchor="middle">Timing-attack safe comparison</text>

  <!-- Branch: match -->
  <line x1="555" y1="140" x2="620" y2="140" stroke="#10B981" stroke-width="2" marker-end="url(#arr-g)"/>
  <rect x="625" y="125" width="110" height="30" rx="6" fill="#064E3B" stroke="#10B981" stroke-width="1"/>
  <text x="680" y="144" font-size="10" fill="#6EE7B7" text-anchor="middle" font-weight="bold">✅ អនុញ្ញាត</text>

  <!-- Branch: mismatch -->
  <line x1="400" y1="167" x2="400" y2="195" stroke="#EF4444" stroke-width="1.5" marker-end="url(#arr-r)"/>
  <rect x="320" y="198" width="160" height="34" rx="6" fill="#7F1D1D" stroke="#EF4444" stroke-width="1"/>
  <text x="400" y="214" font-size="10" fill="#FCA5A5" text-anchor="middle" font-weight="bold">❌ បញ្ចប់សម័យ</text>
  <text x="400" y="226" font-size="9" fill="#9CA3AF" text-anchor="middle">Logout + Redirect</text>

  <!-- Additional checks -->
  <rect x="30" y="120" width="190" height="110" rx="8" fill="rgba(255,255,255,0.02)" stroke="rgba(255,255,255,0.06)"/>
  <text x="125" y="138" font-size="10" fill="#9CA3AF" text-anchor="middle" font-weight="bold">ការត្រួតពិនិត្យផ្សេងទៀត</text>
  <text x="40" y="156" font-size="9" fill="#6EE7B7">✓ Rotation interval (15min)</text>
  <text x="40" y="172" font-size="9" fill="#6EE7B7">✓ Absolute timeout (8h)</text>
  <text x="40" y="188" font-size="9" fill="#6EE7B7">✓ AES-256-CBC encryption</text>
  <text x="40" y="204" font-size="9" fill="#6EE7B7">✓ CSRF token validation</text>
  <text x="40" y="220" font-size="9" fill="#6EE7B7">✓ Ban detection</text>
</svg>

`SecurityMiddleware` អនុវត្ត **ការពារការលួចសម័យ (session hijacking)** តាមរយៈការភ្ជាប់ស្នាមម្រាមដៃ (fingerprint binding)៖

```
រាល់ពេលចូលប្រព័ន្ធ → ប្រព័ន្ធគណនា HMAC-SHA256 នៃ 
(User-Agent + IP + Accept-Language) ដោយប្រើ APP_KEY
→ រក្សាទុកក្នុងសម័យ

រាល់សំណើបន្ទាប់ → គណនាស្នាមម្រាមដៃបច្ចុប្បន្ន
→ ប្រៀបធៀបជាមួយស្នាមម្រាមដៃដែលបានរក្សាទុក
→ ប្រើ hash_equals() ដើម្បីការពារការវាយប្រហារ timing attack

បើមិនដូចគ្នា → បញ្ចប់សម័យ និងបញ្ជូនទៅកាន់ទំព័រចូល
```

### ការកំណត់ពេលវេលាផុតកំណត់ និងការបង្វិលសម័យ

| មុខងារ | ការកំណត់ | ទីតាំង |
|----------|------------|----------|
| **រយៈពេលផុតកំណត់ដាច់ខាត** | ៨ ម៉ោង (កំណត់រចនាសម្ព័ន្ធបាន) | `SecurityMiddleware` |
| **ការបង្វិលលេខសម្គាល់សម័យ** | រៀងរាល់ ១៥ នាទី | `SecurityMiddleware` |
| **រយៈពេលផុតកំណត់ពេលអសកម្ម** | ១២០ នាទី (កំណត់រចនាសម្ព័ន្ធបាន) | `config/session.php` |

### ការអ៊ិនគ្រីបទិន្នន័យសម័យ

ទិន្នន័យសម័យទាំងអស់ត្រូវបាន **អ៊ិនគ្រីប AES-256-CBC** មុនពេលរក្សាទុកក្នុងមូលដ្ឋានទិន្នន័យ៖

```php
// config/session.php
'encrypt' => true, // អ៊ិនគ្រីបទិន្នន័យសម័យទាំងអស់ (AES-256-CBC)
```

### ការកំណត់ខូគីសម័យ

| ការកំណត់ | តម្លៃ |
|------------|--------|
| ឈ្មោះខូគី | `tronmatix_session` (លាក់ឈ្មោះដើម Laravel) |
| HttpOnly | ✅ ពិត — JavaScript អានខូគីមិនបាន |
| SameSite | `lax` — ការពារ CSRS |
| Secure | ✅ ពិត — តម្រូវឱ្យមាន HTTPS |
| Partitioned | ទេ |

### ការការពារ CSRF

| ចំណុចប្រទាក់ | យន្តការ | ព័ត៌មាន |
|----------------|-----------|-----------|
| **Blade Dashboard** | `@csrf` របស់ Laravel | ដោយស្វ័យប្រវត្តិលើគ្រប់ POST/PUT/DELETE |
| **React SPA (API)** | Sanctum SPA authentication | ខូគី `X-XSRF-TOKEN` កំណត់ដោយ Sanctum |
| **React SPA (Token)** | មិនត្រូវការ CSRF | Token-based auth ការពារ CSRF ដោយធម្មជាតិ |

### សុពលភាព Token API

Token ទាំងអស់ផុតកំណត់បន្ទាប់ពី **៣០ ថ្ងៃ** ដើម្បីបង្ខំឱ្យមានការផ្ទៀងផ្ទាត់ឡើងវិញជាទៀងទាត់៖

```php
// config/sanctum.php
'expiration' => 43200, // 30 ថ្ងៃគិតជានាទី
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#7C3AED" stop-opacity="0"/>
      <stop offset="50%" stop-color="#A78BFA" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#7C3AED" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad3)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#A78BFA"/>
</svg>

## 👮 ការគ្រប់គ្រងការចូលប្រើតាមតួនាទី (RBAC)

### តួនាទី

<!-- ─── RBAC Roles Pyramid SVG ──────────────────────────────────── -->
<svg viewBox="0 0 800 200" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="py1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#EF4444"/>
    </linearGradient>
    <linearGradient id="py2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EA580C"/><stop offset="100%" stop-color="#F97316"/>
    </linearGradient>
    <linearGradient id="py3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#D97706"/><stop offset="100%" stop-color="#F59E0B"/>
    </linearGradient>
    <linearGradient id="py4" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669"/><stop offset="100%" stop-color="#10B981"/>
    </linearGradient>
    <linearGradient id="py5" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/><stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
    <linearGradient id="py6" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#6D28D9"/><stop offset="100%" stop-color="#8B5CF6"/>
    </linearGradient>
  </defs>

  <!-- Pyramid layers -->
  <!-- superadmin -->
  <polygon points="400,30 520,65 280,65" fill="url(#py1)" opacity="0.9" stroke="#DC2626" stroke-width="1"/>
  <text x="400" y="55" font-size="13" fill="#fff" text-anchor="middle" font-weight="bold">superadmin</text>
  <!-- admin -->
  <polygon points="400,65 560,100 240,100" fill="url(#py2)" opacity="0.85" stroke="#EA580C" stroke-width="1"/>
  <text x="400" y="90" font-size="12" fill="#fff" text-anchor="middle" font-weight="bold">admin</text>
  <!-- editor -->
  <polygon points="400,100 600,135 200,135" fill="url(#py3)" opacity="0.8" stroke="#D97706" stroke-width="1"/>
  <text x="400" y="125" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">editor · seller</text>
  <!-- delivery + developer -->
  <polygon points="400,135 640,170 160,170" fill="url(#py4)" opacity="0.75" stroke="#059669" stroke-width="1"/>
  <text x="400" y="160" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">delivery</text>

  <!-- Right labels -->
  <text x="660" y="55" font-size="10" fill="#EF4444" font-weight="bold">🔴 ពេញលេញ</text>
  <text x="660" y="90" font-size="10" fill="#F97316" font-weight="bold">🟠 ខ្ពស់</text>
  <text x="660" y="125" font-size="10" fill="#F59E0B" font-weight="bold">🟡 មធ្យម</text>
  <text x="660" y="155" font-size="10" fill="#10B981" font-weight="bold">🟢 ទាប</text>
  <text x="660" y="178" font-size="10" fill="#8B5CF6" font-weight="bold">🟣 ពិសេស</text>

  <!-- Left: developer -->
  <rect x="20" y="140" width="100" height="28" rx="12" fill="url(#py6)" opacity="0.8"/>
  <text x="70" y="159" font-size="10" fill="#fff" text-anchor="middle" font-weight="bold">developer 🟣</text>

  <!-- Bottom bar -->
  <rect x="160" y="178" width="480" height="1" fill="rgba(255,255,255,0.1)"/>
  <text x="400" y="192" font-size="9" fill="#6B7280" text-anchor="middle">↑ កម្រិតសិទ្ធិថយចុះតាមលំដាប់ពីលើចុះក្រោម</text>
</svg>

មាន **៦ តួនាទី** ដែលមានការអនុញ្ញាតខុសៗគ្នា៖

| តួនាទី | កម្រិត | សិទ្ធិចូល Dashboard |
|----------|---------|----------------------|
| **superadmin** | 🔴 ខ្ពស់បំផុត | ពេញលេញ Blade + React |
| **admin** | 🟠 ខ្ពស់ | ពេញលេញ Blade + React |
| **editor** | 🟡 មធ្យម | ផលិតផល, បញ្ចុះតម្លៃ, បដា, របាយការណ៍ |
| **seller** | 🟢 មធ្យម | ផលិតផល, ការបញ្ជាទិញ, របាយការណ៍ |
| **delivery** | 🔵 ទាប | ប្តូរស្ថានភាពការបញ្ជាទិញ, បញ្ជាក់ការដឹកជញ្ជូន |
| **developer** | 🟣 ពិសេស | សុខភាពប្រព័ន្ធ, logs, environment |

### តារាងសិទ្ធិ

| មុខងារ | superadmin | admin | editor | seller | delivery | developer |
|----------|:----------:|:-----:|:------:|:------:|:--------:|:---------:|
| **ការកំណត់** | ✅ ចាក់សោ | ✅ ចាក់សោ | ❌ | ❌ | ❌ | ❌ |
| **បុគ្គលិក** | ✅ ចាក់សោ | ✅ ចាក់សោ | ❌ | ❌ | ❌ | ❌ |
| **កែប្រែការបញ្ជាទិញ** | ✅ ចាក់សោ | ✅ ចាក់សោ | ✅ | ✅ | ❌ | ❌ |
| **អ្នកប្រើ** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **ផលិតផល** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **បញ្ចុះតម្លៃ** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **បដា** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **របាយការណ៍** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **មតិអតិថិជន** | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |

### លំហូរការត្រួតពិនិត្យសិទ្ធិ

<!-- ─── Permission Check Flow SVG ───────────────────────────────── -->
<svg viewBox="0 0 800 200" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <marker id="arrb" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#6B7280"/>
    </marker>
    <linearGradient id="bgx" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1F2937"/><stop offset="100%" stop-color="#111827"/>
    </linearGradient>
  </defs>

  <!-- Box 1 -->
  <rect x="20" y="20" width="130" height="40" rx="6" fill="url(#bgx)" stroke="#6B7280" stroke-width="1"/>
  <text x="85" y="45" font-size="11" fill="#D1D5DB" text-anchor="middle" font-weight="bold">📨 សំណើចូល</text>

  <line x1="150" y1="40" x2="200" y2="40" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrb)"/>

  <!-- Box 2 -->
  <rect x="205" y="20" width="130" height="40" rx="6" fill="url(#bgx)" stroke="#F59E0B" stroke-width="1"/>
  <text x="270" y="38" font-size="10" fill="#FDE68A" text-anchor="middle" font-weight="bold">🔍 កំណត់តួនាទី</text>
  <text x="270" y="52" font-size="9" fill="#6B7280" text-anchor="middle">$_role</text>

  <line x1="335" y1="40" x2="385" y2="40" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrb)"/>

  <!-- Decision: superadmin? -->
  <rect x="390" y="15" width="150" height="50" rx="6" fill="url(#bgx)" stroke="#EF4444" stroke-width="1.5"/>
  <text x="465" y="35" font-size="10" fill="#FCA5A5" text-anchor="middle" font-weight="bold">❓ superadmin?</text>
  <text x="465" y="52" font-size="9" fill="#6B7280" text-anchor="middle">Always granted (bypass)</text>

  <!-- Yes branch -->
  <line x1="540" y1="30" x2="590" y2="30" stroke="#10B981" stroke-width="2"/>
  <polygon points="580,25 590,30 580,35" fill="#10B981"/>
  <rect x="595" y="15" width="110" height="30" rx="6" fill="#064E3B" stroke="#10B981" stroke-width="1"/>
  <text x="650" y="35" font-size="10" fill="#6EE7B7" text-anchor="middle" font-weight="bold">✅ អនុញ្ញាត</text>

  <!-- No branch going down -->
  <line x1="465" y1="65" x2="465" y2="95" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrb)"/>

  <!-- Check DB -->
  <rect x="390" y="100" width="150" height="40" rx="6" fill="url(#bgx)" stroke="#3B82F6" stroke-width="1"/>
  <text x="465" y="118" font-size="10" fill="#93C5FD" text-anchor="middle" font-weight="bold">📂 រកមើល admin_settings</text>
  <text x="465" y="132" font-size="9" fill="#6B7280" text-anchor="middle">perm_{role}_{feature}</text>

  <!-- Branch: found? -->
  <line x1="540" y1="120" x2="590" y2="120" stroke="#10B981" stroke-width="1.5"/>
  <polygon points="580,115 590,120 580,125" fill="#10B981"/>
  <text x="565" y="112" font-size="9" fill="#10B981" text-anchor="middle">បាទ</text>
  <rect x="595" y="105" width="110" height="30" rx="6" fill="#064E3B" stroke="#10B981" stroke-width="1"/>
  <text x="650" y="125" font-size="10" fill="#6EE7B7" text-anchor="middle" font-weight="bold">✅ អនុញ្ញាត</text>

  <!-- No found -->
  <line x1="465" y1="140" x2="465" y2="165" stroke="#6B7280" stroke-width="1.5" marker-end="url(#arrb)"/>
  <rect x="390" y="168" width="150" height="28" rx="6" fill="url(#bgx)" stroke="#A78BFA" stroke-width="1"/>
  <text x="465" y="186" font-size="9" fill="#C4B5FD" text-anchor="middle">Fallback → getDefaults()</text>
</svg>

```
សំណើ → RoleMiddleware (API) / _permission_check.blade.php (Web)
  ↓
១. កំណត់តួនាទីអ្នកប្រើ
  ↓
២. superadmin? → អនុញ្ញាតជានិច្ច
  ↓
៣. រកមើល perm_{role}_{feature} ក្នុងតារាង admin_settings
  ↓
៤. រកមិនឃើញ? → ប្រើតម្លៃលំនាំដើមពី AdminSetting::getDefaults()
  ↓
៥. គ្មានសិទ្ធិ? → 403 JSON (API) ឬ access-denied partial (Blade)
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad4" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F59E0B" stop-opacity="0"/>
      <stop offset="50%" stop-color="#EAB308" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#F59E0B" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad4)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#EAB308"/>
</svg>

## 🚦 កំណត់អត្រា និងការពារការរំលោភបំពាន

### កម្រិតអត្រាបច្ចុប្បន្ន

<!-- ─── Rate Limiting Tiers SVG ─────────────────────────────────── -->
<svg viewBox="0 0 800 230" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="rd1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#EF4444"/>
    </linearGradient>
    <linearGradient id="rd2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EA580C"/><stop offset="100%" stop-color="#F97316"/>
    </linearGradient>
    <linearGradient id="rd3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#D97706"/><stop offset="100%" stop-color="#F59E0B"/>
    </linearGradient>
    <linearGradient id="rd4" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/><stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
    <linearGradient id="rd5" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669"/><stop offset="100%" stop-color="#10B981"/>
    </linearGradient>
  </defs>

  <text x="400" y="22" font-size="13" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🚦 RATE LIMITING TIERS — កម្រិតកំណត់អត្រា</text>

  <!-- Bar 1: Auth login -->
  <text x="50" y="52" font-size="11" fill="#FCA5A5" text-anchor="end" font-weight="bold">🔑 Auth</text>
  <rect x="65" y="38" width="60" height="22" rx="4" fill="url(#rd1)"/>
  <text x="95" y="53" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">៥/ន</text>
  <text x="135" y="53" font-size="10" fill="#9CA3AF">Login attempts</text>

  <!-- Bar 2: Staff/Dev login -->
  <text x="50" y="82" font-size="11" fill="#FDBA74" text-anchor="end" font-weight="bold">👔 Staff</text>
  <rect x="65" y="68" width="100" height="22" rx="4" fill="url(#rd2)"/>
  <text x="115" y="83" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">១០/ន</text>
  <text x="175" y="83" font-size="10" fill="#9CA3AF">Staff/Dev login</text>

  <!-- Bar 3: Payment -->
  <text x="50" y="112" font-size="11" fill="#FDE68A" text-anchor="end" font-weight="bold">💳 Pay</text>
  <rect x="65" y="98" width="100" height="22" rx="4" fill="url(#rd3)"/>
  <text x="115" y="113" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">១០/ន</text>
  <text x="175" y="113" font-size="10" fill="#9CA3AF">Generate QR / Verify payment</text>

  <!-- Bar 4: Orders -->
  <text x="50" y="142" font-size="11" fill="#93C5FD" text-anchor="end" font-weight="bold">📦 Order</text>
  <rect x="65" y="128" width="180" height="22" rx="4" fill="url(#rd4)"/>
  <text x="155" y="143" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">២០/ន</text>
  <text x="255" y="143" font-size="10" fill="#9CA3AF">Create / Cancel / Delete orders</text>

  <!-- Bar 5: General API -->
  <text x="50" y="172" font-size="11" fill="#6EE7B7" text-anchor="end" font-weight="bold">🔌 API</text>
  <rect x="65" y="158" width="220" height="22" rx="4" fill="url(#rd5)"/>
  <text x="175" y="173" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">៦០/ន</text>
  <text x="295" y="173" font-size="10" fill="#9CA3AF">General API (base throttle)</text>

  <!-- Legend -->
  <rect x="520" y="38" width="250" height="145" rx="8" fill="rgba(255,255,255,0.02)" stroke="rgba(255,255,255,0.06)"/>
  <text x="645" y="55" font-size="10" fill="#9CA3AF" text-anchor="middle" font-weight="bold">📊 របារបង្ហាញពីអត្រា</text>
  <line x1="535" y1="72" x2="575" y2="72" stroke="#EF4444" stroke-width="4" stroke-linecap="round"/>
  <text x="585" y="76" font-size="9" fill="#9CA3AF">5/min — Strictest</text>
  <line x1="535" y1="94" x2="575" y2="94" stroke="#F97316" stroke-width="4" stroke-linecap="round"/>
  <text x="585" y="98" font-size="9" fill="#9CA3AF">10/min — Tight</text>
  <line x1="535" y1="116" x2="575" y2="116" stroke="#F59E0B" stroke-width="4" stroke-linecap="round"/>
  <text x="585" y="120" font-size="9" fill="#9CA3AF">10/min — Payment</text>
  <line x1="535" y1="138" x2="575" y2="138" stroke="#3B82F6" stroke-width="4" stroke-linecap="round"/>
  <text x="585" y="142" font-size="9" fill="#9CA3AF">20/min — Orders</text>
  <line x1="535" y1="160" x2="575" y2="160" stroke="#10B981" stroke-width="4" stroke-linecap="round"/>
  <text x="585" y="164" font-size="9" fill="#9CA3AF">60/min — Standard</text>
  <text x="645" y="178" font-size="8" fill="#6B7280" text-anchor="middle" font-style="italic">អត្រាក្នុងមួយនាទីក្នុងមួយអ្នកប្រើ/IP</text>
</svg>

| ក្រុម Endpoint | កំណត់ | រយៈពេល | វិសាលភាព |
|-----------------|--------|----------|------------|
| **ចូលប្រព័ន្ធអតិថិជន** | ៥ ដង | ១ នាទី | Per IP |
| **ចូលប្រព័ន្ធបុគ្គលិក/អ្នកអភិវឌ្ឍន៍** | ១០ ដង | ១ នាទី | Per IP |
| **API ទូទៅ** (ការពារទាំងអស់) | ៦០ ដង | ១ នាទី | Per User |
| **ការបញ្ជាទិញ** (បង្កើត/លុប/បោះបង់) | ២០ ដង | ១ នាទី | Per User |
| **ការទូទាត់** (generate QR, verify) | ១០ ដង | ១ នាទី | Per User |
| **ការជជែក (Chat)** (អ្នកប្រើដែលបានចូល) | ១៥ សារ | ១ នាទី | Per User |
| **ការជជែក (Chat)** (ភ្ញៀវ) | ៥ សារ | ១ នាទី | Per IP |
| **2FA** | ៥ ដង | ១ នាទី | Per Session |
| **ប្តូរពាក្យសម្ងាត់** | ៦ ដង | ១ នាទី | Per User |

### ការអនុវត្ត

```php
// កំណត់អត្រា API ទូទៅ — 60 ដង/នាទី
Route::middleware(['auth:sanctum', 'not_banned', 'throttle:60,1'])->group(function () {
    // ...
});

// កំណត់អត្រាការបញ្ជាទិញ — 20 ដង/នាទី
Route::middleware('throttle:20,1')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

// កំណត់អត្រាការទូទាត់ — 10 ដង/នាទី
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/payment/generate-qr', [GenerateKhqrController::class, 'generate']);
    Route::post('/payment/verify', [CheckPaymentController::class, 'verify']);
});
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad5" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EC4899" stop-opacity="0"/>
      <stop offset="50%" stop-color="#F472B6" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#EC4899" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad5)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#F472B6"/>
</svg>

## 🔌 សន្តិសុខ API

### Sanctum Token Auth

- **ការបង្កើត Token**: Personal Access Tokens បង្កើតពេលចូលប្រព័ន្ធ
- **ការរក្សាទុក Token**: តារាង `personal_access_tokens` ជាមួយ token ដែលត្រូវបាន hashed
- **ការផុតកំណត់**: ៣០ ថ្ងៃ — អ្នកប្រើត្រូវចូលប្រព័ន្ធម្តងទៀតដើម្បីទទួល token ថ្មី
- **ការដកហូតភ្លាមៗ**: លុប token ចេញពីមូលដ្ឋានទិន្នន័យ — មានប្រសិទ្ធភាពភ្លាមៗ
- **Token តែមួយក្នុងពេលតែមួយ**: រាល់ពេលចូលប្រព័ន្ធ token ចាស់ត្រូវបានលុបចោល

### ការត្រួតពិនិត្យសំណើ (Input Validation)

រាល់ការបញ្ចូល API ត្រូវបានត្រួតពិនិត្យតាមរយៈ Laravel Form Requests ឬ Validator:

```php
// ឧទាហរណ៍ពី AuthController
$request->validate([
    'email'    => 'required|email',
    'password' => 'required|string|min:8',
]);
```

### CORS Configuration

```php
// config/cors.php
'paths'          => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
'allowed_origins' => [
    'https://tronmatix-frontend.onrender.com',
    env('FRONTEND_URL', 'http://localhost:5173'),
],
'supports_credentials' => true,
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad6" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#10B981" stop-opacity="0"/>
      <stop offset="50%" stop-color="#34D399" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#10B981" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad6)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#34D399"/>
</svg>

## 💳 សន្តិសុខការទូទាត់

### Bakong KHQR (ការទូទាត់តាម QR កម្ពុជា)

| វិធានការ | ការអនុវត្ត |
|------------|-------------|
| **ការបង្កើត QR** | Server-side តាមរយៈ `pisethchhun/bakong-khqr-php` |
| **ការផុតកំណត់ QR** | ផ្អែកលើ Timestamp, TTL កំណត់រចនាសម្ព័ន្ធបាន |
| **ការពារការប្រើឡើងវិញ** | `qr_md5` unique index ការពារ replay attack |
| **លេខសម្គាល់ប្រតិបត្តិការ** | `tran_id` តែមួយគត់សម្រាប់រាល់ការទូទាត់ |
| **Webhook Idempotent** | ពិនិត្យប្រតិបត្តិការដែលមានស្រាប់មុនពេលដំណើរការ |

### ABA PayWay

| វិធានការ | ការអនុវត្ត |
|------------|-------------|
| **ហត្ថលេខាប្រតិបត្តិការ** | HMAC-SHA512 ជាមួយ merchant secret |
| **ការត្រួតពិនិត្យ Webhook** | ផ្ទៀងផ្ទាត់ហត្ថលេខាតាម ABA spec |
| **គ្មានការរក្សាទុកកាត** | ទិន្នន័យកាតឥណទានត្រូវបានគ្រប់គ្រងដោយ ABA (PCI compliant) |

### វដ្តជីវិតស្ថានភាពការទូទាត់

<!-- ─── Payment Lifecycle SVG ───────────────────────────────────── -->
<svg viewBox="0 0 800 150" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <marker id="arrp" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto">
      <path d="M0,0 L10,5 L0,10 Z" fill="#6B7280"/>
    </marker>
  </defs>
  <text x="400" y="22" font-size="13" fill="#D1D5DB" text-anchor="middle" font-weight="bold">💳 PAYMENT STATUS LIFECYCLE — វដ្តជីវិតស្ថានភាពការទូទាត់</text>

  <!-- Pending -->
  <rect x="30" y="50" width="100" height="34" rx="17" fill="#78350F" stroke="#F59E0B" stroke-width="1.5"/>
  <text x="80" y="72" font-size="11" fill="#FDE68A" text-anchor="middle" font-weight="bold">⏳ Pending</text>

  <!-- Arrow right -->
  <line x1="130" y1="67" x2="170" y2="67" stroke="#10B981" stroke-width="2" marker-end="url(#arrp)"/>
  <text x="150" y="62" font-size="9" fill="#10B981" text-anchor="middle">webhook</text>

  <!-- Paid -->
  <rect x="175" y="50" width="100" height="34" rx="17" fill="#064E3B" stroke="#10B981" stroke-width="2"/>
  <text x="225" y="72" font-size="11" fill="#6EE7B7" text-anchor="middle" font-weight="bold">✅ Paid</text>

  <!-- Arrow down expired -->
  <line x1="80" y1="84" x2="80" y2="115" stroke="#EF4444" stroke-width="1.5" marker-end="url(#arrp)"/>
  <text x="88" y="103" font-size="9" fill="#EF4444">QR timeout</text>

  <!-- Expired -->
  <rect x="30" y="118" width="100" height="28" rx="14" fill="#7F1D1D" stroke="#EF4444" stroke-width="1"/>
  <text x="80" y="137" font-size="10" fill="#FCA5A5" text-anchor="middle">⏰ Expired</text>

  <!-- Arrow down failed -->
  <line x1="225" y1="84" x2="225" y2="100" stroke="#F59E0B" stroke-width="1" marker-end="url(#arrp)"/>

  <!-- Failed -->
  <rect x="175" y="102" width="100" height="28" rx="14" fill="#78350F" stroke="#F59E0B" stroke-width="1"/>
  <text x="225" y="121" font-size="10" fill="#FDE68A" text-anchor="middle">❌ Failed</text>

  <!-- Arrow from Paid to Refunded -->
  <line x1="275" y1="67" x2="315" y2="67" stroke="#8B5CF6" stroke-width="1.5" marker-end="url(#arrp)"/>
  <text x="295" y="62" font-size="9" fill="#A78BFA" text-anchor="middle">refund</text>

  <!-- Refunded -->
  <rect x="320" y="50" width="110" height="34" rx="17" fill="#2D0A5E" stroke="#8B5CF6" stroke-width="1.5"/>
  <text x="375" y="72" font-size="11" fill="#C4B5FD" text-anchor="middle" font-weight="bold">↩️ Refunded</text>

  <!-- Arrow from Paid to Manual Pending -->
  <line x1="275" y1="84" x2="370" y2="120" stroke="#3B82F6" stroke-width="1" stroke-dasharray="4,3"/>
  <text x="340" y="108" font-size="9" fill="#93C5FD" text-anchor="middle">manual verify</text>

  <!-- Manual Pending -->
  <rect x="320" y="118" width="110" height="28" rx="14" fill="#1E3A5F" stroke="#3B82F6" stroke-width="1"/>
  <text x="375" y="137" font-size="10" fill="#93C5FD" text-anchor="middle">🔍 Manual Pending</text>

  <!-- Security badges -->
  <rect x="470" y="40" width="300" height="100" rx="10" fill="rgba(255,255,255,0.02)" stroke="rgba(255,255,255,0.06)"/>
  <text x="620" y="58" font-size="10" fill="#9CA3AF" text-anchor="middle" font-weight="bold">🔒 វិធានការសន្តិសុខ</text>
  <rect x="485" y="68" width="270" height="18" rx="4" fill="rgba(16,185,129,0.1)"/>
  <text x="620" y="81" font-size="9" fill="#6EE7B7" text-anchor="middle">✓ HMAC-SHA512 signing (ABA PayWay)</text>
  <rect x="485" y="91" width="270" height="18" rx="4" fill="rgba(99,102,241,0.1)"/>
  <text x="620" y="104" font-size="9" fill="#A5B4FC" text-anchor="middle">✓ qr_md5 unique index — replay protection</text>
  <rect x="485" y="114" width="270" height="18" rx="4" fill="rgba(234,179,8,0.1)"/>
  <text x="620" y="127" font-size="9" fill="#FDE68A" text-anchor="middle">✓ tran_id unique — idempotent webhook</text>
</svg>

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad7" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EF4444" stop-opacity="0"/>
      <stop offset="50%" stop-color="#F87171" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#EF4444" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad7)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#F87171"/>
</svg>

## 🔧 បឋមកថាសន្តិសុខ HTTP

`SecurityHeadersMiddleware` អនុវត្តបឋមកថាទាំងនេះទៅគ្រប់ការឆ្លើយតបទាំងអស់៖

<!-- ─── Security Headers Shield SVG ─────────────────────────────── -->
<svg viewBox="0 0 800 160" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="sh1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#EF4444"/>
    </linearGradient>
    <linearGradient id="sh2" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#EA580C"/><stop offset="100%" stop-color="#F97316"/>
    </linearGradient>
    <linearGradient id="sh3" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#D97706"/><stop offset="100%" stop-color="#F59E0B"/>
    </linearGradient>
    <linearGradient id="sh4" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#059669"/><stop offset="100%" stop-color="#10B981"/>
    </linearGradient>
    <linearGradient id="sh5" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/><stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
    <linearGradient id="sh6" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#7C3AED"/><stop offset="100%" stop-color="#A78BFA"/>
    </linearGradient>
  </defs>

  <text x="400" y="22" font-size="13" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🛡️ SECURITY HEADERS — បឋមកថាសន្តិសុខ</text>

  <!-- Blocker icon -->
  <text x="60" y="75" font-size="36" text-anchor="middle">🛡️</text>
  <text x="60" y="110" font-size="10" fill="#6B7280" text-anchor="middle">9 Headers</text>

  <!-- Header rows -->
  <rect x="120" y="38" width="650" height="22" rx="4" fill="url(#sh1)"/>
  <text x="140" y="54" font-size="10" fill="#fff" font-weight="bold">Content-Security-Policy</text>
  <text x="550" y="54" font-size="9" fill="#fca5a5">default-src 'self'; script-src 'self'; frame-ancestors 'none'</text>

  <rect x="120" y="65" width="650" height="20" rx="4" fill="url(#sh2)"/>
  <text x="140" y="80" font-size="10" fill="#fff" font-weight="bold">Strict-Transport-Security</text>
  <text x="550" y="80" font-size="9" fill="#fdba74">max-age=31536000; includeSubDomains; preload</text>

  <rect x="120" y="90" width="650" height="20" rx="4" fill="url(#sh3)"/>
  <text x="140" y="105" font-size="10" fill="#fff" font-weight="bold">X-Frame-Options · X-Content-Type-Options</text>
  <text x="550" y="105" font-size="9" fill="#fde68a">DENY · nosniff</text>

  <rect x="120" y="115" width="650" height="20" rx="4" fill="url(#sh4)"/>
  <text x="140" y="130" font-size="10" fill="#fff" font-weight="bold">Permissions-Policy</text>
  <text x="550" y="130" font-size="9" fill="#6ee7b7">camera=(), microphone=(), geolocation=(), payment=(), usb=()</text>

  <rect x="120" y="140" width="650" height="18" rx="4" fill="url(#sh5)"/>
  <text x="140" y="153" font-size="10" fill="#fff" font-weight="bold">Referrer-Policy · X-XSS-Protection</text>
  <text x="550" y="153" font-size="9" fill="#93c5fd">strict-origin-when-cross-origin · 1; mode=block</text>
</svg>

| បឋមកថា | តម្លៃ | គោលបំណង |
|-----------|-------|-----------|
| `Content-Security-Policy` | `default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'; upgrade-insecure-requests` | កំណត់ធនធានដែលកម្មវិធីអាចផ្ទុក, ការពារ XSS និង clickjacking |
| `X-Content-Type-Options` | `nosniff` | ការពារ MIME sniffing |
| `X-Frame-Options` | `DENY` | ការពារ clickjacking |
| `X-XSS-Protection` | `1; mode=block` | បើក XSS filter របស់កម្មវិធីរុករក |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | បង្ខំឱ្យប្រើ HTTPS រយៈពេល ១ ឆ្នាំ |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | គ្រប់គ្រងការលេចធ្លាយ Referrer |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(), payment=(), usb=()` | បិទមុខងារកម្មវិធីរុករកដែលមិនចាំបាច់ |

**បឋមកថាដែលត្រូវបានដកចេញ** ដើម្បីការពារការកំណត់អត្តសញ្ញាណម៉ាស៊ីនមេ៖
- `X-Powered-By`
- `Server`
- `X-Generator`

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad8" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#8B5CF6" stop-opacity="0"/>
      <stop offset="50%" stop-color="#A78BFA" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#8B5CF6" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad8)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#A78BFA"/>
</svg>

## 🔏 ការពារទិន្នន័យ

### ការរក្សាទុកពាក្យសម្ងាត់

- **ក្បួនដោះស្រាយ**: Bcrypt ជាមួយ 12 cost rounds
- **ការ hashing ដោយស្វ័យប្រវត្តិ**: `Hash::make()` លើពាក្យសម្ងាត់ទាំងអស់
- **គ្មានអក្សរធម្មតា**: ពាក្យសម្ងាត់មិនត្រូវបានកត់ត្រា ឬបង្ហាញក្នុងការឆ្លើយតប API ឡើយ

### ការត្រួតពិនិត្យការបញ្ចូល (Input Validation)

- **Server-side**: រាល់ការបញ្ចូលត្រូវបានត្រួតពិនិត្យតាម Form Requests ឬ Validator facade
- **SQL injection**: ការពារដោយ Eloquent ORM parameter binding
- **XSS**: Blade `{{ }}` គេចពី output ដោយស្វ័យប្រវត្តិ; React JSX គ្រប់គ្រង encoding

### សន្តិសុខការផ្ទុកឯកសារ

| មុខងារ | ការអនុវត្ត |
|---------|---------------|
| **ការផ្ទុករូបភាពអតិថិជន** | ត្រួតពិនិត្យ MIME type (jpeg, png, webp, gif) |
| **រូបភាពផលិតផល** | ឈ្មោះឯកសារផ្អែកលើ UUID ការពារ path traversal |
| **ការដាក់ឯកោ** | ឯកសារសាធារណៈរក្សាទុកក្នុង `public/storage` |

### ការរក្សាទុក Cache នៃការកំណត់

ការកំណត់រសើបត្រូវបានរក្សាទុកក្នុង cache ជាមួយ TTL ៥ នាទី។ ពេលរក្សាទុក, cache ត្រូវបានលុបចោល៖

```php
// AdminSetting model
public static function get($key, $default = null)
{
    return Cache::remember("admin_setting_{$key}", 300, function () use ($key) {
        return self::where('key', $key)->value('value');
    });
}
```

### ការការពារប្រឆាំងនឹងការចាក់ SQL (SQL Injection)

រាល់សំណួរប្រើប្រាស់ Eloquent ORM parameterized binding។ គ្មានការបញ្ចូលអ្នកប្រើដោយផ្ទាល់ក្នុង SQL strings ទេ៖

```php
// ឧទាហរណ៍ — សុវត្ថិភាព
$products = Product::where('category', $categoryInput)->get();

// ឧទាហរណ៍ — សុវត្ថិភាព (ជាមួយ parameterized binding)
$users = User::whereRaw('LOWER(username) = ?', [strtolower($input)])->get();

// មិនដែលប្រើ — គ្រោះថ្នាក់
// $users = DB::select("SELECT * FROM users WHERE username = '$input'"); ❌
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad9" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#06B6D4" stop-opacity="0"/>
      <stop offset="50%" stop-color="#22D3EE" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#06B6D4" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad9)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#22D3EE"/>
</svg>

## 🤖 សន្តិសុខ Telegram

### ស្ថាបត្យកម្ម Bot ពីរ

<!-- ─── Telegram Dual Bot Architecture SVG ──────────────────────── -->
<svg viewBox="0 0 800 200" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="tb1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1E3A5F"/><stop offset="100%" stop-color="#0F1F3A"/>
    </linearGradient>
    <linearGradient id="tb2" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#064E3B"/><stop offset="100%" stop-color="#022C22"/>
    </linearGradient>
  </defs>

  <text x="400" y="22" font-size="13" fill="#D1D5DB" text-anchor="middle" font-weight="bold">🤖 DUAL TELEGRAM BOT ARCHITECTURE</text>

  <!-- Bot 1: Admin -->
  <rect x="30" y="38" width="200" height="50" rx="10" fill="url(#tb1)" stroke="#3B82F6" stroke-width="1.5"/>
  <text x="130" y="58" font-size="12" fill="#93C5FD" text-anchor="middle" font-weight="bold">🤖 Bot 1 — Admin</text>
  <text x="130" y="75" font-size="9" fill="#6B7280" text-anchor="middle">ការជូនដំណឹងអ្នកគ្រប់គ្រង</text>

  <!-- Bot 1 arrows to Admin Channel + DM -->
  <line x1="230" y1="55" x2="290" y2="55" stroke="#3B82F6" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="295" y="42" width="150" height="26" rx="6" fill="url(#tb1)" stroke="#3B82F6" stroke-width="0.5"/>
  <text x="370" y="59" font-size="9" fill="#93C5FD" text-anchor="middle">📢 Admin Channel (Private)</text>
  <line x1="230" y1="73" x2="290" y2="85" stroke="#6B7280" stroke-width="1" marker-end="url(#arrow)"/>
  <rect x="295" y="76" width="150" height="22" rx="6" fill="url(#tb1)" stroke="rgba(255,255,255,0.1)"/>
  <text x="370" y="91" font-size="9" fill="#9CA3AF" text-anchor="middle">📩 Shop Owner DMs</text>

  <!-- Security controls center -->
  <rect x="460" y="38" width="150" height="145" rx="10" fill="rgba(255,255,255,0.02)" stroke="rgba(255,255,255,0.06)"/>
  <text x="535" y="55" font-size="10" fill="#9CA3AF" text-anchor="middle" font-weight="bold">🛡️ ការត្រួតពិនិត្យ</text>
  <rect x="475" y="65" width="120" height="22" rx="6" fill="rgba(239,68,68,0.1)" stroke="rgba(239,68,68,0.2)"/>
  <text x="535" y="80" font-size="9" fill="#FCA5A5" text-anchor="middle">✓ Rate Limited</text>
  <rect x="475" y="93" width="120" height="22" rx="6" fill="rgba(16,185,129,0.1)" stroke="rgba(16,185,129,0.2)"/>
  <text x="535" y="108" font-size="9" fill="#6EE7B7" text-anchor="middle">✓ Whitelist Admin IDs</text>
  <rect x="475" y="121" width="120" height="22" rx="6" fill="rgba(99,102,241,0.1)" stroke="rgba(99,102,241,0.2)"/>
  <text x="535" y="136" font-size="9" fill="#A5B4FC" text-anchor="middle">✓ Webhook Token</text>
  <rect x="475" y="149" width="120" height="22" rx="6" fill="rgba(234,179,8,0.1)" stroke="rgba(234,179,8,0.2)"/>
  <text x="535" y="164" font-size="9" fill="#FDE68A" text-anchor="middle">✓ Bot Isolation</text>

  <!-- Bot 2: Customer -->
  <rect x="30" y="105" width="200" height="50" rx="10" fill="url(#tb2)" stroke="#10B981" stroke-width="1.5"/>
  <text x="130" y="125" font-size="12" fill="#6EE7B7" text-anchor="middle" font-weight="bold">🤖 Bot 2 — Customer</text>
  <text x="130" y="142" font-size="9" fill="#6B7280" text-anchor="middle">ការអាប់ដេតអតិថិជន</text>

  <!-- Bot 2 arrow to Customer DM -->
  <line x1="230" y1="120" x2="295" y2="120" stroke="#10B981" stroke-width="1.5" marker-end="url(#arrow-g)"/>
  <rect x="300" y="108" width="140" height="24" rx="6" fill="url(#tb2)" stroke="#10B981" stroke-width="0.5"/>
  <text x="370" y="124" font-size="9" fill="#6EE7B7" text-anchor="middle">💬 Customer DM</text>

  <!-- Connection flow text -->
  <text x="400" y="190" font-size="10" fill="#6B7280" text-anchor="middle" font-style="italic">API keys ផ្សេងគ្នា — ការដាក់ឯកោពេញលេញរវាង Bot ទាំងពីរ</text>
</svg>

| Bot | គោលបំណង | ទម្រង់ | សន្តិសុខ |
|-----|-----------|--------|----------|
| **Bot 1 (Admin)** | ការជូនដំណឹងការបញ្ជាទិញ, ការបញ្ជាក់ការទូទាត់, ការព្រមានស្តុកទាប | Markdown | Admin chat_id whitelist, rate-limited |
| **Bot 2 (អតិថិជន)** | ការអាប់ដេតការបញ្ជាទិញ, តាមដានការដឹកជញ្ជូន, ព័ត៌មានធានា | HTML | Webhook token verification, per-user chat_id |

### លំហូរការភ្ជាប់ Telegram របស់អ្នកប្រើ

```
១. អ្នកប្រើស្នើសុំភ្ជាប់ Telegram
២. ម៉ាស៊ីនមេបង្កើត token ភ្ជាប់តែមួយគត់ (UUID, ផុតកំណត់ក្នុង ១៥ នាទី)
៣. អ្នកប្រើផ្ញើ /start <token> ទៅកាន់ bot
៤. Bot webhook ទទួលសារ និងត្រួតពិនិត្យ token
៥. Backend ភ្ជាប់ telegram_chat_id ទៅគណនីអ្នកប្រើ
៦. អ្នកប្រើទទួលការជូនដំណឹងការបញ្ជាទិញតាមរយៈ bot
```

- **គ្មានទិន្នន័យរសើប** ត្រូវបានបញ្ជូនតាមសារ Telegram
- **ការបំបែកកង្វល់**: Bot អ្នកគ្រប់គ្រង និងអតិថិជនប្រើ API keys ផ្សេងគ្នា
- **ការដាក់ឯកោពីការបរាជ័យ**: Telegram API failures ត្រូវបានរុំក្នុង try-catch — មិនដែលប៉ះពាល់ដល់សំណើចម្បង

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad10" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F97316" stop-opacity="0"/>
      <stop offset="50%" stop-color="#FB923C" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#F97316" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad10)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#FB923C"/>
</svg>

## 👨‍🔧 សន្តិសុខផតថលអ្នកអភិវឌ្ឍន៍

### ការគ្រប់គ្រងការចូលប្រើ

| ស្រទាប់ | ការការពារ |
|----------|------------|
| **ការការពារ Route** | `RoleMiddleware:developer` — មានតែអ្នកប្រើដែលមាន `role=developer` ទេដែលអាចចូលបាន |
| **កំណត់អត្រាចូល** | ១០ ដងក្នុងមួយនាទីក្នុងមួយ IP |
| **ការពន្យាពេលសិប្បនិម្មិត** | ១ វិនាទីពន្យាពេលលើការចូលបរាជ័យ |
| **កូនសោសម្ងាត់** | `DEV_PORTAL_KEY` ក្នុង `.env` សម្រាប់ការការពារបន្ថែម |

### Dev Portal Endpoints

| Endpoint | ទិន្នន័យដែលបង្ហាញ | ហានិភ័យ |
|----------|---------------------|----------|
| `/api/dev/health` | សុខភាពប្រព័ន្ធ, uptime, DB status | ទាប |
| `/api/dev/logs` | ៣០០ បន្ទាត់ចុងក្រោយនៃ `laravel.log` | 🔴 **មធ្យម** |
| `/api/dev/env` | អថេរបរិស្ថាន (filtered) | 🔴 **ខ្ពស់** — កូនសោ API ត្រូវបានលាក់ |

ការចូលប្រើអថេរបរិស្ថានត្រងតម្លៃរសើប៖
```php
$safeEnv = collect($_ENV)->map(function ($value, $key) {
    return str_contains($key, 'SECRET') || str_contains($key, 'KEY')
        ? '********'
        : $value;
});
```

---

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="divGrad11" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626" stop-opacity="0"/>
      <stop offset="50%" stop-color="#EF4444" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#DC2626" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#divGrad11)" stroke-width="1"/>
  <circle cx="400" cy="15" r="3" fill="#EF4444"/>
</svg>

## 🐛 រាយការណ៍ពីភាពងាយរងគ្រោះ

យើងយកចិត្តទុកដាក់លើសន្តិសុខ។ ប្រសិនបើអ្នករកឃើញភាពងាយរងគ្រោះសន្តិសុខនៅក្នុង Tronmatix Computer សូមអនុវត្តតាមជំហានទាំងនេះ៖

### គោលការណ៍បង្ហាញ

1. **កុំ** បង្ហាញភាពងាយរងគ្រោះជាសាធារណៈ (គ្មាន GitHub issues, គ្មានប្រព័ន្ធផ្សព្វផ្សាយសង្គម)
2. **ផ្ញើអ៊ីមែល** ទៅកាន់អ្នកថែរក្សាគម្រោងជាមួយព័ត៌មានលម្អិត
3. **រួមបញ្ចូល**:
   - ប្រភេទនៃភាពងាយរងគ្រោះ
   - ជំហានដើម្បីបង្កើតឡើងវិញ
   - កំណែដែលរងផលប៉ះពាល់
   - ផលប៉ះពាល់ដែលអាចកើតមាន
   - ការជួសជុលដែលបានស្នើ (ប្រសិនបើមាន)

### ពេលវេលាឆ្លើយតប

| រយៈពេល | សកម្មភាព |
|-----------|-----------|
| **០–២៤ ម៉ោង** | ការទទួលស្គាល់ការទទួល |
| **២៤–៧២ ម៉ោង** | ការវាយតម្លៃដំបូង និងការចាត់ថ្នាក់កម្រិតធ្ងន់ធ្ងរ |
| **៣–៧ ថ្ងៃ** | ការអភិវឌ្ឍន៍ជួសជុល និងការធ្វើតេស្តផ្ទៃក្នុង |
| **៧–១៤ ថ្ងៃ** | ការចេញផ្សាយបំណះ និងការបង្ហាញ |

---

## 📜 បញ្ជីត្រួតពិនិត្យសន្តិសុខ

<!-- ─── Security Checklist Progress Bar SVG ─────────────────────── -->
<svg viewBox="0 0 800 80" width="100%" height="auto" style="max-width:800px" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="prog" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#10B981"/><stop offset="100%" stop-color="#3B82F6"/>
    </linearGradient>
  </defs>
  <rect x="50" y="25" width="700" height="20" rx="10" fill="#1F2937"/>
  <rect x="50" y="25" width="700" height="20" rx="10" fill="url(#prog)" opacity="0.9"/>
  <text x="400" y="40" font-size="11" fill="#fff" text-anchor="middle" font-weight="bold">សន្តិសុខ ១០០% — រាល់វិធានការទាំងអស់ត្រូវបានអនុវត្ត</text>
  <text x="50" y="65" font-size="9" fill="#9CA3AF">0%</text>
  <text x="750" y="65" font-size="9" fill="#9CA3AF" text-anchor="end">100%</text>
</svg>

<div style="display:flex; flex-wrap:wrap; gap:8px;">
<div style="flex:1; min-width:250px;">

- [x] ការផ្ទៀងផ្ទាត់ច្រើនប្រភេទ (web, admin, staff, sanctum)
- [x] ការបោះពុម្ពស្នាមម្រាមដៃសម័យ (HMAC-SHA256)
- [x] រយៈពេលផុតកំណត់សម័យ និងការបង្វិល
- [x] ការអ៊ិនគ្រីបសម័យ AES-256-CBC
- [x] Token API ផុតកំណត់ ៣០ ថ្ងៃ
- [x] ការកំណត់អត្រាច្រើនកម្រិត (60/20/10/5 ដង/នាទី)
- [x] ការការពារ CSRF (រាល់ប្រតិបត្តិការផ្លាស់ប្តូរស្ថានភាព)
- [x] ការគ្រប់គ្រងការចូលប្រើតាមតួនាទី (៦ តួនាទី × ៩ មុខងារ)
- [x] Bcrypt password hashing (12 rounds)
- [x] Middleware រកឃើញការហាមឃាត់
- [x] បឋមកថាសន្តិសុខ (CSP, HSTS, X-Frame-Options)
- [x] ការដកបឋមកថាកំណត់អត្តសញ្ញាណម៉ាស៊ីនមេ
</div>
<div style="flex:1; min-width:250px;">

- [x] CORS configuration
- [x] ការត្រួតពិនិត្យសំណើ និងសុពលភាព
- [x] Eloquent ORM (ការពារ SQL injection)
- [x] HMAC signing ការទូទាត់
- [x] Webhook idempotent
- [x] ការដកហូត token API ភ្លាមៗ
- [x] ការការពារផតថលអ្នកអភិវឌ្ឍន៍
- [x] ការដាក់ឯកោ Telegram bot
- [x] ការត្រួតពិនិត្យការផ្ទុកឯកសារ
- [x] ការការពារអថេរបរិស្ថាន
- [x] កំណត់ហេតុសវនកម្ម (Audit Trail) សម្រាប់រាល់សកម្មភាព
</div>
</div>

---

<div align="center">

<svg viewBox="0 0 800 30" width="100%" height="30" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="footerGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#DC2626" stop-opacity="0"/>
      <stop offset="50%" stop-color="#EF4444" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#DC2626" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <line x1="0" y1="15" x2="800" y2="15" stroke="url(#footerGrad)" stroke-width="2"/>
  <circle cx="400" cy="15" r="4" fill="#EF4444"></circle>
</svg>

<br />

**Tronmatix Computer** — *សន្តិសុខជាអាទិភាពសម្រាប់ពាណិជ្ជកម្មអេឡិចត្រូនិកកម្ពុជា*  
_ភ្នំពេញ · ២០២៦_

[![Laravel](https://img.shields.io/badge/Powered%20by-Laravel-F93208?style=flat-square&logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/Powered%20by-React-61DAFB?style=flat-square&logo=react)](https://react.dev)

</div>

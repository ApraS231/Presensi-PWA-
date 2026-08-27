# Design System - Material Design 3 (M3)
## Sistem Presensi PWA PT. Cahaya Anugrah Kalimantan

Dokumentasi implementasi Material Design 3 (M3) untuk web PWA menggunakan CSS Custom Properties.
Dioptimasi untuk Laravel Blade + CSS tanpa dependency framework UI khusus.

---

## 1. Filosofi Design

### M3 Core Principles yang Diterapkan

| Prinsip | Penerapan di Presensi PWA |
|---|---|
| **Dynamic Color** | Skema warna algoritmik dari brand color PT. CAK - adaptif light/dark mode |
| **Expressive Motion** | Transisi halus dengan M3 easing curves - snappy start, gentle landing |
| **Expressive Shape** | Rounded corners mengikuti M3 Shape Scale - dari Extra Small (4px) hingga Full (pill) |
| **Adaptive Layout** | PWA mobile (360-430px) <-> Desktop admin (1024px+) - dua layout terpisah |
| **Tonal Elevation** | Depth menggunakan surface tint bukan drop shadow berlebihan |
| **Accessibility First** | Kontras warna otomatis >= 4.5:1 - pairing algoritmik M3 |

---

## 2. Color System (Dynamic Color Scheme)

### 2.1 Source Color & Key Colors

Brand color utama PT. Cahaya Anugrah Kalimantan -> Biru Profesional sebagai seed:

```css
/* Source / Seed Color */
--md-source: #1565C0;  /* Biru korporat */

/* Key Colors (dihasilkan dari source) */
--md-ref-palette-primary:   #1565C0;  /* Profesional, trust */
--md-ref-palette-secondary: #546E7A;  /* Netral hangat */
--md-ref-palette-tertiary:  #00897B;  /* Aksen teal - sukses & geofence */
--md-ref-palette-error:     #BA1A1A;  /* Error merah M3 */
--md-ref-palette-neutral:   #5D5E61;  /* Baseline netral */
```

### 2.2 Light Theme Tokens

```css
:root,
[data-theme="light"] {
    /* PRIMARY - Tombol utama, FAB, aksen aktif */
    --md-sys-color-primary:               #1565C0;
    --md-sys-color-on-primary:            #FFFFFF;
    --md-sys-color-primary-container:     #D4E3FF;
    --md-sys-color-on-primary-container:  #001C3A;

    /* SECONDARY - Tombol sekunder, filter chips */
    --md-sys-color-secondary:             #546E7A;
    --md-sys-color-on-secondary:          #FFFFFF;
    --md-sys-color-secondary-container:   #CFE4EE;
    --md-sys-color-on-secondary-container:#0C1D26;

    /* TERTIARY - Aksen tambahan (sukses, geofence) */
    --md-sys-color-tertiary:              #00897B;
    --md-sys-color-on-tertiary:           #FFFFFF;
    --md-sys-color-tertiary-container:    #A7F5E7;
    --md-sys-color-on-tertiary-container: #002019;

    /* ERROR - Validasi gagal, alert kritis */
    --md-sys-color-error:                 #BA1A1A;
    --md-sys-color-on-error:              #FFFFFF;
    --md-sys-color-error-container:       #FFDAD6;
    --md-sys-color-on-error-container:    #410002;

    /* SURFACE - Background, card, dialog */
    --md-sys-color-surface:               #FAFAFA;
    --md-sys-color-on-surface:            #1C1B1F;
    --md-sys-color-surface-variant:       #E2E2E6;
    --md-sys-color-on-surface-variant:    #44474E;
    --md-sys-color-surface-container-lowest:  #FFFFFF;
    --md-sys-color-surface-container-low:     #F6F6FA;
    --md-sys-color-surface-container:         #F0F0F4;
    --md-sys-color-surface-container-high:    #EAEAEE;
    --md-sys-color-surface-container-highest: #E4E4E8;

    /* OUTLINE & INVERSE */
    --md-sys-color-outline:               #74777F;
    --md-sys-color-outline-variant:       #C4C6CF;
    --md-sys-color-inverse-surface:       #313033;
    --md-sys-color-inverse-on-surface:    #F4EFF4;
    --md-sys-color-inverse-primary:       #A5C8FF;
    --md-sys-color-scrim:                 #000000;
    --md-sys-color-shadow:                #000000;

    /* CUSTOM - Warna khusus fitur Presensi */
    --md-custom-color-success:            #2E7D32;
    --md-custom-color-on-success:         #FFFFFF;
    --md-custom-color-success-container:  #C8E6C9;
    --md-custom-color-warning:            #F57F17;
    --md-custom-color-on-warning:         #FFFFFF;
    --md-custom-color-warning-container:  #FFF9C4;
    --md-custom-color-info:               #0288D1;
    --md-custom-color-on-info:            #FFFFFF;

    /* Status Presensi */
    --md-custom-color-tepat-waktu:        #2E7D32;  /* Hijau */
    --md-custom-color-terlambat:          #E65100;  /* Oranye tua */
    --md-custom-color-izin:               #0277BD;  /* Biru muda */
    --md-custom-color-sakit:              #6A1B9A;  /* Ungu */
    --md-custom-color-cuti:               #00838F;  /* Cyan tua */
    --md-custom-color-alpha:              #C62828;  /* Merah */
}
```

### 2.3 Dark Theme Tokens

```css
[data-theme="dark"] {
    /* PRIMARY */
    --md-sys-color-primary:               #A5C8FF;
    --md-sys-color-on-primary:           #00315E;
    --md-sys-color-primary-container:    #004884;
    --md-sys-color-on-primary-container: #D4E3FF;

    /* SECONDARY */
    --md-sys-color-secondary:            #B4CAD6;
    --md-sys-color-on-secondary:         #1F333E;
    --md-sys-color-secondary-container:  #364955;
    --md-sys-color-on-secondary-container:#CFE4EE;

    /* TERTIARY */
    --md-sys-color-tertiary:             #8AD8CB;
    --md-sys-color-on-tertiary:          #00382F;
    --md-sys-color-tertiary-container:   #005046;
    --md-sys-color-on-tertiary-container:#A7F5E7;

    /* ERROR */
    --md-sys-color-error:                #FFB4AB;
    --md-sys-color-on-error:             #690005;
    --md-sys-color-error-container:      #93000A;
    --md-sys-color-on-error-container:   #FFDAD6;

    /* SURFACE */
    --md-sys-color-surface:              #131316;
    --md-sys-color-on-surface:           #E4E1E6;
    --md-sys-color-surface-variant:      #44474E;
    --md-sys-color-on-surface-variant:   #C4C6CF;
    --md-sys-color-surface-container-lowest:  #0E0E11;
    --md-sys-color-surface-container-low:     #1C1B1F;
    --md-sys-color-surface-container:         #201F23;
    --md-sys-color-surface-container-high:    #2B2A2E;
    --md-sys-color-surface-container-highest: #363539;

    /* OUTLINE & INVERSE */
    --md-sys-color-outline:              #8E9099;
    --md-sys-color-outline-variant:      #44474E;
    --md-sys-color-inverse-surface:      #E4E1E6;
    --md-sys-color-inverse-on-surface:   #313033;
    --md-sys-color-inverse-primary:      #1565C0;
    --md-sys-color-scrim:                #000000;
    --md-sys-color-shadow:               #000000;

    /* CUSTOM STATUS (dark-adjusted) */
    --md-custom-color-success:           #81C784;
    --md-custom-color-on-success:        #003300;
    --md-custom-color-success-container: #1B5E20;
    --md-custom-color-warning:           #FFD54F;
    --md-custom-color-on-warning:        #3E2723;
    --md-custom-color-warning-container: #E65100;
    --md-custom-color-tepat-waktu:       #81C784;
    --md-custom-color-terlambat:         #FFB74D;
    --md-custom-color-izin:              #4FC3F7;
    --md-custom-color-sakit:             #CE93D8;
    --md-custom-color-cuti:              #4DD0E1;
    --md-custom-color-alpha:             #EF5350;
}
```

### 2.4 Pemetaan Warna ke Komponen Presensi

| Elemen UI | Token yang Digunakan | Alasan |
|---|---|---|
| **Tombol "Presensi Masuk"** | `primary` | Aksi utama, high emphasis |
| **Tombol "Presensi Pulang"** | `secondary` | Aksi kedua |
| **Bounding Box Face Detection** | `tertiary` (hijau teal) | Status verifikasi aktif |
| **Badge "Tepat Waktu"** | `--md-custom-color-tepat-waktu` | Status positif |
| **Badge "Terlambat"** | `--md-custom-color-terlambat` | Status peringatan |
| **Badge "Alpha"** | `--md-custom-color-alpha` | Status kritis |
| **Card Statistik Dashboard** | `surface-container` | Elevated surface M3 |
| **Circle Geofence di Peta** | `primary` (opacity 20%) | Area valid presensi |
| **Marker Presensi di Peta** | Per status (warna custom) | Visual diferensiasi |
| **Navigation Bar (Bottom)** | `surface-container` | M3 standard nav |
| **Sidebar Admin** | `surface-container-low` | Netral, tidak mencolok |
| **FAB (Floating Action Button)** | `primary-container` | M3 standard FAB |
| **Error Toast / Snackbar** | `error-container` | Alert error |

---

## 3. Typography (M3 Type Scale)

### 3.1 Font Family

```css
:root {
    /* Primary font - Google Fonts */
    --md-ref-typeface-brand:   'Inter', 'Roboto', system-ui, -apple-system, sans-serif;
    --md-ref-typeface-plain:   'Inter', 'Roboto', system-ui, -apple-system, sans-serif;
    --md-ref-typeface-mono:    'JetBrains Mono', 'Fira Code', monospace;
}
```

Load via Google Fonts (di layout Blade):
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### 3.2 M3 Type Scale Tokens

```css
:root {
    /* DISPLAY - Hero text, angka besar */
    --md-sys-typescale-display-large:   700 3.5625rem/4rem var(--md-ref-typeface-brand);     /* 57px */
    --md-sys-typescale-display-medium:  700 2.8125rem/3.25rem var(--md-ref-typeface-brand);   /* 45px */
    --md-sys-typescale-display-small:   600 2.25rem/2.75rem var(--md-ref-typeface-brand);     /* 36px */

    /* HEADLINE - Judul halaman, section header */
    --md-sys-typescale-headline-large:  600 2rem/2.5rem var(--md-ref-typeface-brand);         /* 32px */
    --md-sys-typescale-headline-medium: 600 1.75rem/2.25rem var(--md-ref-typeface-brand);     /* 28px */
    --md-sys-typescale-headline-small:  600 1.5rem/2rem var(--md-ref-typeface-brand);         /* 24px */

    /* TITLE - Card title, dialog title */
    --md-sys-typescale-title-large:     600 1.375rem/1.75rem var(--md-ref-typeface-brand);    /* 22px */
    --md-sys-typescale-title-medium:    500 1rem/1.5rem var(--md-ref-typeface-brand);         /* 16px */
    --md-sys-typescale-title-small:     500 0.875rem/1.25rem var(--md-ref-typeface-brand);    /* 14px */

    /* BODY - Paragraf, deskripsi, konten utama */
    --md-sys-typescale-body-large:      400 1rem/1.5rem var(--md-ref-typeface-plain);         /* 16px */
    --md-sys-typescale-body-medium:     400 0.875rem/1.25rem var(--md-ref-typeface-plain);    /* 14px */
    --md-sys-typescale-body-small:      400 0.75rem/1rem var(--md-ref-typeface-plain);        /* 12px */

    /* LABEL - Button text, caption, badge */
    --md-sys-typescale-label-large:     500 0.875rem/1.25rem var(--md-ref-typeface-plain);    /* 14px */
    --md-sys-typescale-label-medium:    500 0.75rem/1rem var(--md-ref-typeface-plain);        /* 12px */
    --md-sys-typescale-label-small:     500 0.6875rem/1rem var(--md-ref-typeface-plain);      /* 11px */
}
```

### 3.3 Utility Classes

```css
.md-display-large   { font: var(--md-sys-typescale-display-large);   letter-spacing: -0.25px; }
.md-display-medium  { font: var(--md-sys-typescale-display-medium);  letter-spacing: 0; }
.md-display-small   { font: var(--md-sys-typescale-display-small);   letter-spacing: 0; }
.md-headline-large  { font: var(--md-sys-typescale-headline-large);  letter-spacing: 0; }
.md-headline-medium { font: var(--md-sys-typescale-headline-medium); letter-spacing: 0; }
.md-headline-small  { font: var(--md-sys-typescale-headline-small);  letter-spacing: 0; }
.md-title-large     { font: var(--md-sys-typescale-title-large);     letter-spacing: 0; }
.md-title-medium    { font: var(--md-sys-typescale-title-medium);    letter-spacing: 0.15px; }
.md-title-small     { font: var(--md-sys-typescale-title-small);     letter-spacing: 0.1px; }
.md-body-large      { font: var(--md-sys-typescale-body-large);      letter-spacing: 0.5px; }
.md-body-medium     { font: var(--md-sys-typescale-body-medium);     letter-spacing: 0.25px; }
.md-body-small      { font: var(--md-sys-typescale-body-small);      letter-spacing: 0.4px; }
.md-label-large     { font: var(--md-sys-typescale-label-large);     letter-spacing: 0.1px; }
.md-label-medium    { font: var(--md-sys-typescale-label-medium);    letter-spacing: 0.5px; }
.md-label-small     { font: var(--md-sys-typescale-label-small);     letter-spacing: 0.5px; }
```

### 3.4 Pemetaan Typescale ke UI Presensi

| Elemen | Typescale | Contoh |
|---|---|---|
| Angka besar dashboard (statistik) | `display-large` / `display-medium` | "42" karyawan hadir |
| Judul halaman | `headline-medium` | "Dashboard Monitoring" |
| Judul card | `title-large` | "Statistik Kehadiran" |
| Nama karyawan di tabel | `title-medium` | "Ahmad Fauzi" |
| Isi tabel / konten | `body-medium` | "08:15 WITA" |
| Label status badge | `label-medium` | "TEPAT WAKTU" |
| Teks tombol | `label-large` | "KIRIM PRESENSI" |
| Caption / helper text | `body-small` | "Jarak: 32m dari kantor" |
| Bottom navigation label | `label-medium` | "Beranda" |

---

## 4. Shape System (M3 Shape Scale)

### 4.1 Shape Tokens

```css
:root {
    --md-sys-shape-corner-none:         0px;
    --md-sys-shape-corner-extra-small:  4px;
    --md-sys-shape-corner-small:        8px;
    --md-sys-shape-corner-medium:       12px;
    --md-sys-shape-corner-large:        16px;
    --md-sys-shape-corner-extra-large:  28px;
    --md-sys-shape-corner-full:         9999px;   /* Pill shape */
}
```

### 4.2 Pemetaan Shape ke Komponen

| Komponen | Shape Token | Radius | Catatan |
|---|---|---|---|
| **Button (Filled/Tonal)** | `full` | pill | M3 standard button |
| **FAB** | `large` | 16px | Floating action |
| **Card** | `medium` | 12px | Default card shape |
| **Card (Elevated)** | `large` | 16px | Dashboard stat cards |
| **Dialog / Modal** | `extra-large` | 28px | M3 standard dialog |
| **Text Field** | `extra-small` (top) | 4px top | M3 filled text field |
| **Chip / Badge** | `small` | 8px | Filter chip |
| **Bottom Sheet** | `extra-large` (top) | 28px top | PWA bottom sheet |
| **Navigation Bar** | `none` | 0px | Full-width bar |
| **Snackbar / Toast** | `extra-small` | 4px | Notification |
| **Kamera Viewfinder** | `large` | 16px | Face detection frame |
| **Avatar / Photo** | `full` | circle | Foto profil |
| **Thumbnail Foto Presensi** | `medium` | 12px | Riwayat foto |

---

## 5. Elevation & Surface (Tonal Elevation)

### 5.1 Elevation Tokens

M3 menggunakan Tonal Elevation - warna surface berubah, bukan shadow berlebihan:

```css
:root {
    --md-sys-elevation-0: none;
    --md-sys-elevation-1: 0 1px 2px 0 rgba(0,0,0,0.05);
    --md-sys-elevation-2: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
    --md-sys-elevation-3: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --md-sys-elevation-4: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --md-sys-elevation-5: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
}
```

### 5.2 Pemetaan Elevation

| Level | Surface Token | Shadow | Digunakan Untuk |
|---|---|---|---|
| **0** | `surface` | none | Background halaman |
| **1** | `surface-container-low` | elevation-1 | Sidebar admin, card datar |
| **2** | `surface-container` | elevation-2 | Card standar, bottom nav |
| **3** | `surface-container-high` | elevation-3 | Card elevated, dialog |
| **4** | `surface-container-highest` | elevation-4 | FAB, dropdown menu |
| **5** | - | elevation-5 | Modal overlay |

---

## 6. Motion & Animation (Expressive Motion)

### 6.1 Easing Curves

```css
:root {
    /* M3 EASING - Snappy start, gentle landing */
    --md-sys-motion-easing-emphasized:           cubic-bezier(0.2, 0.0, 0, 1.0);
    --md-sys-motion-easing-emphasized-decelerate: cubic-bezier(0.05, 0.7, 0.1, 1.0);
    --md-sys-motion-easing-emphasized-accelerate: cubic-bezier(0.3, 0.0, 0.8, 0.15);
    --md-sys-motion-easing-standard:             cubic-bezier(0.2, 0.0, 0, 1.0);
    --md-sys-motion-easing-standard-decelerate:  cubic-bezier(0.0, 0.0, 0, 1.0);
    --md-sys-motion-easing-standard-accelerate:  cubic-bezier(0.3, 0.0, 1.0, 1.0);
    --md-sys-motion-easing-linear:               linear;
}
```

### 6.2 Duration Tokens

```css
:root {
    --md-sys-motion-duration-short1:    50ms;
    --md-sys-motion-duration-short2:    100ms;
    --md-sys-motion-duration-short3:    150ms;
    --md-sys-motion-duration-short4:    200ms;
    --md-sys-motion-duration-medium1:   250ms;
    --md-sys-motion-duration-medium2:   300ms;
    --md-sys-motion-duration-medium3:   350ms;
    --md-sys-motion-duration-medium4:   400ms;
    --md-sys-motion-duration-long1:     450ms;
    --md-sys-motion-duration-long2:     500ms;
    --md-sys-motion-duration-long3:     550ms;
    --md-sys-motion-duration-long4:     600ms;
    --md-sys-motion-duration-extra-long1: 700ms;
}
```

### 6.3 Pemetaan Animasi ke UI

| Interaksi | Duration | Easing | CSS |
|---|---|---|---|
| **Button ripple / press** | short4 (200ms) | standard | `transition: all 200ms var(--md-sys-motion-easing-standard)` |
| **Card hover lift** | medium2 (300ms) | emphasized | `transition: transform 300ms var(--md-sys-motion-easing-emphasized)` |
| **Page transition** | medium4 (400ms) | emphasized-decelerate | `transition: opacity 400ms var(--md-sys-motion-easing-emphasized-decelerate)` |
| **Bottom sheet slide-up** | medium4 (400ms) | emphasized-decelerate | `transform: translateY(0)` with easing |
| **Snackbar enter** | medium1 (250ms) | emphasized-decelerate | Slide-in from bottom |
| **Snackbar exit** | short4 (200ms) | emphasized-accelerate | Fade out |
| **Nav indicator slide** | medium2 (300ms) | emphasized | Active indicator animation |
| **FAB expand** | medium3 (350ms) | emphasized | Scale + fade |
| **Face detection bounding box** | short3 (150ms) | standard | Smooth box tracking |
| **GPS loading spinner** | extra-long1 (700ms) | linear | Continuous rotation |
| **Status badge appear** | short4 (200ms) | emphasized-decelerate | Scale from 0 -> 1 |

### 6.4 Micro-Animations CSS

```css
/* Ripple effect (M3 state layer) */
.md-ripple {
    position: relative;
    overflow: hidden;
}
.md-ripple::after {
    content: '';
    position: absolute;
    inset: 0;
    background: currentColor;
    opacity: 0;
    transition: opacity var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-standard);
}
.md-ripple:hover::after   { opacity: 0.08; }
.md-ripple:focus::after   { opacity: 0.12; }
.md-ripple:active::after  { opacity: 0.12; }

/* Card hover lift */
.md-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--md-sys-elevation-3);
    transition: all var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized);
}

/* Stat number fade-in */
@keyframes md-fade-in-up {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.md-animate-in {
    animation: md-fade-in-up var(--md-sys-motion-duration-medium4) var(--md-sys-motion-easing-emphasized-decelerate) forwards;
}

/* Pulse animation for live indicators */
@keyframes md-pulse {
    0%, 100% { opacity: 1; }
    50%      { opacity: 0.5; }
}
.md-pulse {
    animation: md-pulse 2s var(--md-sys-motion-easing-linear) infinite;
}

/* Skeleton loading shimmer */
@keyframes md-shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.md-skeleton {
    background: linear-gradient(90deg,
        var(--md-sys-color-surface-container) 25%,
        var(--md-sys-color-surface-container-high) 50%,
        var(--md-sys-color-surface-container) 75%
    );
    background-size: 200% 100%;
    animation: md-shimmer 1.5s infinite;
    border-radius: var(--md-sys-shape-corner-small);
}
```

---

## 7. Komponen UI (M3 Component Library)

### 7.1 Buttons

```css
/* Filled Button (Primary Action) */
.md-btn-filled {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 40px;
    padding: 0 24px;
    border: none;
    border-radius: var(--md-sys-shape-corner-full);
    background: var(--md-sys-color-primary);
    color: var(--md-sys-color-on-primary);
    font: var(--md-sys-typescale-label-large);
    letter-spacing: 0.1px;
    cursor: pointer;
    transition: all var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-standard);
}
.md-btn-filled:hover {
    box-shadow: var(--md-sys-elevation-1);
}
.md-btn-filled:disabled {
    background: color-mix(in srgb, var(--md-sys-color-on-surface) 12%, transparent);
    color: color-mix(in srgb, var(--md-sys-color-on-surface) 38%, transparent);
    cursor: not-allowed;
}

/* Tonal Button (Secondary) */
.md-btn-tonal {
    background: var(--md-sys-color-secondary-container);
    color: var(--md-sys-color-on-secondary-container);
}

/* Outlined Button */
.md-btn-outlined {
    background: transparent;
    color: var(--md-sys-color-primary);
    border: 1px solid var(--md-sys-color-outline);
}

/* Text Button */
.md-btn-text {
    background: transparent;
    color: var(--md-sys-color-primary);
    padding: 0 12px;
}

/* FAB (Floating Action Button) */
.md-fab {
    position: fixed;
    bottom: 80px;
    right: 16px;
    width: 56px;
    height: 56px;
    border: none;
    border-radius: var(--md-sys-shape-corner-large);
    background: var(--md-sys-color-primary-container);
    color: var(--md-sys-color-on-primary-container);
    box-shadow: var(--md-sys-elevation-3);
    cursor: pointer;
    transition: all var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized);
    z-index: 50;
}
.md-fab:hover {
    box-shadow: var(--md-sys-elevation-4);
    transform: scale(1.05);
}
```

### 7.2 Cards

```css
/* Elevated Card (Dashboard Stats) */
.md-card-elevated {
    background: var(--md-sys-color-surface-container-low);
    border-radius: var(--md-sys-shape-corner-large);
    box-shadow: var(--md-sys-elevation-1);
    padding: 16px;
    transition: all var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized);
}

/* Filled Card */
.md-card-filled {
    background: var(--md-sys-color-surface-container-highest);
    border-radius: var(--md-sys-shape-corner-medium);
    padding: 16px;
}

/* Outlined Card */
.md-card-outlined {
    background: var(--md-sys-color-surface);
    border: 1px solid var(--md-sys-color-outline-variant);
    border-radius: var(--md-sys-shape-corner-medium);
    padding: 16px;
}
```

### 7.3 Navigation

```css
/* Bottom Navigation Bar (PWA Mobile) */
.md-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-around;
    background: var(--md-sys-color-surface-container);
    box-shadow: var(--md-sys-elevation-2);
    z-index: 100;
    padding-bottom: env(safe-area-inset-bottom);
}
.md-bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: var(--md-sys-color-on-surface-variant);
    text-decoration: none;
    padding: 8px 20px;
    border-radius: var(--md-sys-shape-corner-full);
    transition: all var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-standard);
}
.md-bottom-nav-item.active {
    color: var(--md-sys-color-on-secondary-container);
}
.md-bottom-nav-item.active .md-nav-indicator {
    background: var(--md-sys-color-secondary-container);
    border-radius: var(--md-sys-shape-corner-full);
    padding: 4px 20px;
}

/* Sidebar Navigation (Admin Desktop) */
.md-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 280px;
    background: var(--md-sys-color-surface-container-low);
    padding: 16px 12px;
    overflow-y: auto;
    z-index: 100;
    transition: width var(--md-sys-motion-duration-medium4) var(--md-sys-motion-easing-emphasized);
}
.md-sidebar-item {
    display: flex;
    align-items: center;
    gap: 12px;
    height: 56px;
    padding: 0 16px;
    border-radius: var(--md-sys-shape-corner-full);
    color: var(--md-sys-color-on-surface-variant);
    text-decoration: none;
    font: var(--md-sys-typescale-label-large);
    transition: all var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-standard);
}
.md-sidebar-item:hover {
    background: color-mix(in srgb, var(--md-sys-color-on-surface) 8%, transparent);
}
.md-sidebar-item.active {
    background: var(--md-sys-color-secondary-container);
    color: var(--md-sys-color-on-secondary-container);
    font-weight: 600;
}
```

### 7.4 Status Badges & Chips

```css
/* Status Badges (Presensi) */
.md-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: var(--md-sys-shape-corner-small);
    font: var(--md-sys-typescale-label-medium);
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.md-badge-tepat-waktu  { background: var(--md-custom-color-success-container); color: var(--md-custom-color-success); }
.md-badge-terlambat    { background: var(--md-custom-color-warning-container); color: var(--md-custom-color-terlambat); }
.md-badge-izin         { background: #E3F2FD; color: var(--md-custom-color-izin); }
.md-badge-sakit        { background: #F3E5F5; color: var(--md-custom-color-sakit); }
.md-badge-cuti         { background: #E0F7FA; color: var(--md-custom-color-cuti); }
.md-badge-alpha        { background: var(--md-sys-color-error-container); color: var(--md-sys-color-error); }
.md-badge-pending      { background: var(--md-custom-color-warning-container); color: var(--md-custom-color-warning); }
.md-badge-approved     { background: var(--md-custom-color-success-container); color: var(--md-custom-color-success); }
.md-badge-rejected     { background: var(--md-sys-color-error-container); color: var(--md-sys-color-error); }

/* Notification Badge (Counter) */
.md-badge-notification {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: var(--md-sys-shape-corner-full);
    background: var(--md-sys-color-error);
    color: var(--md-sys-color-on-error);
    font: var(--md-sys-typescale-label-small);
    display: flex;
    align-items: center;
    justify-content: center;
}
```

---

## 8. Adaptive Layout (Responsive M3)

### 8.1 Breakpoints

```css
:root {
    /* M3 Adaptive Breakpoints */
    --md-breakpoint-compact:   0px;      /* 0-599px: Smartphone */
    --md-breakpoint-medium:    600px;    /* 600-839px: Tablet portrait */
    --md-breakpoint-expanded:  840px;    /* 840-1199px: Tablet landscape */
    --md-breakpoint-large:     1200px;   /* 1200-1599px: Desktop */
    --md-breakpoint-extra-large: 1600px; /* 1600px+: Large desktop */
}
```

### 8.2 Layout Grid

```css
/* PWA Mobile (Compact: 0-599px) */
.md-layout-pwa {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    padding: 16px;
    padding-bottom: 96px;
    max-width: 430px;
    margin: 0 auto;
}

/* Admin Desktop (Expanded: 840px+) */
.md-layout-admin {
    display: grid;
    grid-template-columns: 280px 1fr;
    min-height: 100vh;
}
.md-layout-admin-content {
    padding: 24px;
    max-width: 1200px;
}

/* Dashboard Stats Grid */
.md-stats-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

/* Responsive adjustments */
@media (max-width: 839px) {
    .md-layout-admin {
        grid-template-columns: 1fr;
    }
    .md-sidebar {
        transform: translateX(-100%);
    }
    .md-sidebar.open {
        transform: translateX(0);
    }
}
```

### 8.3 Layout per Halaman

| Halaman | Layout | Keterangan |
|---|---|---|
| **Login** | Centered card, full-bleed background | Logo PT. CAK + form login M3 |
| **PWA Dashboard** | Single column, card stack | Greeting + status hari ini + quick actions |
| **PWA Presensi** | Full screen camera + overlay | GPS info bar atas + camera center + tombol bawah |
| **PWA Riwayat** | Scrollable list, outlined cards | Per hari, expandable detail |
| **PWA Izin** | Form layout, M3 text fields | Step-by-step form |
| **Admin Dashboard** | Sidebar + stats grid + peta | 4 stat cards + peta Leaflet fullwidth |
| **Admin Monitoring** | Sidebar + table + filter bar | Data table M3 dengan sorting |
| **Admin Enrollment** | Sidebar + split view | Daftar karyawan kiri + webcam kanan |
| **Admin Laporan** | Sidebar + filter + preview | Date range picker + format selector + preview |
| **Superadmin Lokasi** | Sidebar + peta + form | Peta interaktif + CRUD form samping |

---

## 9. Iconography

### Material Symbols (Rounded, Weight 400)

Load dari Google Fonts:
```html
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
```

| Fitur | Nama Material Symbol |
|---|---|
| **Dashboard** | `dashboard` |
| **Presensi / Absensi** | `how_to_reg` |
| **Riwayat** | `history` |
| **Izin / Cuti** | `description` |
| **Profil** | `person` |
| **Kamera** | `photo_camera` |
| **Lokasi / GPS** | `location_on` |
| **Peta** | `map` |
| **Laporan** | `assessment` |
| **Settings** | `settings` |
| **Notifikasi** | `notifications` |
| **Enrollment Wajah** | `face` |
| **Approve** | `check_circle` |
| **Reject** | `cancel` |
| **Logout** | `logout` |
| **Tepat Waktu** | `schedule` |
| **Terlambat** | `warning` |
| **Dark Mode Toggle** | `dark_mode` |

---

## 10. Dark Mode Implementation

### Toggle Mechanism

```javascript
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme-preference', next);
}

const preference = localStorage.getItem('theme-preference')
    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
document.documentElement.setAttribute('data-theme', preference);
```

### PWA Theme Color (Dynamic)

```html
<!-- Light mode -->
<meta name="theme-color" content="#FAFAFA" media="(prefers-color-scheme: light)">
<!-- Dark mode -->
<meta name="theme-color" content="#131316" media="(prefers-color-scheme: dark)">
```

---

## 11. Spacing & Grid Tokens

```css
:root {
    /* M3 spacing scale (4px base) */
    --md-sys-spacing-1:   4px;
    --md-sys-spacing-2:   8px;
    --md-sys-spacing-3:   12px;
    --md-sys-spacing-4:   16px;
    --md-sys-spacing-5:   20px;
    --md-sys-spacing-6:   24px;
    --md-sys-spacing-7:   28px;
    --md-sys-spacing-8:   32px;
    --md-sys-spacing-10:  40px;
    --md-sys-spacing-12:  48px;
    --md-sys-spacing-16:  64px;
}
```

---

## 12. File CSS Implementasi

Semua token dan komponen di atas diimplementasikan dalam file-file berikut:

```
resources/
└── css/
    ├── m3-tokens.css          /* Semua CSS custom properties (color, type, shape, motion, spacing) */
    ├── m3-components.css      /* Button, card, navigation, badge, dialog, form field styles */
    ├── m3-utilities.css       /* Typescale classes, spacing helpers, animation classes */
    ├── m3-layout-pwa.css      /* Mobile PWA layout & bottom nav */
    └── m3-layout-admin.css    /* Desktop admin sidebar layout */
```

Import di Blade layout:
```html
<!-- layouts/pwa.blade.php -->
<link rel="stylesheet" href="{{ asset('css/m3-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-components.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-layout-pwa.css') }}">

<!-- layouts/admin.blade.php -->
<link rel="stylesheet" href="{{ asset('css/m3-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-components.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('css/m3-layout-admin.css') }}">
```

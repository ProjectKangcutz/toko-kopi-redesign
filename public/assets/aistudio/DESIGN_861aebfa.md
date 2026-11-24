---
name: Nusantara Roast & Commerce
colors:
  surface: '#f5fbf6'
  surface-dim: '#d5dbd6'
  surface-bright: '#f5fbf6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff5f0'
  surface-container: '#e9efea'
  surface-container-high: '#e4eae5'
  surface-container-highest: '#dee4df'
  on-surface: '#171d1a'
  on-surface-variant: '#404943'
  inverse-surface: '#2c322f'
  inverse-on-surface: '#ecf2ed'
  outline: '#707973'
  outline-variant: '#c0c9c2'
  surface-tint: '#316951'
  primary: '#04442f'
  on-primary: '#ffffff'
  primary-container: '#245c45'
  on-primary-container: '#99d2b5'
  inverse-primary: '#99d3b6'
  secondary: '#775845'
  on-secondary: '#ffffff'
  secondary-container: '#ffd4bd'
  on-secondary-container: '#795a48'
  tertiary: '#553400'
  on-tertiary: '#ffffff'
  tertiary-container: '#744900'
  on-tertiary-container: '#ffb857'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#b5efd1'
  primary-fixed-dim: '#99d3b6'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#16503a'
  secondary-fixed: '#ffdbc8'
  secondary-fixed-dim: '#e7bea8'
  on-secondary-fixed: '#2c1608'
  on-secondary-fixed-variant: '#5d402f'
  tertiary-fixed: '#ffddb6'
  tertiary-fixed-dim: '#ffb959'
  on-tertiary-fixed: '#2a1800'
  on-tertiary-fixed-variant: '#643f00'
  background: '#f5fbf6'
  on-background: '#171d1a'
  surface-variant: '#dee4df'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.03em
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.025em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
  price-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.01em
  price-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '700'
    lineHeight: 22px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '600'
    lineHeight: 18px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-sm: 1rem
  gutter-lg: 2rem
  margin: 1.5rem
  margin-mobile: 1rem
  margin-desktop: 3rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system expresses the craft, terroir, and cultural elevation of Indonesian specialty coffee—from the volcanic soils of Gayo and Toraja to modern single-origin microlots. 

The aesthetic is modern editorial commerce: rooted, warm, tactile, and trustworthy. It balances agricultural provenance with crisp, contemporary transaction flows. High contrast, precise layout geometry, and generous surface breathing room instill user confidence across both wholesale supply orders and direct-to-consumer purchases.

Target audience: Specialty coffee aficionados, cafe owners, roasters, and conscious consumers seeking transparent sourcing and regional origin profiles.

## Colors

The color palette reflects the lifecycle of Indonesian coffee cultivation:
- **Primary (`#245C45`):** Deep highland forest green, capturing lush canopy shade and raw origin. Used for primary brand moments, key actions, active navigation, and editorial accents.
- **Secondary (`#3B2314`):** Dark espresso roast, providing foundational grounding, deep typography accents, and rich structural dividers.
- **Tertiary (`#E9A23B`):** Warm golden caramel amber. Reserved for conversion anchors, primary marketplace CTAs, harvest release badges, and discount tags. Always paired with dark charcoal text (`#1F241F`) to maintain strict WCAG AA legibility.
- **Neutral (`#1E2421`):** Deep charcoal pine, ensuring sharp, fatigue-free editorial reading.
- **Surface Canvas:** Tinted with layered oat/sage cream tones (`#F7F5F0` base body canvas, `#E9F1E9` tint for origin highlight zones, `#FFFFFF` for product card tiles). Borders remain soft and clean (`#E2E8E4`).

## Typography

Plus Jakarta Sans powers the entire typographic voice, balancing contemporary Indonesian geometric clarity with open counters and balanced numerals.

### Numerical and Editorial Guidelines
- **Tabular Figures:** Always apply `font-variant-numeric: tabular-nums` to currency indicators (`Rp`), inventory counters, elevation ratings (e.g., `1,450 MASL`), and bag weights (`250g`, `1kg`).
- **Currency Styling:** Format Indonesian Rupiah without spaces after the symbol (e.g., `Rp185.000`), using `font-weight: 700` to anchor scanning patterns.
- **Metadata Consistency:** Use `label-sm` in uppercase with subtle letter spacing for SKU numbers, processing tags (Anaerobic Natural, Full Washed, Wet-Hulled), and flavor profile markers (Jasmine, Bergamot, Brown Sugar).

## Layout & Spacing

The design system employs a structured 12-column responsive fluid grid with maximum canvas bounding at 1320px for desktop catalog views.

- **Mobile (< 768px):** 4 columns, `margin-mobile` (16px), and `gutter-sm` (16px). Product listings display in an asymmetric 2-column card format or 1-column featured card format.
- **Tablet (768px – 1024px):** 8 columns, `margin` (24px), and `gutter` (24px).
- **Desktop (> 1024px):** 12 columns, `margin-desktop` (48px), and `gutter-lg` (32px). Single-origin roaster profile pages and checkout drawers adhere to clean fractional column pairs (e.g., 8-column media/tasting story + 4-column sticky purchase module).

## Elevation & Depth

Visual depth is achieved through tactile tonal layering and low-contrast borders rather than heavy drop shadows:

- **Surface Tiers:** 
  - Base canvas: Warm oat tint (`#F7F5F0`).
  - Sectional grouping: Sage wash (`#E9F1E9`).
  - Interactive cards & inputs: Crisp white (`#FFFFFF`).
- **Border Structure:** Structural clarity relies on subtle 1px outlines (`#E2E8E4`).
- **Shadow Behavior:** When layering is required (floating sticky cart bars, product detail modals, autocomplete search panels), use an ambient earthy tint: `0 8px 24px -4px rgba(36, 92, 69, 0.08), 0 2px 6px -1px rgba(59, 35, 20, 0.04)`.

## Shapes

The design system uses a deliberate, moderate curvature (`roundedness: 2`, base radius 8px). 

- **Base Components (`8px`):** Used across standard product cards, quantity selectors, input fields, and standard buttons.
- **Extended Elements (`16px`):** Applied to promotional banners, roaster origin story cards, and review containers.
- **Pill Exceptions (`9999px`):** Strictly reserved for dynamic tasting note tags, roast profile indicators, processing tags, and active cart quantity indicators.

## Components

### Buttons
- **Primary Conversion CTA:** Background `#E9A23B`, text `#1F241F` (bold, high-contrast), height 48px, rounded 8px. Hover shifts to `#D68F29`.
- **Secondary Brand Action:** Background `#245C45`, text `#FFFFFF`, rounded 8px.
- **Tertiary / Outline:** Background transparent, 1.5px border in `#245C45`, text `#245C45`.

### Product Cards
- **Construction:** `#FFFFFF` background, 1px `#E2E8E4` border, 8px corner radius.
- **Layout:** High-ratio product image with soft oat background padding, followed by roaster tag (`#5A6560`), bean variety/origin heading (`#1E2421`), tabular pricing (`#245C45`), and grind profile dropdown.

### Badges & Chips
- **Harvest & Batch Badges:** Pill-shaped, `#E9F1E9` background with `#245C45` text.
- **Promo / Limited Release:** `#E9A23B` background with `#1F241F` text.
- **Flavor Notes:** Small pill badges with `#F7F5F0` background, `#3B2314` text, and subtle `#E2E8E4` border.

### Inputs & Selectors
- **Text Inputs:** `#FFFFFF` background, 1px `#E2E8E4` border, 8px radius. Active focus switches border to `#245C45` with a 2px `#245C45` ring at 20% opacity.
- **Grind Selection (Whole Bean, Espresso, V60, Cold Brew):** Segmented chip buttons with active state filled in `#245C45` with `#FFFFFF` text.

### Checkboxes & Radio Controls
- **Form Controls:** Custom square (rounded 4px) and circular radios with `#245C45` fill upon check, paired with `#FFFFFF` check icons. Unchecked state uses `#E2E8E4` border against `#FFFFFF`.

### Roaster Terroir Ribbon (Custom Domain Component)
- An origin block highlighting Altitude, Varietal, Region, and Farmer details, placed within `#E9F1E9` containers using small tabular figures and subtle uppercase label dividers.
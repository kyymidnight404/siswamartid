# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/siswamart/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

> **SOURCE OF TRUTH:** Colors, radii, borders, shadows and font stack mirror
> `public/css/siswamart-theme.css` (`--sm-*` tokens) and `resources/css/app.css`.
> If this document and those files disagree, **the CSS wins** — update this file, not the app.

---

**Project:** SiswaMart
**Generated:** 2026-10-31
**Category:** E-commerce / School Marketplace (public storefront + seller panel + admin panel)
**Stack:** Laravel 13, Blade components, Tailwind CSS v4, Alpine.js, Vite

---

## Global Rules

### Color Palette ("biru redam + amber", 60-30-10)

| Role | Hex | CSS Variable | Share |
|------|-----|--------------|-------|
| Background (neutral) | `#F5F8FC` | `--sm-bg` | 60% |
| Surface / card | `#FFFFFF` | `--sm-surface` | 60% |
| Primary soft | `#E3ECF5` | `--sm-primary-soft` | 60% |
| Primary | `#3F6A96` | `--sm-primary` | 30% |
| Primary hover | `#35597E` | `--sm-primary-hover` | 30% |
| Ink (text + borders) | `#1F3550` | `--sm-ink` | 30% |
| Text muted | `#4C6078` | `--sm-text-muted` | 30% |
| On primary | `#F5F8FC` | `--sm-on-primary` | 30% |
| Accent / CTA | `#F2B84B` | `--sm-accent` | 10% |
| Accent hover | `#E5A735` | `--sm-accent-hover` | 10% |
| On accent | `#1F2A3A` | `--sm-on-accent` | 10% |
| Border | `#1F3550` | `--sm-border` | — |
| Input border | `#7D93AC` | `--sm-input-border` | — |
| Error | `#8B1E2D` | `ui-error` | — |

**Verified contrast (WCAG AA 4.5:1):** ink/bg 11.71:1 · muted/bg 6.06:1 · on-primary/primary 5.31:1 · on-accent/accent 8.09:1 · error/surface 9.05:1 · primary as text/surface 5.66:1.

**Rules:** never place `--sm-primary` text on `--sm-primary-soft` without checking (5.66:1 on white, lower on tint). Never use accent amber for body text. Never hardcode raw hex in Blade/Tailwind utilities — use the `--sm-*` variables.

### Typography

- **Body / UI:** Plus Jakarta Sans (`html { font-family }` in `app.css`)
- **Display / accent:** Fredoka (logo, section eyebrows, playful headings)
- **Legacy:** Breeze layouts still load **Figtree** — inconsistent with the shared system; migrate to Plus Jakarta Sans when those views are touched.
- **Mood:** friendly, youthful, product-forward, high-legibility

**Rules:** body 16px minimum, line-height 1.5, heading weight 700-800 (existing `ui-page-header h1` = 28px/800). Do not introduce a third font family.

### Spacing

| Token | Value | Usage |
|-------|-------|-------|
| `--space-xs` | `4px` | Tight icon gaps |
| `--space-sm` | `8px` | Inline spacing |
| `--space-md` | `16px` | Control padding |
| `--space-lg` | `24px` | Card padding |
| `--space-xl` | `32px` | Block gaps |
| `--space-2xl` | `48px` | Section gaps (`--section-gap` min) |
| `--space-3xl` | `64px` | Hero / large rhythm (`--section-gap` max) |

**Containers:** `--container: 1200px` (app), `--container-home: 1280px` (storefront). Gutters `clamp(16px, 4vw, 24px)` / `clamp(18px, 3.5vw, 48px)`.

### Structure Tokens (neobrutalist)

| Token | Value |
|-------|-------|
| `--border-w` | `2.5px` (general), `3px` on `.ui-card` |
| `--r-card` / `--r-tile` | `28px` (cards), `24px` on `.ui-card` / `.product-card` |
| `--r-block` | `36px` |
| `--r-input` | `18px` (`.ui-control` uses `14px`) |
| `--r-pill` | `999px` |
| `--shadow` | `4px 4px 0 var(--sm-ink)` |
| `--shadow-hover` | `6px 6px 0 var(--sm-ink)` |
| `--shadow-active` | `2px 2px 0 var(--sm-ink)` |

Hard offset shadows are the signature. They are **not** decorative blur: always solid, always ink-colored, always offset down-right.

---

## Component Specs

### Buttons

```css
/* Primary — accent, pill, hard shadow */
.btn-primary {
  min-height: 46px;                 /* exceeds 44px touch target */
  background: var(--sm-accent);
  color: var(--sm-on-accent);
  border: 3px solid var(--sm-ink);
  border-radius: 999px;
  box-shadow: 0 4px 0 var(--sm-ink);
  font-weight: 700;
  transition: transform 200ms ease, box-shadow 200ms ease;
  cursor: pointer;
}
.btn-primary:hover { transform: translateY(-1px); }
.btn-primary:active { transform: translateY(2px); box-shadow: var(--shadow-active); }

/* Secondary — surface, same geometry */
.btn-secondary {
  background: var(--sm-surface);
  color: var(--sm-ink);
  border: 3px solid var(--sm-ink);
}
```

Press state must move **down** and **shrink** the shadow; never fade opacity to indicate press.

### Inputs (`.ui-field`, `.ui-control`, `.ui-help`, `.ui-error`)

```css
.ui-control {
  min-height: 46px;
  border: 2px solid var(--sm-input-border);
  border-radius: 14px;
  font-size: 16px;                  /* prevents iOS zoom-on-focus */
}
.ui-control:focus {
  border-color: var(--sm-primary);
  box-shadow: 0 0 0 4px rgb(63 106 150 / 25%);
}
```

Every control needs a visible `<label>` above it (`.ui-field > label`). Helper text via `.ui-help`, field error via `.ui-error`, referenced with `aria-describedby`.

### Cards

`.ui-card` (3px border, 24px radius, `0 5px 0` shadow, 24px padding) for panels;
`.product-card` / `.category-card` for catalog tiles with translate + shadow growth on hover.

### Modals

Overlay `rgba(0,0,0,0.5)`, panel `var(--sm-surface)`, `--r-card` radius, `--shadow`, `--shadow-lg` equivalent. **Must** trap focus, close on `Escape`, restore focus to trigger, and expose `aria-modal="true"` + labelled title. Existing `resources/views/components/modal.blade.php` is Alpine-based — verify focus trap when it is used for moderation dialogs.

---

## Style Guidelines

**Style:** Vibrant & Block-based (neobrutalist execution) — bold blocks, hard borders, offset shadows, high color contrast, playful display type.

**Best for:** youth-focused consumer marketplaces, creative catalogs, school community products.

**Motion:** 150-300ms, transforms and shadows only (never `width`/`height`/`top`/`left`), respect `prefers-reduced-motion`.

### Page Patterns

- **Public storefront:** hero (value prop + search) > featured products > categories > how ordering works (WhatsApp) > trust/school proof > CTA.
- **Seller panel:** KPI row > pending moderation status > product table > recent reviews.
- **Admin panel:** global KPI row > moderation queue > seller accounts > categories/comments > export actions.

> The generic "Feature-Rich Showcase" hero/feature-grid ordering from the catalog applies to the public storefront only. It does **not** apply to dashboards — see the page overrides.

---

## Data Display (charts)

| Need | Use | Never |
|------|-----|-------|
| KPI vs target | Bullet chart / progress bar with target marker | Gauge alone |
| Trend over time | Line / area chart, direct series labels | Color-only series distinction |
| Distribution | Horizontal bar, sorted | Pie with >5 slices |

Always: text labels for values and thresholds, a table fallback for screen readers, `role="img"` + `aria-label` summary, keyboard-reachable detail. Existing dashboards render metrics as cards — keep numbers as text, not chart-only.

---

## Accessibility Floor (non-negotiable)

- Text contrast >= 4.5:1 (verified pairs above).
- Visible focus on every interactive control; never `outline: none` without a ring replacement.
- `scroll-padding-top: 96px` is already set — keep sticky headers from covering focused elements (WCAG 2.2 *Focus Not Obscured*).
- Touch targets >= 44x44px; 46px is the house minimum.
- Icon-only buttons need `aria-label`; decorative icons need `aria-hidden="true"`.
- Form failure: inline error under each field **plus** a focusable error summary at the top of the form, linked to fields.
- Every image needs `alt`; product images need descriptive alt text, not filenames.
- Respect `prefers-reduced-motion`.

---

## Blade Implementation Rules (Laravel 13)

- Extract repeated markup into `x-*` components in `resources/views/components`; do not duplicate HTML blocks across `public/`, `penjual/`, `admin/`.
- Declare props with `@props([...])` and defaults; document the component API in the tag.
- Optional slots must default: `{{ $icon ?? '' }}`, never bare `{{ $icon }}`.
- Prefer `<x-card :title="$title">{{ $slot }}</x-card>` over `@include` with an inline array.
- Use semantic tokens (`--sm-*`) inside components so a page override can retheme without editing markup.

---

## Anti-Patterns (Do NOT Use)

- Emojis as icons — use SVG (Heroicons / Lucide) at consistent stroke width.
- Raw hex values in Blade or one-off Tailwind colors that bypass `--sm-*`.
- Removing focus outlines without a visible ring.
- Blurred/soft-only shadows — this system uses hard ink offsets.
- Opacity fade as the only press feedback.
- Tables or dense lists using pill radii + large offset shadows (unreadable at density) — use `.ui-card` geometry with 12-16px padding instead.
- Text or meaning conveyed by color alone (rating, moderation status, store open/closed).
- Instant state changes (0ms) — always 150-300ms transitions.
- Layout-shifting hover scaling (`scale`) — use transform translate + shadow only.

---

## Pre-Delivery Checklist

- [ ] Colors come from `--sm-*`; no new hex introduced
- [ ] Contrast >= 4.5:1 for all text (re-check any new pairing)
- [ ] Visible focus ring on every control, including inside modals
- [ ] Touch targets >= 44px; 46px for buttons/inputs
- [ ] Labels visible above inputs; errors inline + error summary
- [ ] Icon-only buttons labelled; decorative icons `aria-hidden`
- [ ] Modals trap focus, close on Escape, restore focus
- [ ] `prefers-reduced-motion` respected
- [ ] Hard offset shadows only; press states move down
- [ ] Responsive at 375 / 768 / 1024 / 1440 px; no horizontal scroll
- [ ] No content hidden behind the 96px sticky header offset
- [ ] No emojis used as UI icons
- [ ] Blade components use `@props` and defaulted slots

# Admin Dashboard — Page Overrides

> **PROJECT:** SiswaMart
> **Page Type:** Dashboard / Data View (density override)
> **Generated:** 2026-10-31

> **INHERITS:** All colors, fonts, radii, borders, shadows and accessibility rules from
> `design-system/siswamart/MASTER.md`. Only the deviations below apply.
> The generated catalog match (navy + Fira Code, "Enterprise Gateway" landing pattern) was
> **rejected** — it contradicts the shipped "biru redam + amber" brand. Do not reintroduce it.

---

## Layout Overrides

- **Max width:** full-width inside the panel shell (`--container` = 1200px for content blocks; tables may span wider on >= 1280px).
- **Grid:** 12-column; KPI row is 2 cols on mobile, 4 on `md`, 4-6 on `xl`.
- **Section order (dashboard, not a landing page):**
  1. Page header + breadcrumb (`ui-page-header`)
  2. Global KPI row (products, sellers, views, WhatsApp clicks)
  3. Moderation queue (primary action — the reason this page exists)
  4. Seller accounts / categories / comment moderation
  5. Export actions (PDF / Excel)
- No hero, no video, no client-logo carousel.

## Spacing Overrides (density 8/10)

- Card padding `16px` (not the master 24px) inside tables and queues.
- Gap between cards `12-16px`; between sections `24-32px`.
- Table row height `44-48px`; cell padding `8px 12px`.
- Use `--space-*` scale only — no arbitrary values.

## Typography Overrides

- No font overrides — Plus Jakarta Sans / Fredoka from MASTER.
- Table headers `12-13px`, weight 700, uppercase tracking `0.04em`.
- Numeric KPI values `28-32px` weight 800; tabular figures for aligned columns (`font-variant-numeric: tabular-nums`).
- Body/table text never below 14px; primary cell content 15-16px.

## Color Overrides

- Identity unchanged. Status must be **labelled, not color-only**:
  - `pending` — amber `--sm-accent` chip + text label
  - `aktif` / `diterima` — primary `--sm-primary` chip + label
  - `ditolak` — `#8B1E2D` + label + reason text
- Chips use 6-8px radius, not full pills, at this density.

## Component Overrides

- Replace offset-shadow geometry with `ui-card` (3px border, `0 5px 0`) for containers; **no** `4px 4px 0` shadows on individual table rows.
- Sticky table header allowed; must not obscure focused rows (WCAG 2.2 Focus Not Obscured).
- Destructive actions (reject product with `alasan_ditolak`, deactivate seller) need confirmation + reason field + focus moved to the reason input.
- Bulk actions: selection count announced, `aria-live="polite"`.

## Data Display

- KPI cards: value as text, delta as `+/-` with label, optional sparkline. Never chart-only.
- No target/benchmark defined for most metrics -> **do not** force bullet/gauge charts. Use stat cards + trend line where a time axis exists.
- Every table has a caption or `aria-label` and sortable headers with `aria-sort`.

## Verification Before Shipping

- [ ] Tab order follows visual order through queue -> detail -> action
- [ ] Reject/approve dialogs trap focus and restore it to the trigger
- [ ] Status chips readable without color (label present)
- [ ] Table usable at 375px (horizontal scroll inside a labelled region, not page scroll)
- [ ] No row hover that shifts layout
- [ ] Export buttons labelled with outcome ("Unduh PDF", not just an icon)

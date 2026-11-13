# Seller Dashboard — Page Overrides

> **PROJECT:** SiswaMart
> **Page Type:** Dashboard / Data View (density override)
> **Generated:** 2026-10-31

> **INHERITS:** All colors, fonts, radii, borders, shadows and accessibility rules from
> `design-system/siswamart/MASTER.md`. Only the deviations below apply.
> The generated catalog match (navy + Fira Code, "Enterprise Gateway" landing pattern) was
> **rejected** — it contradicts the shipped "biru redam + amber" brand. Do not reintroduce it.

---

## Layout Overrides

- **Max width:** 1200px content; full-width tables on `xl`.
- **Grid:** 12-column. Performance tiles 2-across on mobile, 4-across on `md+`.
- **Section order (seller task flow, not a landing page):**
  1. Store identity + open/closed switch (`buka` / `tutup`) — highest salience
  2. Performance tiles: total products, views, WhatsApp clicks, average rating
  3. Pending moderation state (`pending_action` / `pending_changes`) — what needs attention
  4. Product management table with create/edit/delete affordances
  5. Recent reviews
  6. Export (PDF / Excel)
- No hero, no video, no logo carousel.

## Spacing Overrides (density 8/10)

- Card padding `16px`; KPI tile padding `14-16px`.
- Grid gap `12px` on mobile, `16px` on desktop.
- Product table rows `48-56px` (image thumbnails need breathing room); cell padding `8px 12px`.
- Section gaps `24-32px`.

## Typography Overrides

- No font overrides — Plus Jakarta Sans / Fredoka from MASTER.
- KPI numbers `28-32px` weight 800, `tabular-nums`; labels `13-14px` muted.
- Rating shown as number + stars + review count text (e.g. "4.6 dari 5 · 12 ulasan"), never stars alone.
- Table text >= 14px.

## Color Overrides

- Store state must be labelled: `buka` = primary chip + text; `tutup` = muted chip + text. Never rely on color/opacity alone.
- Moderation state: `pending` amber + label; `ditolak` `#8B1E2D` + reason. Show **what** is pending (new product vs. edit vs. delete).
- "Menunggu persetujuan" must never look identical to "live on storefront" — the seller's product stays visible publicly during revision, and the UI must say so.

## Component Overrides

- Containers use `ui-card` geometry; avoid offset shadows on rows.
- The open/closed toggle needs `role="switch"`, `aria-checked`, a text state alongside the control, and optimistic-feedback safety (spinner or disabled state during the request).
- Image upload / product form: visible labels, inline errors, error summary at top on failed submit, preview with alt-text input.
- Destructive delete requires confirmation and must state the consequence (product removal from the public catalog).

## Data Display

- Views and WhatsApp clicks are counts, not rates — show absolute numbers with the period ("30 hari terakhir").
- If a trend line is added, use a time axis with >= 4 points; otherwise use a stat card with delta.
- Chart legend + text labels; do not encode series by hue alone.

## Verification Before Shipping

- [ ] Pending-for-review state distinguishable from live state without color
- [ ] Toggle states and errors announced (`aria-live`)
- [ ] Product form: labels visible, errors inline + summary, focus moved to summary
- [ ] Table scrolls within a labelled region at 375px
- [ ] Export buttons labelled with outcome
- [ ] No layout shift on hover; press states move down

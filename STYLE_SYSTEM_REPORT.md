# Ecoride Style System Report

## 1. Theme Colors

### Core palette tokens (`:root`)
- Brand:
  - `--forest: #0f2a1d`
  - `--lime: #c4f547`
  - `--black: #000000`
  - `--white: #ffffff`
- Grays:
  - `--gray-50: #f9fafb`
  - `--gray-100: #f3f4f6`
  - `--gray-200: #e5e7eb`
  - `--gray-300: #d1d5db`
  - `--gray-400: #9ca3af`
  - `--gray-500: #6b7280`
  - `--gray-600: #4b5563`
  - `--gray-700: #374151`
  - `--gray-800: #1f2937`
  - `--gray-900: #111827`

- Status/accent:
  - Reds: `--red-50/100/200/500/600/700`
  - Greens: `--green-50/100/600/700`
  - Yellows: `--yellow-100/400/600/700/800`
  - Blue: `--blue-100/700`
  - Purple: `--purple-100/700`

### Practical theme intent
- Primary brand contrast: `forest` + `lime`
- Neutral UI base: grayscale ladder (50 to 900)
- Surface style: mostly white cards on light-gray borders, with dark nav shells
- Active/highlight state across dashboards: lime

## 2. Default Typography

### Global defaults
- Default family: `var(--font-sans)` -> `"Inter", system-ui, -apple-system, sans-serif`
- Display family: `var(--font-display)` -> `Impact, Haettenschweiler, "Arial Narrow Bold", sans-serif`
- Default text color: `var(--gray-900)`
- Default background: `var(--white)`
- Default line-height: `1.5`
- Default base size:  `16px`

### Text size scale utilities
- `text-xs`: `12/16`
- `text-sm`: `14/20`
- `text-base`: `16/24`
- `text-lg`: `18/28`
- `text-xl`: `20/28`
- `text-2xl`: `24/32`
- `text-3xl`: `30/36`
- `text-4xl`: `36/40`
- `text-5xl`: `48/1`

### Weight scale
- `font-medium` 500
- `font-bold` 700
- `font-extrabold` 800
- `font-black` 900

## 3. Spacing, Layout, and Breakpoints

### Core container system
- Main container max width: `1200px`
- Additional container caps:
  - `container-sm`: `640px`
  - `container-md`: `896px`
  - `container-lg`: `1024px`
  - `container-xl`: `1280px`

### Spacing scale (4px base)
- Utility spacing values follow 4px increments:
  - `1=4`, `2=8`, `3=12`, `4=16`, `5=20`, `6=24`, `8=32`, `12=48`
  - Extended: `24=96`, `32=128`

### Breakpoints
- `sm`: `min-width: 640px`
- `md`: `min-width: 768px`
- `lg`: `min-width: 1024px`

### Dashboard layout defaults
- `.dashboard-main`: `padding 24 16 96`, max width `1200`, centered
- At `md`: `.dashboard-main` becomes `padding 32 24 48`

## 4. Navigation System Style

### Shared nav structure (`style.css`)
- Top nav:
  - Sticky, full-width, `z-index: 50`
  - Inner max width `1200`
  - Item text size `13px`, weight `700`
- Bottom nav:
  - Fixed bottom bar on mobile, hidden at `md+`
  - Height `56px`

### Role nav color scheme (`admin.css`, `driver.css`, `rider.css`)
- Shell background: `black`
- Default nav text: `gray-400`
- Hover nav text: `white`
- Active nav text: `lime`
- Active marker dot: `lime`

### Nav icon styling
- Icons are file-based `<img>` with class `icon-img`
- Current icon state style in nav:
  - Default: gray-ish filter
  - Hover: white filter
  - Active: lime filter

## 5. Dashboard Typography and Sections

### Core dashboard headings
- `.dash-title`: 24px / 900 / uppercase / tight tracking
- `.dash-subtitle`: 14px / gray-500
- `.page-title`: 24px / 900 / uppercase
- `.page-subtitle`: 14px / gray-500
- `.section-title`: 18px / 700 / uppercase

### Responsive heading changes at `md`
- `.dash-title`: `36px`
- `.page-title`: `30px`

### Common section primitives
- `.section-block`: `margin-bottom: 32px`
- `.section-header`: horizontal row, `gap: 8px`, `margin-bottom: 16px`
- `.section-icon`: `32x32`, rounded, centered

## 6. Component Style Summary

### Buttons
- Base: inline-flex, rounded `8px`, weight `700`, transitions
- `btn-primary`: black background, white text
- `btn-lime`: lime background, black text
- `btn-outline`: transparent + gray border/text
- `btn-danger`: red-accented outline

### Forms
- Inputs: light-gray background, gray border, rounded `8px`, `12x16` padding
- Focus ring: lime 2px shadow
- Labels: small uppercase metadata style (11px, gray-500)

### Cards
- White surface, gray border, rounded `12px`
- Hover shadow escalation
- Default body padding `24px`

### Badges
- Pill badges with 11px uppercase text and semantic color variants
- Notable: lime badge uses transparent lime tint + forest text

### Data displays
- Tables: gray header strip, 11px uppercase headings
- Stat cards: strong numeric hierarchy, subtle borders/shadows
- Ongoing card: yellow-accent border/status bar

### Modal system
- Overlay blur with dark scrim
- Animated entrance (`translateY`)
- Body scroll lock on open

## 7. Motion and Interaction

### Keyframes
- `fadeIn`, `fadeInUp`, `slideInLeft`, `slideInRight`, `pulse`, `bounce`, `growBar`, `growWidth`

### Interaction timing
- Most transitions use `0.2s`
- Modal opacity/transform use `0.3s`
- Scroll animation utilities use fade classes and delay variants

## 8. Page-Specific Visual Identity

### Landing (`index.css`)
- High-contrast hero with forest/lime branding
- Large display typography, uppercase headlines
- Layered gradients + blend effects + background imagery

### Auth (`auth.css`)
- Clean white forms + forest/lime accents
- Role cards use subtle tinted states
- Strong uppercase headline style

### Role dashboards (`admin/driver/rider`)
- Consistent dark nav + lime active language
- Shared dashboard spacing/heading system
- Role-specific modules:
  - Admin: badge + admin nav variants
  - Driver: stat cards (`lime-stat`, `white-stat`)
  - Rider: same dashboard primitives without driver stat variants

## 9. System Character (Design Language)

- **Tone:** high contrast, modern utility-first, data-dashboard focused
- **Brand expression:** lime-on-dark accents with forest green identity
- **Text hierarchy:** strong uppercase display titles, compact metadata
- **Component rhythm:** rounded cards, 8px-based spacing, quick hover feedback
- **Responsive behavior:** mobile-first with desktop enhancement at `768px+` and `1024px+`

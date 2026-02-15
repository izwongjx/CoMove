# How To Apply `style.css`, `dashboard.css`, and Dashboard Classes

Use this when building Rider, Driver, and Admin pages.

## 1. Add CSS in the correct order

Put this inside `<head>`:

```html
<link rel="stylesheet" href="../../public-assets/css/style.css">
<link rel="stylesheet" href="../../public-assets/css/dashboard.css">
<link rel="stylesheet" href="rider.css">
```

Replace `rider.css` with `driver.css` or `admin.css` for those roles.

Effects:
- `style.css`: global variables, base layout, shared utility styles.
- `dashboard.css`: shared dashboard layout + role nav/dashboard styling.
- role CSS: role/page-specific overrides.

## 2. Dashboard page structure (main dashboard)

Use `dash-title` and `dash-subtitle` for the main dashboard landing page.

```html
<main class="dashboard-main">
  <div class="dash-container">
    <div class="dash-welcome">
      <h1 class="dash-title">Dashboard <span class="lime-box">Title</span></h1>
      <p class="dash-subtitle">Dashboard Subtitle</p>
    </div>
  </div>
</main>
```

Effects:
- `dashboard-main`: page container, centered, responsive padding.
- `dash-container`: max width 1200px.
- `dash-welcome`: bottom spacing below welcome area.
- `dash-title`: bold uppercase heading, 24px mobile / 36px desktop.
- `dash-subtitle`: smaller muted text under dashboard heading.
- `lime-box`: lime highlight chip with black background (currently used in dashboard titles).

## 3. Subpage structure (Find Rides, Rewards, etc.)

Use `page-title` and `page-subtitle` for non-landing pages.

```html
<main class="dashboard-main">
  <div class="dash-container">
    <h1 class="page-title">Page Title</h1>
    <p class="page-subtitle">Page Subtitle</p>
  </div>
</main>
```

Effects:
- `page-title`: bold uppercase section/page heading, 24px mobile / 30px desktop.
- `page-subtitle`: muted helper text with spacing below.

## 4. Section classes (content blocks inside pages)

```html
<section class="section-block">
  <div class="section-header">
    <div class="section-icon">
      <span data-icon="map" data-icon-size="18"></span>
    </div>
    <h2>Section Heading</h2>
  </div>
  <h3 class="section-title">Optional Title</h3>
  <!-- section content -->
</section>
```

Effects:
- `section-block`: space below each section.
- `section-header`: horizontal row for icon + heading.
- `section-header h2`: uppercase, bold heading style.
- `section-icon`: 32x32 rounded icon holder, centered content.
- `section-title`: standalone uppercase title style for subsections.

## 5. Role nav classes and effects

Apply one role nav class based on page type:

- Rider top nav: `rider-nav-bg`
- Driver top nav: `driver-nav-bg`
- Admin top nav: `admin-nav-bg`

Bottom nav:
- Rider bottom nav: `bottom-nav rider-nav-bg`
- Driver bottom nav: `bottom-nav driver-bottom-nav`
- Admin bottom nav: `bottom-nav admin-bottom-nav`

Effects:
- role nav classes set dark nav background, muted default links, white hover, lime active item.
- admin nav also supports `admin-badge` (small lime "Admin" label near logo).

## 6. Driver-only classes in `dashboard.css`

These are currently for driver stat cards:

- `driver-stats-grid`: one-column grid with gaps.
- `driver-stat`: rounded stat card container.
- `lime-stat`: lime background card variant.
- `white-stat`: white background card variant with gray border.
- `white-stat .stat-small-label` and `white-stat .stat-note`: muted text tones.

## 7. Quick checklist

- Every role page links `style.css` then `dashboard.css` then role CSS.
- Dashboard landing pages use `dash-title` and `dash-subtitle`.
- Subpages use `page-title` and `page-subtitle`.
- Section layouts use `section-block`, `section-header`, and `section-icon` consistently.

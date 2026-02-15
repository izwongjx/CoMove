# Ecoride CSS Framework - Complete Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [Dashboard Layout Structure](#dashboard-layout-structure)
3. [Layout & Spacing](#layout--spacing)
4. [Typography](#typography)
5. [Colors](#colors)
6. [Flexbox & Grid](#flexbox--grid)
7. [Navigation](#navigation)
8. [Cards & Stats](#cards--stats)
9. [Forms](#forms)
10. [Components](#components)
11. [Animations](#animations)
12. [Responsive Design](#responsive-design)
13. [Real Dashboard Examples](#real-dashboard-examples)

## Getting Started
The Ecoride framework uses a utility-first approach. Classes are composable, so you combine multiple small utility classes to build complex designs.

### Basic HTML Structure
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECORIDE - Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <!-- Your content here -->
</body>
</html>
```

## Dashboard Layout Structure
`dashboard.css` contains the dashboard-specific layout and role navigation styles.

### Page Title Section
```html
<!-- Method 1: Main dashboard landing -->
<div class="dash-welcome">
  <h1 class="dash-title">Welcome back, John!</h1>
  <p class="dash-subtitle">Here's what's happening with your rides today.</p>
</div>

<!-- Method 2: Inner pages -->
<h1 class="page-title">Driver Dashboard</h1>
<p class="page-subtitle">Manage your trips and earnings</p>
```

### Section Headers
```html
<!-- Section with icon -->
<div class="section-block">
  <div class="section-header">
    <div class="section-icon bg-lime">
      <span class="icon">ICON</span>
    </div>
    <h2>Today's Stats</h2>
  </div>
</div>

<!-- Section without icon -->
<div class="section-block">
  <h2 class="section-title">Recent Trips</h2>
</div>
```

### Complete Dashboard Structure
```html
<main class="dashboard-main">
  <div class="dash-welcome">
    <h1 class="dash-title">Welcome back, John!</h1>
    <p class="dash-subtitle">Here's what's happening with your rides today.</p>
  </div>

  <div class="section-block">
    <div class="section-header">
      <div class="section-icon bg-lime">
        <span class="icon">ICON</span>
      </div>
      <h2>Today's Overview</h2>
    </div>
    <div class="driver-stats-grid">
      <!-- Stats cards -->
    </div>
  </div>

  <div class="section-block">
    <div class="section-header">
      <div class="section-icon bg-forest text-white">
        <span class="icon">ICON</span>
      </div>
      <h2>Recent Activity</h2>
    </div>
    <div class="card">
      <div class="card-body">
        <!-- Card content -->
      </div>
    </div>
  </div>
</main>
```

## Layout & Spacing

### Container System
```html
<div class="container">
  <div class="card">
    <div class="card-body">
      <h3>Content inside container</h3>
    </div>
  </div>
</div>
```

### Padding Example
```html
<div class="p-8 bg-gray-50">
  <div class="p-6 bg-white rounded">
    <div class="p-4 border-l-4 border-lime">
      <p class="p-2">Content with multiple padding layers</p>
    </div>
  </div>
</div>
```

### Margin Example
```html
<div class="dashboard-main">
  <div class="dash-welcome mb-6">
    <h1 class="dash-title">Driver Dashboard</h1>
    <p class="dash-subtitle">Track your earnings and trips</p>
  </div>

  <div class="driver-stats-grid mb-8">
    <div class="driver-stat lime-stat">
      <div class="stat-value">$124.50</div>
      <div class="stat-note">Today's earnings</div>
    </div>
  </div>
</div>
```

## Typography
```html
<h1 class="dash-title mb-2">Main Dashboard Title</h1>
<h2 class="page-title mb-1">Page Section Title</h2>
<h3 class="section-title mb-3">Card Section Title</h3>

<p class="text-xs text-gray-400">12px metadata</p>
<p class="text-sm text-gray-600">14px secondary text</p>
<p class="text-base">16px body text</p>
<p class="text-lg font-bold">18px emphasized text</p>
```

## Colors
```html
<div class="grid grid-2 gap-4">
  <div class="p-6 bg-lime rounded-lg">
    <h3 class="font-black text-black mb-2">Eco Mode</h3>
    <p class="text-black text-opacity-75">Active now</p>
  </div>

  <div class="p-6 bg-forest rounded-lg">
    <h3 class="font-black text-white mb-2">Forest Green</h3>
    <p class="text-gray-200">Secondary text</p>
  </div>
</div>
```

## Flexbox & Grid

### Nested Flex Example
```html
<div class="stat-card">
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
      <div class="stat-icon bg-lime">
        <span class="icon">ICON</span>
      </div>
      <div>
        <div class="stat-value">$1,234</div>
        <div class="stat-label">Weekly Earnings</div>
      </div>
    </div>
    <span class="badge badge-green">12%</span>
  </div>
</div>
```

### Grid Layout Example
```html
<div class="grid grid-1 md-grid-2 gap-6">
  <div class="card"><div class="card-body">Left content</div></div>
  <div class="card"><div class="card-body">Right content</div></div>
</div>
```

## Navigation
```html
<nav class="top-nav admin-nav-bg">
  <div class="nav-inner">
    <a href="/" class="logo flex items-center gap-2">
      ECORIDE
      <span class="admin-badge">Admin</span>
    </a>
    <div class="nav-items">
      <a href="#" class="nav-item active">Dashboard</a>
      <a href="#" class="nav-item">Drivers</a>
    </div>
    <a href="#" class="nav-logout">Logout</a>
  </div>
</nav>

<nav class="bottom-nav admin-bottom-nav">
  <a href="#" class="active">Dashboard</a>
  <a href="#">Drivers</a>
</nav>
```

## Cards & Stats
```html
<div class="driver-stats-grid">
  <div class="driver-stat lime-stat">
    <div class="stat-small-label">TODAY'S EARNINGS</div>
    <div class="stat-value">$124.50</div>
    <div class="stat-note">from yesterday</div>
  </div>

  <div class="driver-stat white-stat">
    <div class="stat-small-label">TRIPS TODAY</div>
    <div class="stat-value">12</div>
    <div class="stat-note">4h 30m online</div>
  </div>
</div>
```

## Forms
```html
<div class="card">
  <div class="card-body">
    <form>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" placeholder="Enter email">
      </div>

      <div class="flex gap-3 mt-6">
        <button type="submit" class="btn btn-lime flex-1">Save Changes</button>
        <button type="button" class="btn btn-outline flex-1">Cancel</button>
      </div>
    </form>
  </div>
</div>
```

## Components
Common reusable components:
- `card` + `card-body`
- `badge` variants (`badge-green`, `badge-yellow`, `badge-red`, `badge-black`)
- `btn` variants (`btn-lime`, `btn-outline`, `btn-danger`)
- `avatar` sizes (`avatar-sm`, `avatar-md`, `avatar-xl`)
- `toggle` and `toggle-knob`

## Animations
Common animated states used in dashboards:
- `pulse-dot` for live status indicators
- hover transitions on `nav-item`, `btn`, and cards
- active/selected states for nav and tabs

## Responsive Design
```html
<!-- Mobile 1-col, tablet 2-col, desktop 4-col -->
<div class="grid grid-1 md-grid-2 lg-grid-4 gap-4"></div>

<!-- Mobile stack, tablet row -->
<div class="flex flex-col md-flex-row items-center gap-4"></div>
```

## Real Dashboard Examples

### Example 1: Admin Dashboard Skeleton
```html
<nav class="top-nav admin-nav-bg">...</nav>
<main class="dashboard-main">
  <div class="dash-welcome">
    <h1 class="dash-title">Admin Dashboard</h1>
    <p class="dash-subtitle">Platform overview</p>
  </div>
  <div class="grid grid-1 md-grid-2 lg-grid-4 gap-4">...</div>
</main>
<nav class="bottom-nav admin-bottom-nav">...</nav>
```

### Example 2: Driver Dashboard Skeleton
```html
<nav class="top-nav driver-nav-bg">...</nav>
<main class="dashboard-main">
  <div class="dash-welcome">
    <h1 class="dash-title">Ready to ride!</h1>
    <p class="dash-subtitle">You're online and available for trips.</p>
  </div>
  <div class="driver-stats-grid">...</div>
</main>
<nav class="bottom-nav driver-bottom-nav">...</nav>
```

## Best Practices Summary
1. Keep structure consistent: header, stats, sections.
2. Compose small utility classes instead of large one-off classes.
3. Reuse patterns: `section-header`, `card`, and `grid` layouts.
4. Always test mobile, tablet, and desktop breakpoints.

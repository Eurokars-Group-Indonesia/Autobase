# Responsive Design Skill (Bootstrap 5)

Provides responsive design patterns, breakpoints, and mobile-first development guidelines using Bootstrap 5.

## Bootstrap 5 Breakpoints

```css
xs: <576px     /* Extra small - Phones */
sm: ≥576px     /* Small - Portrait phones */
md: ≥768px     /* Medium - Tablets */
lg: ≥992px     /* Large - Desktops */
xl: ≥1200px    /* Extra large - Large desktops */
xxl: ≥1400px   /* Extra extra large - Extra large desktops */
```

## Mobile-First Approach

### CSS Structure
```css
/* Base styles (mobile) */
.component {
  display: block;
  padding: 1rem;
  width: 100%;
}

/* Tablet and up */
@media (min-width: 768px) {
  .component {
    display: flex;
    padding: 2rem;
  }
}

/* Desktop and up */
@media (min-width: 992px) {
  .component {
    max-width: 1140px;
    margin: 0 auto;
  }
}
```

### Blade Component Example
```blade
{{-- Responsive grid: 1 col mobile, 2 tablet, 3 desktop --}}
<div class="row g-4">
  <div class="col-12 col-md-6 col-lg-4">
    {{-- Column 1 --}}
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    {{-- Column 2 --}}
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    {{-- Column 3 --}}
  </div>
</div>
```

## Responsive Patterns

### 1. Fluid Typography
```css
/* Clamp for responsive font sizes */
h1 {
  font-size: clamp(1.5rem, 4vw, 3rem);
}

/* Bootstrap utilities */
<h1 class="fs-4 fs-md-3 fs-lg-2">Responsive Heading</h1>
<p class="fs-sm-6 fs-md-5 fs-lg-4">Responsive text</p>
```

### 2. Responsive Images
```blade
{{-- Bootstrap responsive image --}}
<img 
  src="{{ asset('images/image-800.jpg') }}"
  class="img-fluid"
  alt="Description"
>

{{-- Responsive image with srcset --}}
<img 
  src="{{ asset('images/image-800.jpg') }}"
  srcset="
    {{ asset('images/image-400.jpg') }} 400w,
    {{ asset('images/image-800.jpg') }} 800w,
    {{ asset('images/image-1200.jpg') }} 1200w
  "
  sizes="(max-width: 600px) 100vw, 50vw"
  alt="Description"
  class="img-fluid"
>
```

### 3. Responsive Navigation (Bootstrap Navbar)
```blade
<nav class="navbar navbar-expand-md navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Brand</a>
    
    {{-- Mobile toggle --}}
    <button 
      class="navbar-toggler" 
      type="button" 
      data-bs-toggle="collapse" 
      data-bs-target="#navbarNav"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    
    {{-- Desktop menu --}}
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">About</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
```

### 4. Responsive Cards
```blade
<div class="row g-4">
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card h-100">
      <img src="{{ asset('images/card.jpg') }}" class="card-img-top" alt="...">
      <div class="card-body">
        <h5 class="card-title">Card Title</h5>
        <p class="card-text">Card content here...</p>
        <a href="#" class="btn btn-primary">Action</a>
      </div>
    </div>
  </div>
</div>
```

### 5. Responsive Tables
```blade
<div class="table-responsive">
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Header 1</th>
        <th scope="col">Header 2</th>
      </tr>
    </thead>
    <tbody>
      {{-- Table rows --}}
    </tbody>
  </table>
</div>
```

### 6. Responsive Utilities
```blade
{{-- Display utilities --}}
<div class="d-none d-md-block">Hidden on mobile, visible on md+</div>
<div class="d-block d-md-none">Visible on mobile, hidden on md+</div>

{{-- Spacing utilities --}}
<div class="p-2 p-md-4 p-lg-5">Responsive padding</div>
<div class="m-2 m-md-3 m-lg-4">Responsive margin</div>

{{-- Text utilities --}}
<p class="text-center text-md-start text-lg-end">Responsive text align</p>
<p class="fs-6 fs-md-5 fs-lg-4">Responsive font size</p>

{{-- Visibility --}}
<div class="d-none d-lg-block">Visible only on large screens</div>
<div class="d-xl-none">Hidden on extra large screens</div>
```

## Bootstrap Container System

```blade
{{-- Fixed width containers --}}
<div class="container">        {{-- Responsive fixed width --}}
<div class="container-sm">    {{-- 100% until sm --}}
<div class="container-md">    {{-- 100% until md --}}
<div class="container-lg">    {{-- 100% until lg --}}
<div class="container-xl">    {{-- 100% until xl --}}
<div class="container-xxl">   {{-- 100% until xxl --}}

{{-- Full width --}}
<div class="container-fluid">  {{-- 100% width always --}}
```

## Testing Checklist

- [ ] Test on mobile (320px - 575px)
- [ ] Test on small devices (576px - 767px)
- [ ] Test on tablet (768px - 991px)
- [ ] Test on desktop (992px - 1199px)
- [ ] Test on large desktop (1200px+)
- [ ] Test landscape and portrait orientations
- [ ] Verify touch targets (min 44x44px)
- [ ] Check text readability (16px base minimum)
- [ ] Validate images scale properly (`img-fluid`)
- [ ] Test navbar collapse behavior
- [ ] Test table responsiveness

## Common Issues & Solutions

### Overflow Issues
```css
/* Bootstrap handles most overflow, but for custom elements */
img, video {
  max-width: 100%;
  height: auto;
}

/* Prevent horizontal scroll */
body {
  overflow-x: hidden;
}
```

### Custom Breakpoints
```scss
// If using SCSS, customize breakpoints
$grid-breakpoints: (
  xs: 0,
  sm: 576px,
  md: 768px,
  lg: 992px,
  xl: 1200px,
  xxl: 1400px
);
```

### Touch vs Hover
```css
/* Show dropdown on hover for desktop */
@media (min-width: 768px) and (hover: hover) {
  .dropdown:hover .dropdown-menu {
    display: block;
  }
}
```

## Bootstrap Icons

```blade
{{-- Include in layout --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

{{-- Usage --}}
<i class="bi bi-house-door"></i>
<i class="bi bi-person-circle"></i>
<i class="bi bi-search"></i>

{{-- Responsive icon sizes --}}
<i class="bi bi-house-door fs-5 fs-md-4 fs-lg-3"></i>
```

## Tools

- **Browser DevTools**: Device emulation
- **Responsively App**: Multi-device preview
- **Chrome Lighthouse**: Performance & accessibility
- **Bootstrap Playground**: Official prototyping tool
- **Bootstrap Icons**: https://icons.getbootstrap.com

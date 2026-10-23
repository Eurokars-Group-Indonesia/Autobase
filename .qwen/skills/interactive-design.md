# Interactive Design Skill (Bootstrap 5)

Provides interactive UI patterns, animations, and user experience guidelines using Bootstrap 5.

## Bootstrap Transition & Animation Standards

### Transition Classes
```css
/* Bootstrap transition utilities */
.transition { transition: all 0.3s ease-in-out; }

/* Fade transitions */
.fade { transition: opacity 0.15s linear; }
.fade.show { opacity: 1; }

/* Collapse transitions */
.collapse { transition: height 0.35s ease; }
.collapsing { position: relative; height: 0; overflow: hidden; }
```

### Custom Animation Timing
```css
/* Fast: 150ms - micro interactions */
.animation-fast { transition-duration: 150ms; }

/* Normal: 300ms - most UI elements */
.animation-normal { transition-duration: 300ms; }

/* Slow: 500ms - complex animations */
.animation-slow { transition-duration: 500ms; }
```

## Interactive Components

### 1. Button States
```blade
<button 
  class="btn btn-primary btn-transition"
  style="--bs-btn-transition: transform 0.2s ease, box-shadow 0.2s ease;"
>
  Click Me
</button>

<style>
.btn-transition {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.btn-transition:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.btn-transition:active {
  transform: translateY(0);
}
</style>
```

### 2. Card Hover Effects
```blade
<style>
.card-hover {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}
</style>

<div class="card card-hover">
  <div class="card-body">
    <h5 class="card-title">Card Title</h5>
    <p class="card-text">Hover over this card for animation.</p>
  </div>
</div>
```

### 3. Form Input Interactions
```blade
<div class="mb-3">
  <label for="searchInput" class="form-label">Search</label>
  <div class="input-group">
    <input 
      type="text"
      class="form-control form-control-transition"
      id="searchInput"
      placeholder="Enter text..."
    >
    <span class="input-group-text">
      <i class="bi bi-search"></i>
    </span>
  </div>
</div>

<style>
.form-control-transition {
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-control-transition:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
```

## Bootstrap JavaScript Components

### 1. Dropdown Menu
```blade
<div class="dropdown">
  <button 
    class="btn btn-secondary dropdown-toggle" 
    type="button" 
    data-bs-toggle="dropdown" 
    aria-expanded="false"
  >
    Menu
  </button>
  <ul class="dropdown-menu dropdown-menu-animate">
    <li><a class="dropdown-item" href="#">Action 1</a></li>
    <li><a class="dropdown-item" href="#">Action 2</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="#">Something else</a></li>
  </ul>
</div>

<style>
.dropdown-menu-animate {
  animation: dropdownSlide 0.2s ease-out;
}
@keyframes dropdownSlide {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
```

### 2. Modal Dialog
```blade
{{-- Trigger Button --}}
<button 
  type="button" 
  class="btn btn-primary" 
  data-bs-toggle="modal" 
  data-bs-target="#exampleModal"
>
  Open Modal
</button>

{{-- Modal --}}
<div 
  class="modal fade" 
  id="exampleModal" 
  tabindex="-1" 
  aria-labelledby="exampleModalLabel" 
  aria-hidden="true"
>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal Title</h5>
        <button 
          type="button" 
          class="btn-close" 
          data-bs-dismiss="modal" 
          aria-label="Close"
        ></button>
      </div>
      <div class="modal-body">
        {{-- Modal content --}}
        <p>Modal body content goes here...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
```

### 3. Accordion/Collapse
```blade
<div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button 
        class="accordion-button" 
        type="button" 
        data-bs-toggle="collapse" 
        data-bs-target="#collapseOne"
        aria-expanded="true" 
        aria-controls="collapseOne"
      >
        Accordion Item #1
      </button>
    </h2>
    <div 
      id="collapseOne" 
      class="accordion-collapse collapse show" 
      data-bs-parent="#accordionExample"
    >
      <div class="accordion-body">
        <strong>Content for Item 1</strong>
      </div>
    </div>
  </div>
  
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
      <button 
        class="accordion-button collapsed" 
        type="button" 
        data-bs-toggle="collapse" 
        data-bs-target="#collapseTwo"
      >
        Accordion Item #2
      </button>
    </h2>
    <div 
      id="collapseTwo" 
      class="accordion-collapse collapse" 
      data-bs-parent="#accordionExample"
    >
      <div class="accordion-body">
        <strong>Content for Item 2</strong>
      </div>
    </div>
  </div>
</div>
```

### 4. Toast Notifications
```blade
{{-- Toast Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div 
    id="liveToast" 
    class="toast" 
    role="alert" 
    aria-live="assertive" 
    aria-atomic="true"
  >
    <div class="toast-header">
      <i class="bi bi-bell me-2"></i>
      <strong class="me-auto">Notifications</strong>
      <small>Just now</small>
      <button 
        type="button" 
        class="btn-close" 
        data-bs-dismiss="toast"
      ></button>
    </div>
    <div class="toast-body">
      New message received!
    </div>
  </div>
</div>

{{-- Trigger --}}
<button 
  type="button" 
  class="btn btn-primary" 
  id="liveToastBtn"
>
  Show Toast
</button>

<script>
document.getElementById('liveToastBtn').addEventListener('click', function() {
  const toast = new bootstrap.Toast(document.getElementById('liveToast'));
  toast.show();
});
</script>
```

### 5. Alert with Dismiss
```blade
<div 
  class="alert alert-warning alert-dismissible fade show" 
  role="alert"
>
  <i class="bi bi-exclamation-triangle me-2"></i>
  <strong>Warning!</strong> Please review the information below.
  <button 
    type="button" 
    class="btn-close" 
    data-bs-dismiss="alert"
  ></button>
</div>
```

## Loading States

### 1. Spinner Loading
```blade
{{-- Button with spinner --}}
<button class="btn btn-primary" type="button" disabled>
  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
  <span class="visually-hidden">Loading...</span>
</button>

{{-- Standalone spinner --}}
<div class="d-flex justify-content-center">
  <div class="spinner-border text-primary" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

{{-- Grow spinner --}}
<div class="spinner-grow text-primary" role="status">
  <span class="visually-hidden">Loading...</span>
</div>
```

### 2. Progress Bar
```blade
<div class="progress" role="progressbar">
  <div 
    class="progress-bar progress-bar-striped progress-bar-animated" 
    style="width: 75%"
  ></div>
</div>

{{-- Dynamic progress with JavaScript --}}
<div class="progress mb-3">
  <div 
    id="progressBar" 
    class="progress-bar" 
    style="width: 0%"
  ></div>
</div>

<script>
// Update progress
const progressBar = document.getElementById('progressBar');
let progress = 0;
const interval = setInterval(() => {
  progress += 10;
  progressBar.style.width = progress + '%';
  if (progress >= 100) clearInterval(interval);
}, 500);
</script>
```

### 3. Skeleton Loader
```blade
<style>
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}
@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>

<div class="card">
  <div class="card-body">
    <div class="skeleton mb-2" style="height: 20px; width: 75%;"></div>
    <div class="skeleton mb-2" style="height: 15px; width: 100%;"></div>
    <div class="skeleton" style="height: 15px; width: 80%;"></div>
  </div>
</div>
```

## Scroll Interactions

### 1. Scroll-based Navigation (Sticky)
```blade
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Brand</a>
    {{-- Navigation items --}}
  </div>
</nav>

{{-- Or with custom scroll effect --}}
<style>
.navbar-scrolled {
  background-color: rgba(255, 255, 255, 0.95) !important;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}
</style>

<nav id="mainNav" class="navbar navbar-expand-lg navbar-light fixed-top">
  {{-- Navigation content --}}
</nav>

<script>
window.addEventListener('scroll', function() {
  const nav = document.getElementById('mainNav');
  if (window.scrollY > 50) {
    nav.classList.add('navbar-scrolled');
  } else {
    nav.classList.remove('navbar-scrolled');
  }
});
</script>
```

### 2. Scroll Spy
```blade
{{-- Navigation --}}
<nav class="navbar navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Scroll Spy</a>
    <nav class="nav nav-pills">
      <a class="nav-link" href="#section1">Section 1</a>
      <a class="nav-link" href="#section2">Section 2</a>
      <a class="nav-link" href="#section3">Section 3</a>
    </nav>
  </div>
</nav>

{{-- Content --}}
<div data-bs-spy="scroll" data-bs-target=".nav" data-bs-offset="0">
  <section id="section1">
    <h2>Section 1</h2>
    <p>Content...</p>
  </section>
  <section id="section2">
    <h2>Section 2</h2>
    <p>Content...</p>
  </section>
  <section id="section3">
    <h2>Section 3</h2>
    <p>Content...</p>
  </section>
</div>
```

### 3. Fade In on Scroll
```blade
<style>
.fade-in-section {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.fade-in-section.is-visible {
  opacity: 1;
  transform: translateY(0);
}
</style>

<div class="fade-in-section">
  <h2>Section Title</h2>
  <p>Content that fades in on scroll</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
      }
    });
  }, { threshold: 0.1 });
  
  document.querySelectorAll('.fade-in-section').forEach(section => {
    observer.observe(section);
  });
});
</script>
```

## Tooltips & Popovers

### Tooltips
```blade
<button 
  type="button" 
  class="btn btn-secondary" 
  data-bs-toggle="tooltip" 
  data-bs-placement="top" 
  title="Tooltip on top"
>
  Hover me
</button>

<script>
// Initialize tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(
  trigger => new bootstrap.Tooltip(trigger)
);
</script>
```

### Popovers
```blade
<button 
  type="button" 
  class="btn btn-lg btn-danger" 
  data-bs-toggle="popover" 
  title="Popover Title" 
  data-bs-content="And here's some amazing content. It's very engaging."
>
  Click to toggle popover
</button>

<script>
// Initialize popovers
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(
  trigger => new bootstrap.Popover(trigger)
);
</script>
```

## Accessibility

### Focus Management
```css
/* Bootstrap focus styles */
.btn:focus, .form-control:focus {
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Skip link for keyboard users */
.visually-hidden-focusable {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
.visually-hidden-focusable:focus {
  position: static;
  width: auto;
  height: auto;
  padding: inherit;
  margin: inherit;
  overflow: visible;
  clip: auto;
  white-space: normal;
}
```

### Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

### ARIA Labels
```blade
<button 
  type="button" 
  class="btn-close" 
  data-bs-dismiss="modal"
  aria-label="Close"
></button>

<nav aria-label="Breadcrumb">
  {{-- Breadcrumb content --}}
</nav>

<div role="alert" class="alert alert-danger">
  Error message here
</div>
```

## Performance Tips

1. **Use CSS transitions** over JavaScript animations when possible
2. **Limit concurrent animations** (max 2-3 at once)
3. **Use `will-change`** sparingly for complex animations
4. **Debounce scroll events** to prevent jank
5. **Prefers-reduced-motion** for accessibility
6. **Use Bootstrap's built-in transitions** (optimized)
7. **Lazy load** heavy components (modals, carousels)

## Bootstrap Carousel

```blade
<div 
  id="carouselExample" 
  class="carousel slide" 
  data-bs-ride="carousel"
>
  <div class="carousel-indicators">
    <button 
      type="button" 
      data-bs-target="#carouselExample" 
      data-bs-slide-to="0" 
      class="active"
    ></button>
    <button 
      type="button" 
      data-bs-target="#carouselExample" 
      data-bs-slide-to="1"
    ></button>
  </div>
  
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="{{ asset('images/slide1.jpg') }}" class="d-block w-100" alt="...">
      <div class="carousel-caption d-none d-md-block">
        <h5>Slide 1</h5>
        <p>Description</p>
      </div>
    </div>
    <div class="carousel-item">
      <img src="{{ asset('images/slide2.jpg') }}" class="d-block w-100" alt="...">
    </div>
  </div>
  
  <button 
    class="carousel-control-prev" 
    type="button" 
    data-bs-target="#carouselExample" 
    data-bs-slide="prev"
  >
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button 
    class="carousel-control-next" 
    type="button" 
    data-bs-target="#carouselExample" 
    data-bs-slide="next"
  >
    <span class="carousel-control-next-icon"></span>
  </button>
</div>
```

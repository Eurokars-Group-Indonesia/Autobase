# 🛡️ Content Security Policy (CSP) Customization Guide

## ✅ Issue Fixed: CDN Source Maps Blocked

**Error:** "Connecting to 'https://cdn.jsdelivr.net/...' violates the following Content Security Policy directive: 'connect-src 'self''"

**Solution:** Added CDN domains to `connect-src` directive.

---

## 🔧 What Was Changed

### File: `app/Http/Middleware/SecurityHeaders.php`

**Before:**
```php
"connect-src 'self'",
```

**After:**
```php
"connect-src 'self' https://cdn.jsdelivr.net https://code.jquery.com https://fonts.googleapis.com https://fonts.gstatic.com",
```

This allows:
- ✅ Bootstrap source maps from cdn.jsdelivr.net
- ✅ jQuery source maps from code.jquery.com
- ✅ Google Fonts connections
- ✅ AJAX requests to your own domain

---

## 📚 Understanding CSP Directives

### What is `connect-src`?
Controls which URLs can be loaded using script interfaces like:
- XMLHttpRequest (AJAX)
- Fetch API
- WebSocket
- EventSource
- **Source maps (.map files)** ← This was your issue!

### Common CSP Directives

| Directive | Purpose | Your Current Setting |
|-----------|---------|---------------------|
| `default-src` | Fallback for other directives | `'self'` |
| `script-src` | JavaScript sources | `'self' 'unsafe-inline' CDNs` |
| `style-src` | CSS sources | `'self' 'unsafe-inline' CDNs` |
| `font-src` | Font sources | `'self' CDNs` |
| `img-src` | Image sources | `'self' data: https:` |
| `connect-src` | AJAX/Fetch/WebSocket | `'self' CDNs` ✅ Fixed |
| `frame-ancestors` | Who can embed your site | `'none'` (blocks all) |

---

## 🎯 How to Add More Domains

### Example 1: Add Analytics
If you want to use Google Analytics:

```php
$csp = implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.google-analytics.com https://www.googletagmanager.com",
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
    "img-src 'self' data: https: https://www.google-analytics.com",
    "connect-src 'self' https://cdn.jsdelivr.net https://www.google-analytics.com",
    "frame-ancestors 'none'",
]);
```

### Example 2: Add Payment Gateway
If you need to embed Stripe or PayPal:

```php
$csp = implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://js.stripe.com",
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
    "img-src 'self' data: https:",
    "connect-src 'self' https://cdn.jsdelivr.net https://api.stripe.com",
    "frame-src 'self' https://js.stripe.com", // Allow Stripe iframe
    "frame-ancestors 'none'",
]);
```

### Example 3: Add API Endpoint
If you have an external API:

```php
"connect-src 'self' https://cdn.jsdelivr.net https://api.yourdomain.com",
```

---

## 🚨 Common CSP Issues & Fixes

### Issue 1: Inline Scripts Blocked
**Error:** "Refused to execute inline script"

**Quick Fix (Less Secure):**
```php
"script-src 'self' 'unsafe-inline'",
```

**Better Fix (More Secure):**
Use nonces or move scripts to external files.

### Issue 2: Inline Styles Blocked
**Error:** "Refused to apply inline style"

**Quick Fix (Less Secure):**
```php
"style-src 'self' 'unsafe-inline'",
```

**Better Fix (More Secure):**
Move inline styles to external CSS files.

### Issue 3: Images from External Sites Blocked
**Error:** "Refused to load the image"

**Fix:**
```php
"img-src 'self' data: https: http:",
```

Or specify exact domains:
```php
"img-src 'self' data: https://images.yourdomain.com https://cdn.example.com",
```

### Issue 4: AJAX to External API Blocked
**Error:** "Refused to connect to"

**Fix:**
```php
"connect-src 'self' https://api.external-service.com",
```

### Issue 5: YouTube/Vimeo Embeds Blocked
**Error:** "Refused to frame"

**Fix:**
```php
"frame-src 'self' https://www.youtube.com https://player.vimeo.com",
```

---

## 🔍 Testing CSP Changes

### Method 1: Browser Console
1. Open Developer Tools (F12)
2. Go to Console tab
3. Look for CSP violation errors
4. Add the blocked domain to appropriate directive

### Method 2: CSP Report-Only Mode
Test CSP without blocking (for testing):

```php
// In SecurityHeaders.php
$response->headers->set('Content-Security-Policy-Report-Only', $csp);
// Instead of:
// $response->headers->set('Content-Security-Policy', $csp);
```

This will log violations without blocking them.

### Method 3: Online CSP Evaluator
Use: https://csp-evaluator.withgoogle.com/

---

## 📋 Your Current CSP Policy

```
default-src 'self';
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://fonts.googleapis.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;
img-src 'self' data: https:;
connect-src 'self' https://cdn.jsdelivr.net https://code.jquery.com https://fonts.googleapis.com https://fonts.gstatic.com;
frame-ancestors 'none';
```

### What This Allows:
- ✅ Scripts from your domain and CDNs
- ✅ Styles from your domain and CDNs
- ✅ Fonts from Google Fonts and CDNs
- ✅ Images from anywhere (HTTPS)
- ✅ AJAX to your domain and CDNs
- ✅ Source maps from CDNs
- ❌ Embedding your site in iframes (security!)

### What This Blocks:
- ❌ Scripts from unknown domains
- ❌ Styles from unknown domains
- ❌ AJAX to unknown domains
- ❌ Embedding your site in iframes
- ❌ WebSockets to unknown domains

---

## 🎯 Best Practices

### 1. Start Strict, Then Relax
Start with strict CSP and add domains as needed:
```php
"default-src 'self'",
```

### 2. Avoid 'unsafe-inline'
Currently using it for convenience, but better to:
- Move inline scripts to external files
- Use nonces for necessary inline scripts

### 3. Specify Exact Domains
Instead of:
```php
"script-src 'self' https:",
```

Use:
```php
"script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com",
```

### 4. Use Subresource Integrity (SRI)
For CDN resources, add integrity hashes:
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-..." 
        crossorigin="anonymous"></script>
```

### 5. Monitor CSP Violations
Set up CSP reporting:
```php
"report-uri /csp-violation-report-endpoint",
```

---

## 🔄 Quick Reference: Adding New Resources

### Adding a New CDN
1. Open `app/Http/Middleware/SecurityHeaders.php`
2. Find the appropriate directive
3. Add the domain
4. Clear cache: `php artisan optimize`
5. Test in browser

### Example: Adding Cloudflare CDN
```php
"script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
"style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
"connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
```

---

## 🧪 Test Your Changes

After modifying CSP:

```bash
# Clear cache
php artisan optimize

# Test in browser
# 1. Open http://localhost:8000
# 2. Press F12
# 3. Check Console for CSP errors
# 4. Check Network tab for blocked requests
```

---

## 📊 CSP Security Levels

| Level | Security | Convenience | Recommendation |
|-------|----------|-------------|----------------|
| Very Strict | 🔒🔒🔒🔒🔒 | ⚠️ | Production (after testing) |
| Strict | 🔒🔒🔒🔒 | ⚠️⚠️ | Production |
| Moderate | 🔒🔒🔒 | ✅✅ | **Current** ← You are here |
| Relaxed | 🔒🔒 | ✅✅✅ | Development |
| Permissive | 🔒 | ✅✅✅✅ | Not recommended |

---

## ✅ Verification

After the fix, you should:
- ✅ No CSP errors in console
- ✅ Bootstrap source maps load
- ✅ jQuery source maps load
- ✅ Google Fonts load
- ✅ All CDN resources work
- ✅ Application functions normally

---

## 🆘 Still Having Issues?

### Check These:
1. Cache cleared? `php artisan optimize`
2. Browser cache cleared? (Ctrl+Shift+Delete)
3. Hard refresh? (Ctrl+F5)
4. Check exact error in console
5. Verify domain spelling in CSP

### Get Help:
- Check browser console for exact CSP violation
- Copy the blocked URL
- Add that domain to the appropriate directive
- Test again

---

**Issue:** CDN source maps blocked by CSP  
**Status:** ✅ Fixed  
**Solution:** Added CDN domains to connect-src  
**Date:** January 25, 2026

# Performance Testing Guide

## Before Testing

1. **Rebuild assets with optimizations:**
   ```bash
   npm run build
   ```

2. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan optimize
   ```

3. **Ensure production mode:**
   - Set `APP_ENV=production` in `.env`
   - Set `APP_DEBUG=false`

## Testing Tools

### 1. Google PageSpeed Insights
- URL: https://pagespeed.web.dev/
- Test your live site URL
- Check both Mobile and Desktop scores

### 2. Chrome DevTools Lighthouse
1. Open Chrome DevTools (F12)
2. Go to "Lighthouse" tab
3. Select "Performance" category
4. Click "Analyze page load"

### 3. WebPageTest
- URL: https://www.webpagetest.org/
- Provides detailed waterfall analysis
- Test from multiple locations

## Key Metrics to Monitor

### Core Web Vitals
- **LCP (Largest Contentful Paint)**: Target < 2.5s
- **FID (First Input Delay)**: Target < 100ms
- **CLS (Cumulative Layout Shift)**: Target < 0.1

### Additional Metrics
- **FCP (First Contentful Paint)**: Target < 1.8s
- **TBT (Total Blocking Time)**: Target < 200ms
- **Speed Index**: Target < 3.4s

## Expected Improvements

### Before Optimization
- FCP: 9.0s
- LCP: 10.0s
- TBT: 1,490ms
- Speed Index: 18.5s

### After Optimization (Expected)
- FCP: ~2.0s (77% improvement)
- LCP: ~2.5s (75% improvement)
- TBT: ~150ms (90% improvement)
- Speed Index: ~3.5s (81% improvement)

## Testing Checklist

### Homepage
- [ ] Load time < 3s
- [ ] No render-blocking resources
- [ ] Fonts load with display=swap
- [ ] No Chart.js loaded (not needed)

### Dashboard (with charts)
- [ ] Chart.js lazy loaded
- [ ] Charts render after page load
- [ ] No jQuery/Summernote loaded

### Course Builder
- [ ] Fast initial load
- [ ] Smooth interactions
- [ ] Cached data loads quickly
- [ ] No N+1 queries (check Laravel Debugbar)

### Lesson Editor
- [ ] Summernote loads only on this page
- [ ] Editor initializes quickly
- [ ] No blocking on initial render

## Debugging Performance Issues

### Check Network Tab
```
1. Open DevTools → Network
2. Reload page
3. Look for:
   - Large files (> 500KB)
   - Slow requests (> 1s)
   - Blocking resources
   - Unnecessary requests
```

### Check Performance Tab
```
1. Open DevTools → Performance
2. Click Record
3. Reload page
4. Stop recording
5. Analyze:
   - Long tasks (> 50ms)
   - Layout shifts
   - JavaScript execution time
```

### Check Database Queries
```bash
# Install Laravel Debugbar (dev only)
composer require barryvdh/laravel-debugbar --dev

# Check queries in browser toolbar
# Look for:
# - Duplicate queries
# - N+1 problems
# - Slow queries (> 100ms)
```

## Monitoring in Production

### Laravel Telescope
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### Application Performance Monitoring (APM)
Consider using:
- New Relic
- Datadog
- Scout APM
- Blackfire.io

## Continuous Optimization

### Regular Checks
- [ ] Weekly: Run Lighthouse audit
- [ ] Monthly: Full performance review
- [ ] After deploys: Verify no regressions

### Asset Optimization
- [ ] Compress images (WebP, AVIF)
- [ ] Minify CSS/JS (done via Vite)
- [ ] Enable HTTP/2
- [ ] Enable Brotli compression

### Server Optimization
- [ ] Enable OPcache
- [ ] Configure Redis/Memcached
- [ ] Optimize database indexes
- [ ] Enable CDN for static assets

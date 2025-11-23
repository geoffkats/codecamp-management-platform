# Performance Optimization Plan

## Current Performance Issues

### Metrics (Target → Current)
- **First Contentful Paint**: 1.8s → 9.0s ❌
- **Largest Contentful Paint**: 2.5s → 10.0s ❌
- **Total Blocking Time**: 200ms → 1,490ms ❌
- **Speed Index**: 3.4s → 18.5s ❌

### Root Causes

#### 1. Render-Blocking Resources (Critical)
- jQuery (87KB) loaded synchronously in `<head>`
- Summernote CSS + JS (150KB+) loaded on every page
- External font from fonts.bunny.net blocking render
- No async/defer on scripts

#### 2. Bundle Size Issues
- CSS: 308KB (Tailwind + Flux + custom styles)
- JS: 242KB (Chart.js + axios + Livewire)
- Chart.js loaded globally but only used on dashboard
- No code splitting or lazy loading

#### 3. Database Performance
- N+1 queries in Builder component
- Missing eager loading for nested relationships
- No query result caching

#### 4. Missing Optimizations
- No asset compression (gzip/brotli)
- No resource hints (preload, prefetch, preconnect)
- No image optimization
- No service worker/caching strategy

## Optimization Strategy

### Phase 1: Quick Wins (Immediate Impact)

#### A. Defer Non-Critical Scripts
```html
<!-- Move to bottom of body or defer -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js" defer></script>
```

#### B. Optimize Font Loading
```html
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="dns-prefetch" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet">
```

#### C. Conditional Loading (Summernote)
Only load Summernote on pages that need rich text editing:
```php
@stack('editor-scripts')
```

#### D. Lazy Load Chart.js
```javascript
// Only import when needed
if (document.querySelector('[data-chart]')) {
    import('chart.js/auto').then(module => {
        window.Chart = module.default;
    });
}
```

### Phase 2: Database Optimization

#### Fix N+1 Queries in Builder Component
```php
// In loadCourse() method
$this->course = $query
    ->with([
        'modules' => function($q) {
            $q->orderBy('order_index');
        },
        'modules.lessons' => function($q) {
            $q->orderBy('order_index');
        },
        'modules.lessons.assessments'
    ])
    ->firstOrFail();
```

#### Add Query Caching
```php
$this->course = Cache::remember(
    "course.{$this->courseId}.builder",
    now()->addMinutes(5),
    fn() => $query->with([...])->firstOrFail()
);
```

### Phase 3: Build Optimization

#### A. Vite Configuration
```javascript
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'chart': ['chart.js'],
                    'vendor': ['axios']
                }
            }
        },
        cssCodeSplit: true,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true
            }
        }
    }
});
```

#### B. CSS Optimization
- Enable PurgeCSS for Tailwind
- Remove unused Flux components
- Split critical CSS

### Phase 4: Advanced Optimizations

#### A. Resource Hints
```html
<link rel="preconnect" href="https://code.jquery.com">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="dns-prefetch" href="https://fonts.bunny.net">
```

#### B. Image Optimization
- Implement lazy loading
- Use WebP format
- Add responsive images

#### C. Caching Strategy
- Add service worker
- Implement HTTP caching headers
- Use Laravel response caching

## Implementation Priority

### High Priority (Do First)
1. ✅ Defer jQuery and Summernote
2. ✅ Optimize font loading
3. ✅ Fix N+1 queries in Builder
4. ✅ Lazy load Chart.js
5. ✅ Add resource hints

### Medium Priority
6. ⏳ Implement code splitting
7. ⏳ Add query caching
8. ⏳ Optimize CSS bundle

### Low Priority
9. ⏳ Service worker
10. ⏳ Image optimization
11. ⏳ Advanced caching

## Expected Results

After implementing high-priority fixes:
- **FCP**: 9.0s → ~2.0s (77% improvement)
- **LCP**: 10.0s → ~2.5s (75% improvement)
- **TBT**: 1,490ms → ~150ms (90% improvement)
- **Speed Index**: 18.5s → ~3.5s (81% improvement)

## Next Steps

1. Review and approve this plan
2. Implement Phase 1 optimizations
3. Test and measure improvements
4. Proceed to Phase 2 based on results

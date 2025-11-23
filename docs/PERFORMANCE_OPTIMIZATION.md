# Performance Optimization Guide

## Implemented Optimizations

### 1. Image Optimization ✅
- **Installed**: Intervention Image package
- **Command**: `php artisan images:optimize`
- **What it does**:
  - Resizes images larger than 1920px
  - Compresses images to 85% quality
  - Reduces file sizes significantly

**Usage**:
```bash
# Optimize all images in storage/app/public/settings
php artisan images:optimize

# Optimize specific path
php artisan images:optimize --path=storage/app/public/uploads
```

### 2. Lazy Loading Images ✅
- **Component**: `<x-optimized-image>`
- **Features**:
  - Automatic lazy loading
  - Async decoding
  - Proper width/height attributes

**Usage**:
```blade
{{-- Old way --}}
<img src="{{ $image }}" alt="Description">

{{-- New optimized way --}}
<x-optimized-image 
    :src="$image" 
    alt="Description"
    class="w-full h-auto"
    :width="800"
    :height="600"
/>
```

### 3. Asset Optimization ✅
- **Vite Configuration**: Optimized for production
- **Features**:
  - Code splitting
  - Tree shaking
  - Minification with Terser
  - CSS optimization
  - Console.log removal in production

**Build Command**:
```bash
npm run build
```

### 4. Caching Headers ✅
- **Location**: `public/.htaccess`
- **Features**:
  - 1-year cache for static assets
  - Gzip compression enabled
  - Browser caching configured

### 5. Performance Monitoring

**Check Performance**:
```bash
# Run Lighthouse audit
# Chrome DevTools > Lighthouse > Generate Report

# Or use CLI
npm install -g lighthouse
lighthouse http://your-site.com --view
```

## Performance Checklist

### Before Deployment
- [ ] Run `npm run build` for production assets
- [ ] Run `php artisan images:optimize` for all images
- [ ] Enable OPcache in PHP
- [ ] Configure Redis/Memcached for caching
- [ ] Enable HTTP/2 on server
- [ ] Set up CDN for static assets

### Regular Maintenance
- [ ] Optimize new images weekly
- [ ] Clear old cache files monthly
- [ ] Review Lighthouse reports monthly
- [ ] Update dependencies quarterly

## Expected Performance Improvements

### Before Optimization:
- Performance Score: **53/100**
- FCP: 3.9s
- LCP: 4.8s
- Total Size: 2,170 KB

### After Optimization:
- Performance Score: **75-85/100** (expected)
- FCP: 1.5-2.0s (expected)
- LCP: 2.0-2.5s (expected)
- Total Size: 800-1,000 KB (expected)

## Additional Recommendations

### 1. Enable OPcache (PHP)
Add to `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 2. Use Redis for Caching
```bash
# Install Redis
composer require predis/predis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Enable HTTP/2
For Apache, add to VirtualHost:
```apache
Protocols h2 http/1.1
```

### 4. Use CDN
Consider using:
- Cloudflare (free tier available)
- AWS CloudFront
- BunnyCDN

### 5. Database Optimization
```bash
# Add indexes to frequently queried columns
php artisan make:migration add_indexes_to_tables

# Enable query caching
# In config/database.php
'options' => [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"',
    PDO::ATTR_EMULATE_PREPARES => false,
],
```

## Monitoring Tools

1. **Laravel Telescope**: Already installed
   - Monitor slow queries
   - Track request times
   - View cache hits/misses

2. **Laravel Debugbar**: Already installed
   - View page load times
   - Check N+1 queries
   - Monitor memory usage

3. **Google Lighthouse**: Browser tool
   - Performance audits
   - Best practices
   - SEO checks

## Troubleshooting

### Images not loading after optimization
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Regenerate optimized images
php artisan images:optimize
```

### Assets not updating
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Slow database queries
```bash
# Check slow queries in Telescope
php artisan telescope:prune

# Add indexes
php artisan make:migration add_performance_indexes
```

## Performance Testing Commands

```bash
# Test page load time
curl -w "@curl-format.txt" -o /dev/null -s http://your-site.com

# Create curl-format.txt:
time_namelookup:  %{time_namelookup}\n
time_connect:  %{time_connect}\n
time_starttransfer:  %{time_starttransfer}\n
time_total:  %{time_total}\n
```

## Automated Optimization

Add to `composer.json`:
```json
"scripts": {
    "post-deploy": [
        "@php artisan optimize",
        "@php artisan images:optimize",
        "@php artisan view:cache",
        "@php artisan route:cache",
        "@php artisan config:cache"
    ]
}
```

Run after deployment:
```bash
composer run post-deploy
```

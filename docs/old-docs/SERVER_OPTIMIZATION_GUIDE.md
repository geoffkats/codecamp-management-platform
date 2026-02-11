# Server Optimization Guide

## Critical Issue: Server Response Time (7.5 seconds!)

Your server is taking 7.5 seconds to respond, which is the #1 performance killer.

### Immediate Actions Required

#### 1. Enable OPcache (PHP)

Check if OPcache is enabled:
```bash
php -i | findstr opcache
```

If not enabled, add to `php.ini`:
```ini
[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.validate_timestamps=1
```

Restart Apache/PHP-FPM after changes.

#### 2. Enable Compression

The `.htaccess` file has been updated with gzip compression.

**Verify it's working:**
```bash
curl -H "Accept-Encoding: gzip" -I http://127.0.0.1/dashboard
```

Look for: `Content-Encoding: gzip`

If not working:
1. Enable `mod_deflate` in Apache
2. Restart Apache

#### 3. Database Optimization

**Check slow queries:**
```sql
-- In MySQL
SHOW VARIABLES LIKE 'slow_query_log';
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

**Add indexes** (migration already created):
```bash
php artisan migrate
```

#### 4. Enable Query Caching

Already implemented in Builder component. Verify Redis/Memcached is available:

```bash
# Check if Redis is running
redis-cli ping
# Should return: PONG
```

If not installed:
```bash
# Install Redis (Windows)
# Download from: https://github.com/microsoftarchive/redis/releases
# Or use Memcached
```

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### 5. Laravel Optimizations

Run these commands:
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Clear and optimize
php artisan optimize
```

### Performance Checklist

- [ ] OPcache enabled and configured
- [ ] Gzip compression working
- [ ] Database indexes added
- [ ] Redis/Memcached configured
- [ ] Laravel caches generated
- [ ] Autoloader optimized

### Expected Improvements

| Optimization | Time Saved |
|--------------|------------|
| OPcache | 2-3 seconds |
| Compression | 500-1000ms |
| Database indexes | 1-2 seconds |
| Query caching | 500-1000ms |
| Laravel caches | 500ms |

**Total Expected**: Server response from 7.5s → **1-2s**

### Monitoring

Create a test endpoint to check performance:

```php
// routes/web.php
Route::get('/performance-check', function() {
    $start = microtime(true);
    
    // Test database
    $dbStart = microtime(true);
    \App\Models\User::count();
    $dbTime = (microtime(true) - $dbStart) * 1000;
    
    // Test cache
    $cacheStart = microtime(true);
    cache()->remember('test', 60, fn() => 'test');
    $cacheTime = (microtime(true) - $cacheStart) * 1000;
    
    $total = (microtime(true) - $start) * 1000;
    
    return response()->json([
        'total_ms' => round($total, 2),
        'database_ms' => round($dbTime, 2),
        'cache_ms' => round($cacheTime, 2),
        'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status(),
        'cache_driver' => config('cache.default'),
    ]);
});
```

Visit: `http://127.0.0.1/performance-check`

### Troubleshooting

**If server is still slow:**

1. **Check WAMP configuration**
   - Increase PHP memory limit
   - Increase max_execution_time
   - Check Apache logs for errors

2. **Profile with Xdebug**
   ```bash
   composer require --dev barryvdh/laravel-debugbar
   ```

3. **Check disk I/O**
   - Move to SSD if on HDD
   - Check antivirus isn't scanning files

4. **Database queries**
   ```bash
   composer require --dev barryvdh/laravel-debugbar
   ```
   Check for N+1 queries in the toolbar

### Production Recommendations

1. **Use PHP 8.2+** (faster JIT compiler)
2. **Use Redis** for cache/sessions
3. **Enable HTTP/2** in Apache
4. **Use CDN** for static assets
5. **Consider Laravel Octane** for even better performance

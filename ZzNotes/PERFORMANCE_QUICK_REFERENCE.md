# Performance Optimization Quick Reference

**Version:** Phase 6  
**Last Updated:** October 2025

---

## Database Optimization

### Run Performance Indexes Migration

```bash
# Apply indexes
docker-compose exec app php artisan migrate

# Verify indexes
docker-compose exec app php artisan migrate:status

# Rollback if needed
docker-compose exec app php artisan migrate:rollback --step=1
```

### Check Query Performance

```php
// In controller or tinker
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

// Run your queries...
$reservations = Reservation::with('user')->get();

// Get logged queries
$queries = DB::getQueryLog();
foreach ($queries as $query) {
    echo "Time: {$query['time']}ms | SQL: {$query['sql']}\n";
}
```

### Analyze Slow Queries

```php
// Add to AppServiceProvider boot method
DB::listen(function ($query) {
    if ($query->time > 100) { // Log queries over 100ms
        \Log::warning('Slow Query', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time
        ]);
    }
});
```

---

## Caching Service

### Basic Usage

```php
use App\Services\PerformanceOptimizationService;

// In controller constructor
public function __construct(
    protected PerformanceOptimizationService $perfService
) {}

// Get cached data
$services = $this->perfService->getServices();
$venues = $this->perfService->getVenues();
$priests = $this->perfService->getAvailablePriests();
```

### Cache Invalidation

```php
// After creating/updating reservation
$this->perfService->invalidateReservationCaches();

// After user update
$this->perfService->invalidateUserCache($userId);

// Clear all caches
$this->perfService->clearAllCaches();
```

### Custom Caching

```php
// Cache custom data
$data = $this->perfService->remember(
    'custom.cache.key',
    fn() => expensiveOperation(),
    3600 // 1 hour
);
```

### Cache Statistics

```php
// Get cache stats
$stats = $this->perfService->getCacheStats();
dd($stats);

// Warm up caches
$this->perfService->warmupCaches();
```

---

## Image Lazy Loading

### HTML Implementation

```html
<!-- Basic lazy loading -->
<img
    class="lazy"
    data-src="/storage/photos/image.jpg"
    alt="Description"
    style="min-height: 200px; background: #f3f4f6;"
/>

<!-- With responsive images -->
<img
    class="lazy"
    data-src="/storage/photos/image-600.jpg"
    data-srcset="
        /storage/photos/image-300.jpg 300w,
        /storage/photos/image-600.jpg 600w,
        /storage/photos/image-1200.jpg 1200w
    "
    sizes="(max-width: 640px) 300px, (max-width: 1024px) 600px, 1200px"
    alt="Responsive Image"
/>

<!-- Disable lazy loading -->
<img src="/images/logo.png" alt="Logo" />
```

---

## JavaScript Utilities

### Debounce (for search inputs)

```javascript
// Search with debounce
const searchInput = document.getElementById("search");

searchInput.addEventListener(
    "input",
    debounce((e) => {
        const query = e.target.value;
        fetch(`/api/search?q=${query}`)
            .then((res) => res.json())
            .then((data) => updateResults(data));
    }, 300)
); // Wait 300ms after typing stops
```

### Throttle (for scroll/resize)

```javascript
// Scroll handler with throttle
window.addEventListener(
    "scroll",
    throttle(() => {
        const scrollTop = window.pageYOffset;
        updateScrollIndicator(scrollTop);
    }, 100)
); // Execute at most every 100ms
```

### Link Prefetching

```html
<!-- Automatically prefetches on hover -->
<a href="/reservations/show/123">View Reservation</a>

<!-- Disable prefetch -->
<a href="/external-link" data-no-prefetch>External Link</a>
```

### Performance Monitoring

```javascript
// Monitoring is automatic
// Check console in development for metrics:
// - Page Load Time
// - Connection Time
// - Render Time
```

---

## Build Commands

### Development

```bash
# Watch mode (auto-rebuild)
docker-compose exec app npm run dev

# Build for development
docker-compose exec app npm run build
```

### Production

```bash
# Clean install dependencies
docker-compose exec app npm ci

# Build optimized assets
docker-compose exec app npm run build

# Verify build output
ls -lh public/build/assets/
```

---

## Cache Management

### Laravel Cache Commands

```bash
# Clear all caches
docker-compose exec app php artisan cache:clear

# Cache configuration
docker-compose exec app php artisan config:cache

# Cache routes
docker-compose exec app php artisan route:cache

# Cache views
docker-compose exec app php artisan view:cache

# Clear all optimizations
docker-compose exec app php artisan optimize:clear
```

### Application Cache

```bash
# In tinker or controller
use App\Services\PerformanceOptimizationService;

$perf = app(PerformanceOptimizationService::class);

// Warm up caches
$perf->warmupCaches();

// Clear all app caches
$perf->clearAllCaches();

// Get cache statistics
$stats = $perf->getCacheStats();
```

---

## Performance Headers

### Headers Added Automatically

**Static Assets (CSS, JS, images):**

-   `Cache-Control: public, max-age=31536000, immutable`

**HTML Pages:**

-   `Cache-Control: public, max-age=300, must-revalidate`

**API Routes:**

-   `Cache-Control: no-cache, no-store, must-revalidate`

**Security:**

-   `X-Content-Type-Options: nosniff`
-   `X-Frame-Options: SAMEORIGIN`
-   `X-XSS-Protection: 1; mode=block`

### No Configuration Required

Headers are set automatically by `SetPerformanceHeaders` middleware.

---

## Performance Testing

### Lighthouse Audit

```bash
# Using Chrome DevTools
1. Open DevTools (F12)
2. Click "Lighthouse" tab
3. Select categories:
   - Performance
   - Accessibility
   - Best Practices
   - SEO
4. Click "Analyze page load"

# Target Scores
Performance: 85+
Accessibility: 95+
Best Practices: 90+
SEO: 92+
```

### Network Analysis

```bash
# Using Chrome DevTools
1. Open DevTools (F12)
2. Click "Network" tab
3. Reload page (Ctrl+R)
4. Check:
   - Total requests
   - Total size
   - Load time
   - Cached resources

# Expected Results
Total requests: < 50
Total size: < 500 KB (first load)
Load time: < 2 seconds
Cache hits: 80%+ (subsequent loads)
```

### Database Performance

```php
// Check query count
DB::enableQueryLog();
// ... perform operations
$queryCount = count(DB::getQueryLog());
echo "Total queries: {$queryCount}\n";

// Target: < 20 queries per page load
```

---

## Troubleshooting

### Assets Not Loading

```bash
# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Rebuild assets
docker-compose exec app npm run build

# Check file permissions
docker-compose exec app chmod -R 755 public/build
```

### Slow Queries

```php
// Enable query logging
DB::enableQueryLog();

// Check logged queries
$queries = DB::getQueryLog();
foreach ($queries as $q) {
    if ($q['time'] > 100) {
        dump($q);
    }
}

// Solutions:
// 1. Add missing indexes
// 2. Use eager loading
// 3. Optimize query logic
```

### Cache Issues

```bash
# Clear Redis cache (if using Redis)
docker-compose exec redis redis-cli FLUSHDB

# Clear Laravel cache
docker-compose exec app php artisan cache:clear

# Verify cache driver
docker-compose exec app php artisan tinker
>>> config('cache.default')
```

### High Memory Usage

```bash
# Check cache size (Redis)
docker-compose exec redis redis-cli INFO memory

# Clear old cache entries
docker-compose exec app php artisan cache:clear

# Optimize database
docker-compose exec app php artisan tinker
>>> DB::statement('OPTIMIZE TABLE reservations, users, notifications');
```

---

## Best Practices

### Query Optimization

```php
// ❌ Bad: N+1 problem
$reservations = Reservation::all();
foreach ($reservations as $r) {
    echo $r->user->name; // Queries for each user
}

// ✅ Good: Eager loading
$reservations = Reservation::with('user')->get();
foreach ($reservations as $r) {
    echo $r->user->name; // Already loaded
}

// ✅ Better: Select only needed columns
$reservations = Reservation::with('user:id,first_name,last_name')
    ->select('reservation_id', 'user_id', 'status')
    ->get();
```

### Caching Strategy

```php
// ❌ Bad: Cache everything forever
Cache::forever('data', $expensiveData);

// ✅ Good: Cache with appropriate TTL
Cache::remember('data', 3600, fn() => $expensiveData);

// ✅ Better: Use caching service
$data = $perfService->remember('data', fn() => $expensiveData, 3600);

// ❌ Bad: No cache invalidation
// Update data but don't clear cache

// ✅ Good: Invalidate on updates
$data->update($request->all());
Cache::forget('data');
```

### Image Optimization

```html
<!-- ❌ Bad: Large image, no lazy load -->
<img src="/storage/photos/huge-image.jpg" alt="Photo" />

<!-- ✅ Good: Lazy load -->
<img class="lazy" data-src="/storage/photos/image.jpg" alt="Photo" />

<!-- ✅ Better: Lazy load + responsive -->
<img
    class="lazy"
    data-srcset="
        /storage/photos/image-300.jpg 300w,
        /storage/photos/image-600.jpg 600w,
        /storage/photos/image-1200.jpg 1200w
    "
    sizes="(max-width: 640px) 300px, (max-width: 1024px) 600px, 1200px"
    alt="Photo"
/>
```

---

## Monitoring Checklist

### Daily Checks

-   [ ] Error logs review
-   [ ] Slow query log check
-   [ ] Cache hit ratio
-   [ ] Average response time

### Weekly Checks

-   [ ] Database size growth
-   [ ] Cache memory usage
-   [ ] Disk space
-   [ ] Backup verification

### Monthly Checks

-   [ ] Lighthouse audit
-   [ ] Database optimization (ANALYZE/OPTIMIZE)
-   [ ] Review slow queries
-   [ ] Update dependencies

---

## Performance Goals

### Page Load Times

-   Homepage: < 1.5 seconds
-   Dashboard: < 2 seconds
-   List pages: < 2.5 seconds
-   Detail pages: < 1.8 seconds

### Database Queries

-   Per page: < 20 queries
-   Execution time: < 100ms each
-   N+1 problems: 0

### Cache Hit Ratio

-   Target: > 80%
-   First visit: 0% (expected)
-   Return visits: 90%+

### Asset Sizes

-   CSS: < 120 KB (uncompressed)
-   JS: < 100 KB (uncompressed)
-   Images: < 200 KB each
-   Total page: < 2 MB

---

## Quick Commands Reference

```bash
# Performance Testing
docker-compose exec app php artisan optimize         # Optimize for production
docker-compose exec app php artisan optimize:clear   # Clear optimizations

# Cache Management
docker-compose exec app php artisan cache:clear      # Clear cache
docker-compose exec app php artisan config:cache     # Cache config
docker-compose exec app php artisan route:cache      # Cache routes
docker-compose exec app php artisan view:cache       # Cache views

# Database
docker-compose exec app php artisan migrate          # Run migrations
docker-compose exec app php artisan db:monitor       # Monitor DB
docker-compose exec app php artisan db:show          # Show DB info

# Assets
docker-compose exec app npm run build                # Build assets
docker-compose exec app npm run dev                  # Watch mode

# Monitoring
docker-compose logs app --tail=100 --follow          # Follow logs
docker-compose exec app php artisan tinker           # Interactive shell
```

---

**Last Updated:** Phase 6 Complete - October 2025  
**Next Phase:** Testing & Launch Preparation

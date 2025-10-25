<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceOptimizationService
{
    /**
     * Cache duration in seconds (1 hour default)
     */
    protected int $cacheDuration = 3600;

    /**
     * Get or cache frequently accessed data
     */
    public function remember(string $key, callable $callback, ?int $duration = null)
    {
        $duration = $duration ?? $this->cacheDuration;

        return Cache::remember($key, $duration, $callback);
    }

    /**
     * Cache user's active organizations
     */
    public function getUserOrganizations(int $userId)
    {
        return $this->remember("user.{$userId}.organizations", function () use ($userId) {
            return \App\Models\Organization::where('adviser_id', $userId)
                ->select('org_id', 'org_name', 'adviser_id')
                ->get();
        }, 1800); // 30 minutes
    }

    /**
     * Cache available priests for assignment
     */
    public function getAvailablePriests()
    {
        return $this->remember('priests.available', function () {
            return \App\Models\User::where('role', 'priest')
                ->where('account_status', 'verified')
                ->select('id', 'first_name', 'last_name', 'email')
                ->orderBy('first_name')
                ->get();
        }, 3600); // 1 hour
    }

    /**
     * Cache service types
     */
    public function getServices()
    {
        return $this->remember('services.all', function () {
            return \App\Models\Service::select('service_id', 'service_name', 'service_type')
                ->orderBy('service_name')
                ->get();
        }, 7200); // 2 hours
    }

    /**
     * Cache venues
     */
    public function getVenues()
    {
        return $this->remember('venues.all', function () {
            return \App\Models\Venue::select('venue_id', 'name', 'location', 'capacity')
                ->orderBy('name')
                ->get();
        }, 7200); // 2 hours
    }

    /**
     * Invalidate user-specific caches
     */
    public function invalidateUserCache(int $userId)
    {
        Cache::forget("user.{$userId}.organizations");
        Cache::forget("user.{$userId}.reservations.count");
        Cache::forget("user.{$userId}.notifications.count");
    }

    /**
     * Invalidate reservation-related caches
     */
    public function invalidateReservationCaches()
    {
        Cache::forget('reservations.stats');
        Cache::forget('priests.available');
    }

    /**
     * Invalidate all system caches
     */
    public function clearAllCaches()
    {
        Cache::flush();
        Log::info('All caches cleared');
    }

    /**
     * Get database query performance metrics
     */
    public function getQueryMetrics(): array
    {
        // Enable query logging
        DB::enableQueryLog();

        // Get logged queries
        $queries = DB::getQueryLog();

        return [
            'total_queries' => count($queries),
            'slow_queries' => collect($queries)->filter(fn($q) => $q['time'] > 100)->count(),
            'queries' => $queries
        ];
    }

    /**
     * Warm up commonly accessed caches
     */
    public function warmupCaches()
    {
        try {
            $this->getServices();
            $this->getVenues();
            $this->getAvailablePriests();

            Log::info('Caches warmed up successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache warmup failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $keys = [
            'services.all',
            'venues.all',
            'priests.available',
            'reservations.stats'
        ];

        $stats = [];
        foreach ($keys as $key) {
            $stats[$key] = Cache::has($key);
        }

        return $stats;
    }
}

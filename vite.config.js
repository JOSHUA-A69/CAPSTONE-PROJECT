import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                // Manual chunking for better caching
                manualChunks: {
                    'vendor': ['alpinejs'],
                },
            },
        },
        // Enable minification (using esbuild, faster than terser)
        minify: 'esbuild',
        // Source maps for debugging (disable in production)
        sourcemap: false,
        // Asset optimization
        assetsInlineLimit: 4096, // Inline assets smaller than 4kb
    },
    // CSS optimization
    css: {
        devSourcemap: true,
    },
    // Performance optimizations
    optimizeDeps: {
        include: ['alpinejs'],
    },
});

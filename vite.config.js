import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    build: {
        // Enable code splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split Chart.js into separate chunk (lazy loaded)
                    'chart': ['chart.js/auto'],
                    // Split vendor code
                    'vendor': ['axios']
                },
                // Optimize chunk names for better caching
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]'
            }
        },
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Minification settings
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
                passes: 2 // Run minification twice for better results
            },
            mangle: {
                safari10: true // Fix Safari 10 issues
            }
        },
        // Chunk size warnings
        chunkSizeWarningLimit: 500,
        // Disable source maps in production
        sourcemap: false,
        // Target modern browsers for smaller bundles
        target: 'es2020',
        // Optimize CSS
        cssMinify: 'lightningcss'
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['axios'],
        exclude: ['chart.js/auto'] // Exclude from pre-bundling since it's lazy loaded
    }
});
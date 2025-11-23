#!/bin/bash

echo "🚀 Starting Performance Optimization..."
echo ""

echo "📦 Building production assets..."
npm run build
echo "✅ Assets built!"
echo ""

echo "🖼️  Optimizing images..."
php artisan images:optimize
echo "✅ Images optimized!"
echo ""

echo "🗑️  Clearing caches..."
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
echo "✅ Caches cleared!"
echo ""

echo "⚡ Optimizing Laravel..."
php artisan optimize
php artisan view:cache
php artisan route:cache
php artisan config:cache
echo "✅ Laravel optimized!"
echo ""

echo "✨ Performance optimization complete!"
echo ""
echo "📊 Next steps:"
echo "1. Test your site speed"
echo "2. Run Lighthouse audit"
echo "3. Monitor with Laravel Telescope"

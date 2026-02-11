<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
    <head>
        <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body class="min-h-screen bg-white dark:bg-neutral-950 antialiased overflow-hidden">
        <?php
            $appName = \App\Models\SystemSetting::get('app_name', config('app.name'));
            $appTagline = \App\Models\SystemSetting::get('app_tagline', 'E-Learning Platform');
            $logo = \App\Models\SystemSetting::get('logo');
            $logoDark = \App\Models\SystemSetting::get('logo_dark');
        ?>
        
        <!-- Split Screen Layout -->
        <div class="flex min-h-screen">
            <!-- Left Side - Login Form -->
            <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:px-20 xl:px-24 relative">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <!-- Logo and Brand -->
                    <div class="mb-10">
                        <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-3 group" wire:navigate>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo || $logoDark): ?>
                                <img src="<?php echo e(asset('storage/' . ($logoDark ?: $logo))); ?>" alt="<?php echo e($appName); ?>" class="h-12 dark:hidden">
                                <img src="<?php echo e(asset('storage/' . ($logoDark ?: $logo))); ?>" alt="<?php echo e($appName); ?>" class="h-12 hidden dark:block">
                            <?php else: ?>
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-purple-600 shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                                    <?php if (isset($component)) { $__componentOriginal159d6670770cb479b1921cea6416c26c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal159d6670770cb479b1921cea6416c26c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-logo-icon','data' => ['class' => 'size-7 fill-current text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-logo-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-7 fill-current text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $attributes = $__attributesOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__attributesOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $component = $__componentOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__componentOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($appName); ?></h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($appTagline); ?></p>
                            </div>
                        </a>
                    </div>

                    <!-- Form Content -->
                    <div>
                        <?php echo e($slot); ?>

                    </div>

                    <!-- Back to Home Link -->
                    <div class="mt-8">
                        <a href="<?php echo e(route('home')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors inline-flex items-center gap-2" wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to homepage
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Side - Brand/Illustration -->
            <div class="hidden lg:block relative flex-1 bg-blue-600 dark:bg-blue-700">
                <!-- Subtle Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="relative h-full flex flex-col justify-center px-12 xl:px-16 text-white">
                    <div class="max-w-md">
                        <h1 class="text-4xl xl:text-5xl font-bold mb-6 leading-tight">
                            Bridge the Digital Divide Through Code
                        </h1>
                        <p class="text-lg xl:text-xl text-white/90 mb-8 leading-relaxed">
                            Join Code Academy Uganda's comprehensive e-learning platform. Learn web development, mobile apps, robotics, and earn ICDL certifications.
                        </p>

                        <!-- Features List -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-white/90">Accredited ICDL Testing Center</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-white/90">Holiday Code Camps for Ages 7-19</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-white/90">Web, Mobile & Robotics Training</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-white/90">Based in Kampala, Uganda</span>
                            </div>
                        </div>

                        <!-- Stats Card -->
                        <div class="mt-12 p-6 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-3xl font-bold text-white">500+</div>
                                    <div class="text-xs text-white/80 mt-1">Students</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-white">50+</div>
                                    <div class="text-xs text-white/80 mt-1">Courses</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-white">ICDL</div>
                                    <div class="text-xs text-white/80 mt-1">Certified</div>
                                </div>
                            </div>
                        </div>

                        <!-- Quote -->
                        <div class="mt-8 pt-8 border-t border-white/20">
                            <p class="text-white/80 text-sm italic">
                                "Empowering the next generation of Ugandan developers with world-class tech education and globally recognized certifications."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

    </body>
</html>
<?php /**PATH C:\wamp64\www\codecamp-system\resources\views/components/layouts/auth/simple.blade.php ENDPATH**/ ?>
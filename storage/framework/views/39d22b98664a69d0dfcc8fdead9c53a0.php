<div class="min-h-screen bg-gradient-to-br from-gray-50 via-orange-50 to-yellow-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6 space-y-8">
    
    <div class="relative overflow-hidden bg-gradient-to-r from-orange-600 via-yellow-600 to-orange-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative z-10 flex items-center justify-between">
        <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold">Content Approvals</h1>
                </div>
                <p class="text-orange-100 text-lg">Review and approve content submissions for quality assurance</p>
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm"><?php echo e(now()->format('l, F j, Y')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <a href="?filterStatus=pending" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if(($stats['pending'] ?? 0) > 0): ?>
                        <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-full animate-pulse">Action Required</span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pending</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2"><?php echo e(number_format($stats['pending'] ?? 0)); ?></p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <!--[if BLOCK]><![endif]--><?php if(($stats['pending'] ?? 0) > 0): ?>
                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                            <span>Requires attention</span>
                        <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span>All clear!</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        </a>

        
        <a href="?filterStatus=approved" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Approved</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2"><?php echo e(number_format($stats['approved'] ?? 0)); ?></p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Quality approved</span>
                    </div>
                </div>
            </div>
        </a>

        
        <a href="?filterStatus=rejected" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Rejected</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2"><?php echo e(number_format($stats['rejected'] ?? 0)); ?></p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Needs revision</span>
                    </div>
                </div>
            </div>
        </a>

        
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2"><?php echo e(number_format($stats['total'] ?? 0)); ?></p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>All submissions</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Search</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Search content...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Search content...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'filterStatus','label' => 'Status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterStatus','label' => 'Status']); ?>
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'filterType','label' => 'Type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterType','label' => 'Type']); ?>
                <option value="all">All Types</option>
                <option value="course">Courses</option>
                <option value="lesson">Lessons</option>
                <option value="module">Modules</option>
                <option value="assessment">Assessments</option>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'filterPriority','label' => 'Priority']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterPriority','label' => 'Priority']); ?>
                <option value="all">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($approvals->count() > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Content Submissions</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and manage content awaiting approval</p>
            </div>
            <div class="p-6">
        <div class="space-y-4">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('content-approvals.review', $approval)); ?>" class="block">
                            <div class="rounded-lg border-2 <?php echo e($approval->status === 'pending' ? 'border-orange-300 dark:border-orange-700 bg-orange-50/50 dark:bg-orange-900/10' : ($approval->status === 'approved' ? 'border-green-300 dark:border-green-700 bg-green-50/50 dark:bg-green-900/10' : 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-900/10')); ?> hover:shadow-lg transition-all duration-300 overflow-hidden cursor-pointer p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            <?php
                                                $title = $approval->approvable ? match(class_basename($approval->approvable_type)) {
                                                    'Course' => $approval->approvable->title,
                                                    'Lesson' => $approval->approvable->title,
                                                    'CourseModule' => $approval->approvable->title,
                                                    'Assessment' => $approval->approvable->title,
                                                    default => 'Unknown Content',
                                                } : 'Deleted Item';
                                            ?>
                                            <?php echo e($title); ?>

                                        </h3>
                                        <!--[if BLOCK]><![endif]--><?php if($approval->status === 'approved'): ?>
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-green-100 dark:bg-green-900/40 border border-green-200 dark:border-green-800 rounded-md">
                                                <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span class="text-xs font-semibold text-green-700 dark:text-green-300"><?php echo e(ucfirst($approval->status)); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'sm','variant' => ''.e($approval->status === 'rejected' ? 'danger' : 'warning').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','variant' => ''.e($approval->status === 'rejected' ? 'danger' : 'warning').'']); ?>
                                                <?php echo e(ucfirst($approval->status)); ?>

                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'sm','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','variant' => 'ghost']); ?>
                                            <?php echo e(class_basename($approval->approvable_type)); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                                        <!--[if BLOCK]><![endif]--><?php if($approval->priority): ?>
                                            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'xs','variant' => ''.e($approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs','variant' => ''.e($approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost')).'']); ?>
                                                <?php echo e(ucfirst($approval->priority)); ?> Priority
                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            <span>Submitted by <span class="font-medium text-gray-900 dark:text-white"><?php echo e($approval->submitter->name ?? 'Unknown'); ?></span></span>
                                        <span>•</span>
                                        <span><?php echo e($approval->submitted_at->diffForHumans()); ?></span>
                                        <!--[if BLOCK]><![endif]--><?php if($approval->reviewed_at): ?>
                                            <span>•</span>
                                            <span>Reviewed <?php echo e($approval->reviewed_at->diffForHumans()); ?></span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if($approval->rejection_reason): ?>
                                            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <p class="text-sm text-red-800 dark:text-red-200">
                                                    <strong>Rejection Reason:</strong> <?php echo e($approval->rejection_reason); ?>

                                            </p>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <!--[if BLOCK]><![endif]--><?php if($approval->status === 'pending'): ?>
                                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click.stop' => 'approveContent('.e($approval->id).')','variant' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click.stop' => 'approveContent('.e($approval->id).')','variant' => 'primary','size' => 'sm']); ?>
                                            Approve
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click.stop' => 'rejectContent('.e($approval->id).', \'Needs revision\')','variant' => 'danger','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click.stop' => 'rejectContent('.e($approval->id).', \'Needs revision\')','variant' => 'danger','size' => 'sm']); ?>
                                            Reject
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="mt-6">
            <?php echo e($approvals->links()); ?>

                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">All Clear!</h3>
            <p class="text-gray-600 dark:text-gray-400">No content approvals found matching your filters</p>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/livewire/content-approvals/index.blade.php ENDPATH**/ ?>
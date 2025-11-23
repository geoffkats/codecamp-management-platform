<div class="max-w-5xl mx-auto p-8 space-y-8">
    
    <div class="flex items-center justify-between pb-6 border-b <?php echo e($approval->status === 'approved' ? 'border-green-300 dark:border-green-700' : 'border-gray-200 dark:border-gray-700'); ?>">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?php echo e(route('content-approvals.index')); ?>" wire:navigate 
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Content Review</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($contentType); ?> • Approval Request</p>
                </div>
            </div>
        </div>
        <!--[if BLOCK]><![endif]--><?php if($approval->status === 'approved'): ?>
            <div class="flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="px-3 py-1 text-sm font-semibold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/40 rounded-md">
                    <?php echo e(ucfirst($approval->status)); ?>

                </span>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['variant' => ''.e($approval->status === 'pending' ? 'warning' : 'danger').'','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($approval->status === 'pending' ? 'warning' : 'danger').'','size' => 'lg']); ?>
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
    </div>

    
    <div class="bg-white dark:bg-gray-800 border <?php echo e($approval->status === 'approved' ? 'border-green-200 dark:border-green-800' : 'border-gray-200 dark:border-gray-700'); ?> rounded-lg overflow-hidden">
        <div class="px-6 py-4 <?php echo e($approval->status === 'approved' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700'); ?> border-b">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Submission Details</h2>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Content Type</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($contentType); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Submitted By</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approval->submitter->name ?? 'Unknown'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Submitted Date</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approval->submitted_at?->format('M d, Y \a\t g:i A')); ?></dd>
                </div>
                <!--[if BLOCK]><![endif]--><?php if($approval->priority): ?>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Priority</dt>
                        <dd>
                            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['variant' => ''.e($approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost')).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost')).'','size' => 'sm']); ?>
                                <?php echo e(ucfirst($approval->priority)); ?>

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
                        </dd>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </dl>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Content Preview</h2>
                <!--[if BLOCK]><![endif]--><?php if($approvable): ?>
                    <!--[if BLOCK]><![endif]--><?php if($approvable instanceof \App\Models\Course): ?>
                        <a href="<?php echo e(route('courses.show', $approvable)); ?>" target="_blank" 
                           class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                            View Full Course
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    <?php elseif($approvable instanceof \App\Models\Lesson): ?>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('curriculum.builder', ['course' => $approvable->course_id])); ?>" target="_blank" 
                               class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                                Edit in Curriculum Builder
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        
        <!--[if BLOCK]><![endif]--><?php if($approvable): ?>
            <div class="p-6">
                <!--[if BLOCK]><![endif]--><?php if(isset($approvable->title)): ?>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"><?php echo e($approvable->title); ?></h3>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                
                <!--[if BLOCK]><![endif]--><?php if(isset($approvable->description)): ?>
                    <div class="prose prose-sm dark:prose-invert max-w-none mb-6">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed"><?php echo e($approvable->description); ?></p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if(isset($approvable->short_description)): ?>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed"><?php echo e($approvable->short_description); ?></p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($approvable instanceof \App\Models\Course): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Instructor</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->instructor->name ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Category</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->category ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Difficulty</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->difficulty_level ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Status</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->is_published ? 'Published' : 'Draft'); ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($approvable instanceof \App\Models\Lesson): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Lesson Type</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->lesson_type ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Duration</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($approvable->duration_minutes ?? 'N/A'); ?> minutes</dd>
                            </div>
                        </dl>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php else: ?>
            <div class="p-6">
                <p class="text-gray-500 dark:text-gray-400 italic">Content not found or has been deleted.</p>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($submissionCount > 0): ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Submissions Awaiting Grading</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($submissionCount); ?> <?php echo e(Str::plural('submission', $submissionCount)); ?> need grading</p>
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if($submissionCount > 0): ?>
                        <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['variant' => 'warning','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'warning','size' => 'sm']); ?><?php echo e($submissionCount); ?> Pending <?php echo $__env->renderComponent(); ?>
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
            </div>
            
            <div class="p-6">
                <div class="space-y-3">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $pendingSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($submission->assignment->title); ?>

                                    </h4>
                                    <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'xs','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs','variant' => 'ghost']); ?>
                                        <?php echo e($submission->assignment->course->title ?? 'N/A'); ?>

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
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <?php echo e($submission->user->name); ?>

                                    </span>
                                    <!--[if BLOCK]><![endif]--><?php if($submission->submitted_at): ?>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <?php echo e($submission->submitted_at->diffForHumans()); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($submission->assignment->max_points): ?>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                            <?php echo e($submission->assignment->max_points); ?> points
                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <div class="ml-4 flex items-center gap-2">
                                <a href="<?php echo e(route('grades.grade', $submission)); ?>" 
                                   wire:navigate
                                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                    Grade
                                </a>
                                <a href="<?php echo e(route('submissions.show', $submission)); ?>" 
                                   target="_blank"
                                   class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                                   title="View Submission">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                
                <!--[if BLOCK]><![endif]--><?php if($submissionCount > 5): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Showing <?php echo e(min(5, count($pendingSubmissions))); ?> of <?php echo e($submissionCount); ?> submissions
                        </p>
                        <a href="<?php echo e(route('submissions.index')); ?>" 
                           wire:navigate
                           class="mt-2 block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            View All Submissions →
                        </a>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Review & Decision</h2>
        </div>
        
        <div class="p-6 space-y-6">
            <div>
                <?php if (isset($component)) { $__componentOriginaldbce252eb40169cc4a74f0123aabaf0b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::field','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal8a84eac5abb8af1e2274971f8640b38f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::label','data' => ['class' => 'text-sm font-medium text-gray-700 dark:text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-sm font-medium text-gray-700 dark:text-gray-300']); ?>Review Notes <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $attributes = $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $component = $__componentOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalf323826200b199a8f33f16501b918a9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf323826200b199a8f33f16501b918a9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::description','data' => ['class' => 'text-xs text-gray-500 dark:text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-xs text-gray-500 dark:text-gray-400']); ?>Optional notes that will be shared with the submitter <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $attributes = $__attributesOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__attributesOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $component = $__componentOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__componentOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'notes','rows' => '4','placeholder' => 'Add any feedback or notes for the submitter...','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'notes','rows' => '4','placeholder' => 'Add any feedback or notes for the submitter...','class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b)): ?>
<?php $attributes = $__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b; ?>
<?php unset($__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldbce252eb40169cc4a74f0123aabaf0b)): ?>
<?php $component = $__componentOriginaldbce252eb40169cc4a74f0123aabaf0b; ?>
<?php unset($__componentOriginaldbce252eb40169cc4a74f0123aabaf0b); ?>
<?php endif; ?>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <?php if (isset($component)) { $__componentOriginaldbce252eb40169cc4a74f0123aabaf0b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::field','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal8a84eac5abb8af1e2274971f8640b38f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::label','data' => ['class' => 'text-sm font-medium text-gray-700 dark:text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-sm font-medium text-gray-700 dark:text-gray-300']); ?>Rejection Reason <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $attributes = $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $component = $__componentOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalf323826200b199a8f33f16501b918a9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf323826200b199a8f33f16501b918a9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::description','data' => ['class' => 'text-xs text-gray-500 dark:text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-xs text-gray-500 dark:text-gray-400']); ?>Required if you plan to reject this submission <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $attributes = $__attributesOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__attributesOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $component = $__componentOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__componentOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'rejectionReason','rows' => '4','placeholder' => 'Provide a clear and detailed reason for rejection...','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'rejectionReason','rows' => '4','placeholder' => 'Provide a clear and detailed reason for rejection...','class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b)): ?>
<?php $attributes = $__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b; ?>
<?php unset($__attributesOriginaldbce252eb40169cc4a74f0123aabaf0b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldbce252eb40169cc4a74f0123aabaf0b)): ?>
<?php $component = $__componentOriginaldbce252eb40169cc4a74f0123aabaf0b; ?>
<?php unset($__componentOriginaldbce252eb40169cc4a74f0123aabaf0b); ?>
<?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="<?php echo e(route('content-approvals.index')); ?>" wire:navigate 
           class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            Cancel
        </a>
        <div class="flex items-center gap-3">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'reject','variant' => 'danger','disabled' => empty($rejectionReason)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'reject','variant' => 'danger','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($rejectionReason))]); ?>
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
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'approve','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'approve','variant' => 'primary']); ?>
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
        </div>
    </div>
</div>

<!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-xl rounded-lg px-4 py-3 z-50 max-w-md">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e(session('message')); ?></p>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<?php if(session()->has('error')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-xl rounded-lg px-4 py-3 z-50 max-w-md">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e(session('error')); ?></p>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/livewire/content-approvals/review.blade.php ENDPATH**/ ?>
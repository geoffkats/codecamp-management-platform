<div class="p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Teacher Feedback Management</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Review and respond to student feedback about teachers</p>
            </div>
            <button wire:click="exportCSV" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </button>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <span class="text-green-800 dark:text-green-200"><?php echo e(session('message')); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total']); ?></div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Feedback</div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg shadow p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="text-2xl font-bold text-yellow-800 dark:text-yellow-200"><?php echo e($stats['pending']); ?></div>
                <div class="text-sm text-yellow-700 dark:text-yellow-300">Pending</div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-4 border border-blue-200 dark:border-blue-800">
                <div class="text-2xl font-bold text-blue-800 dark:text-blue-200"><?php echo e($stats['reviewed']); ?></div>
                <div class="text-sm text-blue-700 dark:text-blue-300">Reviewed</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg shadow p-4 border border-green-200 dark:border-green-800">
                <div class="text-2xl font-bold text-green-800 dark:text-green-200"><?php echo e($stats['resolved']); ?></div>
                <div class="text-sm text-green-700 dark:text-green-300">Resolved</div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg shadow p-4 border border-purple-200 dark:border-purple-800">
                <div class="text-2xl font-bold text-purple-800 dark:text-purple-200"><?php echo e($stats['average_rating'] ?? 'N/A'); ?></div>
                <div class="text-sm text-purple-700 dark:text-purple-300">Avg Rating</div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select wire:model.live="filterStatus" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teacher</label>
                    <select wire:model.live="filterTeacher" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">All Teachers</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                    <select wire:model.live="filterCategory" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="all">All Categories</option>
                        <option value="teaching_quality">Teaching Quality</option>
                        <option value="communication">Communication</option>
                        <option value="support">Student Support</option>
                        <option value="professionalism">Professionalism</option>
                        <option value="general">General</option>
                    </select>
                </div>
            </div>
        </div>

        
        <div class="space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $feedbackList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($feedback->is_anonymous ? 'Anonymous Student' : $feedback->student->name); ?>

                                </h3>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo e($feedback->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : ($feedback->status === 'reviewed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400')); ?>">
                                    <?php echo e(ucfirst($feedback->status)); ?>

                                </span>
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $feedback->category))); ?>

                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <p><strong>Teacher:</strong> <?php echo e($feedback->teacher->name); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedback->course): ?>
                                    <p><strong>Course:</strong> <?php echo e($feedback->course->title); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedback->rating): ?>
                                    <p><strong>Rating:</strong> 
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?php echo e($i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'); ?>">★</span>
                                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p><strong>Submitted:</strong> <?php echo e($feedback->created_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="viewFeedback(<?php echo e($feedback->id); ?>)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                                View Details
                            </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedback->status === 'reviewed'): ?>
                                <button wire:click="markAsResolved(<?php echo e($feedback->id); ?>)" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                                    Mark Resolved
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <p class="text-gray-700 dark:text-gray-300"><?php echo e($feedback->feedback); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No feedback found matching your filters.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="mt-6">
            <?php echo e($feedbackList->links()); ?>

        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal && $selectedFeedback): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Feedback Details</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Student</label>
                                <p class="text-gray-900 dark:text-white"><?php echo e($selectedFeedback->is_anonymous ? 'Anonymous' : $selectedFeedback->student->name); ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Teacher</label>
                                <p class="text-gray-900 dark:text-white"><?php echo e($selectedFeedback->teacher->name); ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Course</label>
                                <p class="text-gray-900 dark:text-white"><?php echo e($selectedFeedback->course ? $selectedFeedback->course->title : 'General Feedback'); ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Category</label>
                                <p class="text-gray-900 dark:text-white"><?php echo e(ucfirst(str_replace('_', ' ', $selectedFeedback->category))); ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedFeedback->rating): ?>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Rating</label>
                                    <p class="text-2xl">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?php echo e($i <= $selectedFeedback->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'); ?>">★</span>
                                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Submitted</label>
                                <p class="text-gray-900 dark:text-white"><?php echo e($selectedFeedback->created_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Feedback</label>
                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p class="text-gray-900 dark:text-white"><?php echo e($selectedFeedback->feedback); ?></p>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedFeedback->status !== 'pending'): ?>
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Review Information</label>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Reviewed by <?php echo e($selectedFeedback->reviewer->name ?? 'Unknown'); ?> on <?php echo e($selectedFeedback->reviewed_at?->format('M d, Y H:i')); ?>

                                </p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div>
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Admin Response</label>
                            <textarea wire:model="adminResponse" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Enter your response or notes..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button wire:click="closeModal" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button wire:click="markAsReviewed" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                                Mark as Reviewed & Save Response
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\wamp64\www\codecamp-system\resources\views/livewire/admin/manage-teacher-feedback.blade.php ENDPATH**/ ?>
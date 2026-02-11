<div class="flex h-screen bg-gray-50 dark:bg-gray-950"
     x-data="{ collapsed: <?php if ((object) ('sidebarCollapsed') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('sidebarCollapsed'->value()); ?>')<?php echo e('sidebarCollapsed'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('sidebarCollapsed'); ?>')<?php endif; ?> }"
     x-init="
         document.addEventListener('keydown', (e) => {
             if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                 e.preventDefault();
                 $wire.toggleSidebar();
             }
         }, { passive: false });
     ">
    
    <div class="transition-all duration-300 bg-white dark:bg-gray-900 overflow-hidden <?php echo e($sidebarCollapsed ? 'w-0 border-0' : 'w-96 border-r border-gray-200 dark:border-gray-800 overflow-y-auto'); ?>">
        <div class="<?php echo e($sidebarCollapsed ? 'hidden' : 'block'); ?>" style="width: 384px;">
            <div wire:loading.delay class="p-6 space-y-4">
                <div class="h-5 w-32 rounded bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-10 w-full rounded bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-10 w-4/5 rounded bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-10 w-3/5 rounded bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-10 w-2/3 rounded bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
            </div>
            <div wire:loading.remove>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$courseId): ?>
                
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Select Course</h2>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($courses->count() > 0): ?>
                        <div class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a wire:key="course-<?php echo e($courseOption->id); ?>"
                                   href="<?php echo e(route('curriculum.builder', ['course' => $courseOption->id])); ?>"
                                   wire:navigate
                                   class="block p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-gray-900 dark:hover:border-gray-100 hover:shadow-sm transition-all">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo e($courseOption->title); ?></h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <?php echo e($courseOption->modules_count); ?> modules
                                            </p>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($courseOption->instructor_id !== auth()->id()): ?>
                                            <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full whitespace-nowrap">
                                                Collaborator
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 dark:text-gray-400">No courses available</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Structure</h2>
                        <a href="<?php echo e(route('curriculum.builder')); ?>" wire:navigate class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            ← Courses
                        </a>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course): ?>
                        <div class="mb-4 p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($course->title); ?></h3>
                        </div>

                        <?php
                            $activeModules = $course->modules->filter(fn($m) => !method_exists($m, 'trashed') || !$m->trashed());
                            $archivedModules = $course->modules->filter(fn($m) => method_exists($m, 'trashed') && $m->trashed());
                        ?>

                        <div class="flex items-center gap-2 mb-4">
                            <button wire:click="setStructureTab('active')"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                                        <?php echo e($structureTab === 'active' ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800'); ?>">
                                Active (<?php echo e($activeModules->count()); ?>)
                            </button>
                            <button wire:click="setStructureTab('archived')"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                                        <?php echo e($structureTab === 'archived' ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800'); ?>">
                                Archived (<?php echo e($archivedModules->count()); ?>)
                            </button>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($structureTab === 'active'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'build' && $canManageCourse): ?>
                                <button wire:click="selectItem('module')"
                                        class="w-full mb-4 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition-colors">
                                    + Add Module
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $activeLessons = $module->lessons->filter(fn($lesson) => !method_exists($lesson, 'trashed') || !$lesson->trashed());
                                    ?>
                                    <details wire:key="module-<?php echo e($module->id); ?>" class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden" open>
                                        <summary wire:click="selectItem('module', <?php echo e($module->id); ?>)"
                                                 class="cursor-pointer list-none px-4 py-3 text-left bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors
                                                        <?php echo e($selectedType === 'module' && $selectedId === $module->id ? 'bg-gray-50 dark:bg-gray-800' : ''); ?>">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="flex-1 font-semibold text-gray-900 dark:text-white whitespace-normal break-words leading-snug"><?php echo e($module->title); ?></span>
                                                <span class="text-xs text-gray-500 whitespace-nowrap"><?php echo e($activeLessons->count()); ?> lessons</span>
                                            </div>
                                        </summary>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                            <div class="flex justify-end gap-3 px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-800">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                    <button wire:click="deleteModule(<?php echo e($module->id); ?>)"
                                                            class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1"
                                                            title="Archive to keep recoverable">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10" />
                                                        </svg>
                                                        <span>Archive</span>
                                                    </button>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeLessons->count() === 0): ?>
                                                        <button wire:click="forceDeleteModule(<?php echo e($module->id); ?>)"
                                                                class="text-sm font-semibold text-red-700 hover:text-red-800 dark:text-red-200 flex items-center gap-2 px-3 py-1.5 rounded-md border border-red-200 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
                                                                title="This removes the module forever. No undo.">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7m3 4v6m4-6v6" />
                                                            </svg>
                                                            <span>Delete Permanently</span>
                                                        </button>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <button type="button" disabled
                                                            class="text-sm text-gray-400 dark:text-gray-500 flex items-center gap-1 cursor-not-allowed"
                                                            title="You do not have permission to manage modules.">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10" />
                                                        </svg>
                                                        <span>Archive</span>
                                                    </button>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeLessons->count() === 0): ?>
                                                        <button type="button" disabled
                                                                class="text-sm font-semibold text-gray-400 dark:text-gray-500 flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 cursor-not-allowed"
                                                                title="You do not have permission to manage modules.">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7m3 4v6m4-6v6" />
                                                            </svg>
                                                            <span>Delete Permanently</span>
                                                        </button>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <div class="pl-4 pb-3">
                                            <div class="ml-2 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                                                <div class="grid grid-cols-12 gap-2 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
                                                    <div class="col-span-9">Lesson</div>
                                                    <div class="col-span-1 text-right">Quizzes</div>
                                                    <div class="col-span-2 text-right">Actions</div>
                                                </div>
                                                <div class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div wire:key="lesson-<?php echo e($lesson->id); ?>">
                                                            <div class="grid grid-cols-12 gap-2 px-3 py-2 items-center group relative"
                                                                 x-data="{ menuOpen: false, menuX: 0, menuY: 0 }"
                                                                 @contextmenu.prevent="menuOpen = true; menuX = $event.offsetX; menuY = $event.offsetY;"
                                                                 @click="menuOpen = false">
                                                                <button type="button" wire:click.debounce.100ms="selectItem('lesson', <?php echo e($lesson->id); ?>)"
                                                                        class="col-span-9 text-left text-sm text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">
                                                                    <div class="flex items-center gap-2">
                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->approval_status === 'approved'): ?>
                                                                                <span class="inline-flex w-2 h-2 rounded-full bg-green-500"></span>
                                                                            <?php elseif($lesson->approval_status === 'pending'): ?>
                                                                                <span class="inline-flex w-2 h-2 rounded-full bg-yellow-500"></span>
                                                                            <?php elseif($lesson->approval_status === 'rejected'): ?>
                                                                                <span class="inline-flex w-2 h-2 rounded-full bg-red-500"></span>
                                                                            <?php else: ?>
                                                                                <span class="inline-flex w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        <?php else: ?>
                                                                            <span class="inline-flex w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        <span class="whitespace-normal break-words leading-snug"><?php echo e($lesson->title); ?></span>
                                                                    </div>
                                                                </button>
                                                                <div class="col-span-1 text-right text-xs text-gray-500 dark:text-gray-400">
                                                                    <?php echo e($lesson->assessments_count ?? $lesson->assessments->count()); ?>

                                                                </div>
                                                                <div class="col-span-2 flex justify-end gap-1">
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                            <button wire:click.stop.debounce.150ms="toggleLessonLock(<?php echo e($lesson->id); ?>)"
                                                                                title="<?php echo e($lesson->is_locked ? 'Unlock lesson' : 'Lock lesson'); ?>"
                                                                                class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->is_locked): ?>
                                                                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                                    </svg>
                                                                                <?php else: ?>
                                                                                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                                                    </svg>
                                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                            </button>
                                                                            <button wire:click.stop.debounce.150ms="deleteLesson(<?php echo e($lesson->id); ?>)"
                                                                                title="Archive lesson"
                                                                                class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10" />
                                                                                </svg>
                                                                            </button>
                                                                        <?php else: ?>
                                                                            <button type="button" disabled
                                                                                title="You do not have permission to manage lessons."
                                                                                class="p-1.5 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                                                </svg>
                                                                            </button>
                                                                            <button type="button" disabled
                                                                                title="You do not have permission to manage lessons."
                                                                                class="p-1.5 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10" />
                                                                                </svg>
                                                                            </button>
                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </div>

                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                                    <div x-show="menuOpen"
                                                                         x-transition.opacity.duration.150ms
                                                                         @click.away="menuOpen = false"
                                                                         class="absolute z-50 min-w-[180px] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                                                         :style="`left: ${menuX}px; top: ${menuY}px;`">
                                                                        <div class="py-1">
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                                <button type="button"
                                                                                        wire:click.stop.debounce.150ms="selectItem('lesson', <?php echo e($lesson->id); ?>)"
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                                    Edit lesson
                                                                                </button>
                                                                                <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                                                <button type="button"
                                                                                        wire:click.stop.debounce.150ms="toggleLessonLock(<?php echo e($lesson->id); ?>)"
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                                    <?php echo e($lesson->is_locked ? 'Unlock lesson' : 'Lock lesson'); ?>

                                                                                </button>
                                                                                <button type="button"
                                                                                        wire:click.stop.debounce.150ms="deleteLesson(<?php echo e($lesson->id); ?>)"
                                                                                        class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                                                    Archive lesson
                                                                                </button>
                                                                                <button type="button"
                                                                                        wire:click.stop.debounce.150ms="selectItem('assessment', null, <?php echo e($lesson->id); ?>)"
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                                    Add quiz
                                                                                </button>
                                                                            <?php else: ?>
                                                                                <button type="button" disabled
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                    Edit lesson
                                                                                </button>
                                                                                <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                                                <button type="button" disabled
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                    Lock lesson
                                                                                </button>
                                                                                <button type="button" disabled
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                    Archive lesson
                                                                                </button>
                                                                                <button type="button" disabled
                                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                    Add quiz
                                                                                </button>
                                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage' && $lesson->assessments->count() > 0): ?>
                                                                <div class="pl-6 pb-2">
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lesson->assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assessment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <div wire:key="assessment-<?php echo e($assessment->id); ?>" class="flex items-center group relative"
                                                                             x-data="{ menuOpen: false, menuX: 0, menuY: 0 }"
                                                                             @contextmenu.prevent="menuOpen = true; menuX = $event.offsetX; menuY = $event.offsetY;"
                                                                             @click="menuOpen = false">
                                                                            <button wire:click.debounce.100ms="selectItem('assessment', <?php echo e($assessment->id); ?>)"
                                                                                    class="flex-1 px-3 py-1.5 text-left text-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                                                                           <?php echo e($selectedType === 'assessment' && $selectedId === $assessment->id ? 'bg-gray-50 dark:bg-gray-800' : ''); ?>">
                                                                                <div class="flex items-center gap-2">
                                                                                    <span class="inline-flex w-2 h-2 rounded-full bg-purple-400"></span>
                                                                                    <span class="text-gray-600 dark:text-gray-400 whitespace-normal break-words leading-snug"><?php echo e($assessment->title); ?></span>
                                                                                </div>
                                                                            </button>
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                                <button wire:click.stop.debounce.150ms="toggleAssessmentLock(<?php echo e($assessment->id); ?>)"
                                                                                        title="<?php echo e($assessment->is_locked ? 'Unlock quiz' : 'Lock quiz'); ?>"
                                                                                        class="px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assessment->is_locked): ?>
                                                                                        <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                                        </svg>
                                                                                    <?php else: ?>
                                                                                        <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                                                            <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                                                        </svg>
                                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                </button>
                                                                            <?php else: ?>
                                                                                <button type="button" disabled
                                                                                        title="You do not have permission to manage quizzes."
                                                                                        class="px-2 py-1.5 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                                                    </svg>
                                                                                </button>
                                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                                            <div x-show="menuOpen"
                                                                                 x-transition.opacity.duration.150ms
                                                                                 @click.away="menuOpen = false"
                                                                                 class="absolute z-50 min-w-[180px] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                                                                 :style="`left: ${menuX}px; top: ${menuY}px;`">
                                                                                <div class="py-1">
                                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                                        <button type="button"
                                                                                                wire:click.stop.debounce.150ms="selectItem('assessment', <?php echo e($assessment->id); ?>)"
                                                                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                                            Edit quiz
                                                                                        </button>
                                                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                                                        <button type="button"
                                                                                                wire:click.stop.debounce.150ms="toggleAssessmentLock(<?php echo e($assessment->id); ?>)"
                                                                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                                            <?php echo e($assessment->is_locked ? 'Unlock quiz' : 'Lock quiz'); ?>

                                                                                        </button>
                                                                                        <button type="button"
                                                                                                wire:click.stop.debounce.150ms="deleteAssessment(<?php echo e($assessment->id); ?>)"
                                                                                                class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                                                            Archive quiz
                                                                                        </button>
                                                                                    <?php else: ?>
                                                                                        <button type="button" disabled
                                                                                                class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                            Edit quiz
                                                                                        </button>
                                                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                                                        <button type="button" disabled
                                                                                                class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                            Lock quiz
                                                                                        </button>
                                                                                        <button type="button" disabled
                                                                                                class="w-full px-3 py-2 text-left text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                                                            Archive quiz
                                                                                        </button>
                                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </div>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                                <div class="pl-6 pb-2">
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                        <button wire:click="selectItem('assessment', null, <?php echo e($lesson->id); ?>)"
                                                                                class="w-full px-3 py-1.5 text-left text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                                            + Add Quiz
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button type="button" disabled
                                                                                class="w-full px-3 py-1.5 text-left text-xs text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                                                title="You do not have permission to add quizzes.">
                                                                            + Add Quiz
                                                                        </button>
                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </div>
                                                            <?php elseif($mode === 'build' && $selectedType === 'lesson' && $selectedId === $lesson->id && $canManageCourse): ?>
                                                                <div class="pl-6 pb-2">
                                                                    <button wire:click="selectItem('assessment', null, <?php echo e($lesson->id); ?>)"
                                                                            class="w-full px-3 py-1.5 text-left text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                                        + Add Quiz (optional)
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'build' && $canManageCourse): ?>
                                                <button wire:click="selectItem('lesson', null, <?php echo e($module->id); ?>)"
                                                        class="mt-2 w-full px-4 py-2 text-left text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                    + Add Lesson
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </details>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                Archived items are read-only in the builder.
                            </div>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $archivedModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $archivedLessons = $module->lessons->filter(fn($lesson) => method_exists($lesson, 'trashed') && $lesson->trashed());
                                    ?>
                                    <details wire:key="archived-module-<?php echo e($module->id); ?>" class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden opacity-80">
                                        <summary wire:click="selectItem('module', <?php echo e($module->id); ?>)"
                                                 class="cursor-pointer list-none px-4 py-3 text-left bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($module->title); ?></span>
                                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">Archived</span>
                                            </div>
                                        </summary>
                                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                    <button wire:click="restoreModule(<?php echo e($module->id); ?>)"
                                                            class="text-sm text-green-600 hover:text-green-700 dark:text-green-300">Restore Module</button>
                                                <?php else: ?>
                                                    <button type="button" disabled
                                                            class="text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                            title="You do not have permission to restore modules.">Restore Module</button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="pl-4 pb-2">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $archivedLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div wire:key="archived-lesson-<?php echo e($lesson->id); ?>" class="border-l-2 border-gray-200 dark:border-gray-800 ml-2">
                                                    <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                                        <span class="whitespace-normal break-words leading-snug"><?php echo e($lesson->title); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManageCourse): ?>
                                                                <button wire:click.stop.debounce.150ms="restoreLesson(<?php echo e($lesson->id); ?>)"
                                                                        class="text-xs text-green-600 hover:text-green-700 dark:text-green-300">Restore</button>
                                                            <?php else: ?>
                                                                <button type="button" disabled
                                                                        class="text-xs text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                                        title="You do not have permission to restore lessons.">Restore</button>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </details>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    
    
    <div class="fixed left-0 top-1/2 -translate-y-1/2 z-50 <?php echo e($sidebarCollapsed ? 'translate-x-0' : 'translate-x-96'); ?>" style="transition: transform 200ms ease-out;">
        <button wire:click.debounce.100ms="toggleSidebar" 
                class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-r-lg shadow-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-xl transition-colors duration-150 relative"
                x-data="{ showTooltip: false }"
                @mouseenter="showTooltip = true"
                @mouseleave="showTooltip = false">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 <?php echo e($sidebarCollapsed ? '' : 'rotate-180'); ?>" 
                 style="transition: transform 150ms ease-out;"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            
            
            <div x-show="showTooltip" 
                 x-transition.opacity.duration.150ms
                 class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg shadow-lg whitespace-nowrap pointer-events-none">
                <?php echo e($sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar'); ?>

                <span class="text-xs text-gray-400 ml-2">(Ctrl+B)</span>
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"></div>
            </div>
        </button>
    </div>
    
    
    <div class="flex-1 overflow-y-auto relative" wire:key="content-<?php echo e($selectedType); ?>-<?php echo e($selectedId); ?>">
        
        
        <div wire:loading.delay class="absolute inset-0 bg-gradient-to-br from-white/95 via-blue-50/90 to-white/95 dark:from-gray-900/95 dark:via-blue-900/50 dark:to-gray-900/95 backdrop-blur-md flex items-center justify-center z-50">
            <div class="text-center">
                <!-- Premium Spinner -->
                <div class="relative w-16 h-16 mx-auto mb-6">
                    <div class="absolute inset-0 rounded-full border-4 border-blue-100 dark:border-blue-900/30"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-600 dark:border-t-blue-400 border-r-blue-500 dark:border-r-blue-300 animate-spin"></div>
                </div>
                
                <!-- Loading Text -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Updating Content</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Please wait while we refresh your curriculum...</p>
                
                <!-- Pulse Dots -->
                <div class="flex justify-center gap-1.5 mt-4">
                    <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0s;"></div>
                    <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0.2s;"></div>
                    <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0.4s;"></div>
                </div>

                <!-- Skeleton Preview -->
                <div class="mt-6 space-y-3">
                    <div class="h-4 w-64 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
                    <div class="h-4 w-56 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
                    <div class="h-4 w-48 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
                </div>
            </div>
        </div>
        
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
            <div class="mx-8 mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-800 dark:text-green-200 font-medium"><?php echo e(session('message')); ?></p>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
            <div class="mx-8 mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-red-800 dark:text-red-200 font-medium"><?php echo e(session('error')); ?></p>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($courseId && $course): ?>
            <div class="mx-8 mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($course->title); ?></h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <?php echo e($mode === 'build' ? 'Build mode: add modules and lessons with a clear flow.' : 'Manage mode: approvals, collaborators, locks, and settings.'); ?>

                    </p>
                </div>
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-1">
                    <button wire:click="setMode('build')"
                            class="px-4 py-2 text-sm font-semibold rounded-md transition-colors
                                <?php echo e($mode === 'build' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'); ?>">
                        Build Course
                    </button>
                    <button wire:click="setMode('manage')"
                            class="px-4 py-2 text-sm font-semibold rounded-md transition-colors
                                <?php echo e($mode === 'manage' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'); ?>">
                        Manage Course
                    </button>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$courseId): ?>
            
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Curriculum Builder</h2>
                    <p class="text-gray-600 dark:text-gray-400">Select a course from the sidebar to start building</p>
                    

                </div>
            </div>
        <?php elseif($showForm && $selectedType === 'lesson'): ?>
            
            <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 md:p-8">
                <div class="max-w-5xl mx-auto">
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                <?php echo e($selectedId ? 'Edit Lesson' : 'Create New Lesson'); ?>

                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Build structured, engaging lessons for your students</p>
                        </div>
                        <button wire:click="closeForm" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$course): ?>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                            <p class="text-red-800 dark:text-red-200">Error: Course not loaded. Please select a course first.</p>
                        </div>
                    <?php else: ?>
                    <form wire:submit.prevent="saveLesson" class="space-y-6">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            
                            <div class="lg:col-span-2 space-y-6">
                                
                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Basic Information</h2>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Lesson Title <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model="formData.title" 
                                                   class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="e.g., Introduction to Variables">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Module <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="formData.module_id" 
                                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    <option value="">Select Module</option>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($module->id); ?>"><?php echo e($module->title); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </select>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.module_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Lesson Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model.live="formData.lesson_type" 
                                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    <option value="text">📝 Text Lesson</option>
                                                    <option value="video">🎥 Video Lesson</option>
                                                    <option value="interactive">💻 Interactive</option>
                                                    <option value="quiz">✅ Quiz</option>
                                                </select>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.lesson_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>

                                        
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Short Summary
                                            </label>
                                            <textarea wire:model="formData.summary" rows="2"
                                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                      placeholder="Brief description of this lesson (1-2 sentences)"></textarea>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This appears in lesson previews and search results</p>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Learning Objectives</h2>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            What should students know or be able to do after this lesson?
                                        </label>
                                        <textarea wire:model="formData.objectives" rows="5"
                                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                                  placeholder="• Understand the concept of variables&#10;• Create and assign values to variables&#10;• Use variables in simple programs&#10;• Explain the difference between variable types"></textarea>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use bullet points (•) or numbers to list objectives clearly</p>
                                    </div>
                                </div>

                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Lesson Content</h2>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Main lesson content and instructions
                                        </label>
                                        
                                        
                                        <div wire:ignore
                                             class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-700"
                                             x-data="setupTipTapEditor($wire.entangle('formData.content'))"
                                             x-init="$nextTick(() => init($refs.editor, '<?php echo e($courseId); ?>'))">
                                            <div x-show="loading" class="p-4 text-center text-gray-500">
                                                <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <p class="mt-2 text-sm">Loading editor...</p>
                                            </div>
                                            <div x-ref="editor" x-show="!loading && !error" class="min-h-[300px]"></div>
                                            <div x-show="error" class="p-4">
                                                <p class="text-sm text-amber-700 dark:text-amber-300 mb-2" x-text="error"></p>
                                                <textarea x-model="content" rows="12"
                                                          class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
                                            </div>
                                        </div>
                                        
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            ✨ Rich text editor with formatting, images, and code blocks. Auto-saves every 10 seconds.
                                        </p>
                                    </div>

                                    <div class="mt-6">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Upload PDF (optional)
                                        </label>
                                        
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($formData['attachments']) && count($formData['attachments']) > 0): ?>
                                            <div class="mb-4 space-y-2">
                                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Current Attachments:</p>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $formData['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($attachment['type']) && $attachment['type'] === 'pdf'): ?>
                                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2v-1z"/>
                                                                </svg>
                                                                <span class="text-sm text-gray-700 dark:text-gray-200"><?php echo e($attachment['name'] ?? 'PDF Document'); ?></span>
                                                            </div>
                                                            <button type="button" wire:click="removeAttachment(<?php echo e($index); ?>)"
                                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        
                                        
                                        <input type="file" accept="application/pdf" wire:model="pdfUpload"
                                               class="block w-full text-sm text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Attach a PDF to display to students inside the lesson viewer. Max 50 MB.
                                        </p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pdfUpload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pdfUpload): ?>
                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">Selected: <?php echo e($pdfUpload->getClientOriginalName()); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formData['lesson_type'] === 'video'): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Video Settings</h2>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Video URL <span class="text-red-500">*</span>
                                            </label>
                                            <input type="url" wire:model="formData.video_url" 
                                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="https://example.com/video.mp4 or Vimeo/YouTube URL">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paste direct video URL (MP4, Vimeo, YouTube, etc.)</p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.video_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Video Duration (minutes)
                                            </label>
                                            <input type="number" wire:model="formData.video_duration" 
                                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="e.g., 15">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Approximate length of the video</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formData['lesson_type'] === 'interactive'): ?>
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg shadow-sm border-2 border-purple-200 dark:border-purple-800 p-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Interactive Components</h2>
                                            <p class="text-sm text-purple-700 dark:text-purple-300">Add visual elements to make your lesson engaging</p>
                                        </div>
                                    </div>

                                    
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                1. Step-by-Step Instructions
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                💡 Break down your lesson into clear steps. Example: "Step 1: Open editor" → "Step 2: Create file"
                                            </p>
                                        </div>
                                        <textarea 
                                            wire:model="formData.lesson_steps_text" 
                                            rows="6"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                            placeholder="Step 1: Open your code editor&#10;Step 2: Create a new file&#10;Step 3: Write your code&#10;Step 4: Test your program"></textarea>
                                    </div>

                                    
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                2. Scratch Project Embed (Optional)
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                🎮 For Scratch lessons: Add the project ID from scratch.mit.edu (e.g., "1234567890")
                                            </p>
                                        </div>
                                        <input 
                                            type="text" 
                                            wire:model="formData.scratch_project_id"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            placeholder="e.g., 1234567890 (leave empty if not a Scratch lesson)">
                                    </div>

                                    
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                3. Code Examples (Optional)
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                💻 Add code snippets or blocks students will use. One per line.
                                            </p>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                                Examples:<br>
                                                • Scratch: "move (10) steps"<br>
                                                • Python: "print('Hello World')"<br>
                                                • HTML: "&lt;h1&gt;My Title&lt;/h1&gt;"
                                            </div>
                                        </div>
                                        <textarea 
                                            wire:model="formData.code_examples_text" 
                                            rows="5"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                            placeholder="print('Hello World')&#10;name = 'Student'&#10;if age > 18:&#10;    print('Adult')"></textarea>
                                    </div>

                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                                <strong>Preview:</strong> Students will see these components displayed beautifully when they view the lesson. All fields are optional - add only what you need!
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </div>

                            
                            <div class="space-y-6">
                                
                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Publishing</h3>
                                    
                                    <div class="space-y-4">
                                        
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Published</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Make visible to students</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_published" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Active</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Enable lesson access</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_active" class="sr-only peer" checked>
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Free Preview</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Allow non-enrolled users</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_free_preview" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Locked</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Require prerequisites</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_locked" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-gray-600 peer-focus:ring-2 peer-focus:ring-gray-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Settings</h3>
                                    
                                    <div class="space-y-4">
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Difficulty Level
                                            </label>
                                            <select wire:model="formData.difficulty_level" 
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="beginner">🟢 Beginner</option>
                                                <option value="intermediate">🟡 Intermediate</option>
                                                <option value="advanced">🔴 Advanced</option>
                                            </select>
                                        </div>

                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Duration (minutes)
                                            </label>
                                            <input type="number" wire:model="formData.duration_minutes" 
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="30">
                                        </div>

                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Order Position
                                            </label>
                                            <input type="number" wire:model="formData.order_index" 
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="1">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Position in module</p>
                                        </div>
                                    </div>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedId && isset($formData['approval_status'])): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Approval Status</h3>
                                    
                                    <div class="mb-4">
                                        <?php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            ];
                                            $status = $formData['approval_status'] ?? 'draft';
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo e($statusColors[$status]); ?>">
                                            <?php echo e(ucfirst($status)); ?>

                                        </span>
                                    </div>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasAnyRole(['admin', 'supervisor'])): ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status !== 'approved'): ?>
                                        <div class="space-y-2">
                                            <button type="button" wire:click="approveLesson" 
                                                    wire:loading.attr="disabled"
                                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                                <span wire:loading.remove wire:target="approveLesson">✓ Approve Lesson</span>
                                                <span wire:loading wire:target="approveLesson">Approving...</span>
                                            </button>
                                            <button type="button" wire:click="openRejectModal" 
                                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                                ✗ Reject Lesson
                                            </button>
                                        </div>
                                        <?php else: ?>
                                        <div class="space-y-2">
                                            <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                <p class="text-sm text-green-600 dark:text-green-400">
                                                    ✓ This lesson has been approved
                                                </p>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson && $lesson->approved_at): ?>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    Approved <?php echo e($lesson->approved_at->diffForHumans()); ?>

                                                </p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <button type="button" wire:click="openRejectModal" 
                                                    class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                                                ⚠️ Disapprove Lesson
                                            </button>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'rejected'): ?>
                                        <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Rejection Reason:</p>
                                            <p class="text-sm text-red-700 dark:text-red-300">
                                                <?php echo e($lesson->rejection_reason ?? 'No reason provided'); ?>

                                            </p>
                                        </div>
                                        <button type="button" wire:click="submitForApproval" 
                                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                                📤 Resubmit for Approval
                                        </button>
                                        <?php elseif($status === 'draft'): ?>
                                        <button type="button" wire:click="submitForApproval" 
                                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                                📤 Submit for Approval
                                        </button>
                                        <?php elseif($status === 'pending'): ?>
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                            ⏳ Waiting for approval from admin/supervisor
                                        </p>
                                        <?php elseif($status === 'approved'): ?>
                                        <p class="text-sm text-green-600 dark:text-green-400">
                                            ✓ This lesson has been approved
                                        </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <?php
                                        $currentStatus = $formData['approval_status'] ?? 'draft';
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedId && $currentStatus === 'approved' && !auth()->user()->hasAnyRole(['admin', 'supervisor'])): ?>
                                        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Re-approval Required</p>
                                                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                                        This lesson is currently approved. Updating it will send it back for re-approval by admin/supervisor.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <button type="submit" 
                                            class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        <?php echo e($selectedId ? 'Update Lesson' : 'Create Lesson'); ?>

                                    </button>
                                    <button type="button" wire:click="closeForm"
                                            class="w-full mt-3 px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                    
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showRejectModal ?? false): ?>
                    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeRejectModal">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6" wire:click.stop>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                <?php echo e($status === 'approved' ? 'Disapprove Lesson' : 'Reject Lesson'); ?>

                            </h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason for <?php echo e($status === 'approved' ? 'Disapproval' : 'Rejection'); ?> <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="rejectionReason" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                          placeholder="Explain why this lesson needs revision..."></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rejectionReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                                <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <p class="text-sm text-red-800 dark:text-red-200"><?php echo e(session('error')); ?></p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div class="flex gap-3">
                                <button type="button" 
                                        wire:click="disapproveLesson" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        x-data
                                        @click="if(!$wire.rejectionReason || $wire.rejectionReason.trim() === '') { alert('Please provide a reason for disapproval'); $event.stopPropagation(); }"
                                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                                    <span wire:loading.remove wire:target="disapproveLesson">
                                        <?php echo e($status === 'approved' ? '⚠️ Disapprove' : '✗ Reject'); ?>

                                    </span>
                                    <span wire:loading wire:target="disapproveLesson">Processing...</span>
                                </button>
                                <button type="button" wire:click="closeRejectModal" 
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php elseif($showForm && $selectedType === 'module'): ?>
            
            <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 md:p-8">
                <div class="max-w-3xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                <?php echo e($selectedId ? 'Edit Module' : 'Create New Module'); ?>

                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize lessons into structured modules</p>
                        </div>
                        <button wire:click="closeForm" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveModule" class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="space-y-5">
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Module Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="formData.title" 
                                           class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="e.g., Introduction to Programming">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Description
                                    </label>
                                    <textarea wire:model="formData.description" rows="4"
                                              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                              placeholder="Describe what students will learn in this module"></textarea>
                                </div>

                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Order Position
                                    </label>
                                    <input type="number" wire:model="formData.order_index" 
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="1">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Position in course structure</p>
                                </div>
                            </div>
                        </div>

                        
                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                <?php echo e($selectedId ? 'Update Module' : 'Create Module'); ?>

                            </button>
                            <button type="button" wire:click="closeForm"
                                    class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif($showForm && $selectedType === 'assessment'): ?>
            
            <div class="flex items-center justify-center h-full p-8">
                <div class="max-w-md w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                        <div class="text-center">
                            <div class="mx-auto w-20 h-20 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Assessment Builder</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                Create and manage quizzes, tests, and assignments using the dedicated assessment builder.
                            </p>
                            
                            <div class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedId): ?>
                                    
                                    <?php
                                        $assessment = \App\Models\Assessment::find($selectedId);
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assessment): ?>
                                        <button wire:click="toggleAssessmentLock(<?php echo e($selectedId); ?>)" 
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50 cursor-not-allowed"
                                                class="block w-full px-6 py-3 <?php echo e($assessment->is_locked ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'); ?> text-white font-semibold rounded-lg transition-colors shadow-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assessment->is_locked): ?>
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span wire:loading.remove wire:target="toggleAssessmentLock">🔒 Locked - Click to Unlock</span>
                                                <?php else: ?>
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                    </svg>
                                                    <span wire:loading.remove wire:target="toggleAssessmentLock">🔓 Unlocked - Click to Lock</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span wire:loading wire:target="toggleAssessmentLock">Processing...</span>
                                            </div>
                                        </button>
                                        
                                        <div class="text-sm text-gray-600 dark:text-gray-400 text-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assessment->is_locked): ?>
                                                ⚠️ Students cannot access this assessment
                                            <?php else: ?>
                                                ✅ Students can access this assessment
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <a href="<?php echo e(route('assessments.edit', $selectedId)); ?>" 
                                       class="block w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Assessment
                                        </div>
                                    </a>
                                <?php else: ?>
                                                <a href="<?php echo e(route('assessments.create', ['course_id' => $course->id, 'lesson_id' => $lessonId ?? null])); ?>" 
                                       class="block w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Create New Assessment
                                        </div>
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <button wire:click="closeForm" 
                                        class="block w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                    Back to Structure
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif($showForm): ?>
            
            <div class="p-8">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo e($selectedId ? 'Edit' : 'Create'); ?> <?php echo e(ucfirst($selectedType)); ?>

                        </h2>
                        <button wire:click="closeForm" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <p class="text-gray-600 dark:text-gray-400">Form for <?php echo e($selectedType); ?> will go here</p>
                    </div>
                </div>
            </div>
        <?php elseif($showForm): ?>
            
            <div class="p-8">
                <div class="bg-red-100 dark:bg-red-900/20 p-6 rounded-lg">
                    <h2 class="text-xl font-bold text-red-900 dark:text-red-100 mb-4">Debug: Form State</h2>
                    <p>showForm: <?php echo e($showForm ? 'TRUE' : 'FALSE'); ?></p>
                    <p>selectedType: <?php echo e($selectedType ?? 'NULL'); ?></p>
                    <p>selectedId: <?php echo e($selectedId ?? 'NULL'); ?></p>
                    <p>courseId: <?php echo e($courseId ?? 'NULL'); ?></p>
                    <p>course exists: <?php echo e($course ? 'YES' : 'NO'); ?></p>
                </div>
            </div>
        <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'manage'): ?>
                    
                    <div class="p-8">
                        <div class="max-w-4xl mx-auto">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Course Settings</h2>
                                <div class="flex flex-wrap items-center gap-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->trashed()): ?>
                                        <span class="text-sm px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                                            Archived <?php echo e(optional($course->deleted_at)->diffForHumans()); ?>

                                        </span>
                                        <?php $restoreBy = $course->deleted_at?->copy()->addDays($restoreWindowDays); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($restoreBy): ?>
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Restore by <?php echo e($restoreBy->format('M d, Y')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button wire:click="restoreCourse"
                                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow">
                                            Restore Course
                                        </button>
                                    <?php else: ?>
                                        <button wire:click="deleteCourse"
                                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow"
                                                title="Archive course (soft delete, restorable within <?php echo e($restoreWindowDays); ?> days)">
                                            Archive Course
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            
                            
                            <div class="grid grid-cols-3 gap-6 mb-8">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo e($course->modules->count()); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400">Modules (including archived)</div>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <div class="text-3xl font-bold text-green-600 mb-2"><?php echo e($course->modules->sum(fn($m) => $m->lessons->count())); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400">Lessons (including archived)</div>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo e($course->modules->flatMap(fn($m) => $m->lessons)->flatMap(fn($l) => $l->assessments)->count()); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400">Assessments</div>
                                </div>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin() || auth()->user()->isSupervisor()): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('course.manage-collaborators', ['course' => $course]);

$key = 'collaborators-'.$course->id;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2536782867-0', 'collaborators-'.$course->id);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor')) && $course->approval_status !== 'approved'): ?>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-lg shadow p-6 mb-6">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Course Approval</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        This course is currently in <span class="font-semibold"><?php echo e(ucfirst($course->approval_status)); ?></span> status.
                                    </p>
                                    <div class="flex gap-3">
                                        <button wire:click="approveCourse" 
                                                class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                            ✓ Approve Course
                                        </button>
                                        <button wire:click="rejectCourse" 
                                                class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">
                                            ✗ Reject Course
                                        </button>
                                    </div>
                                </div>
                            <?php elseif((auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor')) && $course->approval_status === 'approved'): ?>
                                <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg shadow p-6 mb-6">
                                    <h3 class="text-lg font-bold text-green-900 dark:text-green-100 mb-2">✓ Course Approved</h3>
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        This course has been approved and is published.
                                    </p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Content Lock Management
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Control student access to lessons, quizzes, and assignments. Hover over items in the sidebar to lock/unlock them.
                                </p>
                                
                                <?php
                                    $allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
                                    $lockedLessons = $allLessons->where('is_locked', true)->count();
                                    $unlockedLessons = $allLessons->where('is_locked', false)->count();
                                    
                                    $allAssessments = $allLessons->flatMap(fn($l) => $l->assessments);
                                    $lockedAssessments = $allAssessments->where('is_locked', true)->count();
                                    $unlockedAssessments = $allAssessments->where('is_locked', false)->count();
                                ?>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lessons</div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($lockedLessons); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                </svg>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($unlockedLessons); ?></span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                                    </div>
                                    
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quizzes</div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($lockedAssessments); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                </svg>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($unlockedAssessments); ?></span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Tip:</strong> Hover over any lesson or quiz in the left sidebar to see the lock/unlock button.
                                    </p>
                                </div>
                            </div>
                            
                            
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                                <div class="space-y-3">
                                    <button wire:click="setMode('build')" 
                                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-left">
                                        Switch to Build Mode
                                    </button>
                                    <a href="<?php echo e(route('courses.edit', $course->id)); ?>" wire:navigate
                                       class="block w-full px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
                                        Edit Course Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    
                    <div class="p-8">
                        <div class="max-w-3xl mx-auto bg-white dark:bg-gray-900 rounded-xl shadow p-8 border border-gray-200 dark:border-gray-800">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Build your course step by step</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Start with modules, then add lessons inside each module. Quizzes are optional and live inside lessons.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">1. Add a module</div>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1">Create the major sections of your course.</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">2. Add lessons</div>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1">Populate each module with lessons.</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">3. Add quizzes</div>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1">Optional checks inside lessons.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                
                <div class="p-8">
                    <div class="max-w-2xl mx-auto text-center">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-8">
                            <svg class="w-16 h-16 text-yellow-600 dark:text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Course Not Found</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                The course you're trying to access doesn't exist or you don't have permission to edit it.
                            </p>
                            <a href="<?php echo e(route('courses.index')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" wire:navigate>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Courses
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>


</div>
<?php /**PATH C:\wamp64\www\codecamp-system\resources\views/livewire/curriculum/new-builder.blade.php ENDPATH**/ ?>
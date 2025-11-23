<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Student Attendance — Today</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e(now()->format('l, F j, Y')); ?></p>
    </div>

    <div class="p-6">
        <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200"><?php echo e(session('message')); ?></p>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                    <input type="date" wire:model.live="attendanceDate" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Class Filter</label>
                    <select wire:model.live="classFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Classes</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $students->pluck('class_grade')->unique()->filter(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class); ?>"><?php echo e($class); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Filter</label>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" wire:model.live="search" placeholder="Search by name or ID..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-24">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $currentStatus = $attendance[$student->id] ?? 'present';
                    // Apply status filter
                    if ($statusFilter && $currentStatus !== $statusFilter) continue;
                ?>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all" 
                     wire:key="student-<?php echo e($student->id); ?>">
                    
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-lg">
                            <?php echo e(strtoupper(substr($student->full_name, 0, 1))); ?>

                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm"><?php echo e($student->full_name); ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($student->student_id); ?></p>
                            <!--[if BLOCK]><![endif]--><?php if($student->class_grade): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($student->class_grade); ?></p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" 
                                    wire:click="$set('attendance.<?php echo e($student->id); ?>', 'present')"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-all
                                        <?php echo e($currentStatus === 'present' 
                                            ? 'bg-green-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                                ✓ Present
                            </button>
                            
                            <button type="button" 
                                    wire:click="$set('attendance.<?php echo e($student->id); ?>', 'absent')"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-all
                                        <?php echo e($currentStatus === 'absent' 
                                            ? 'bg-red-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                                ✗ Absent
                            </button>
                        </div>

                        
                        <!--[if BLOCK]><![endif]--><?php if($currentStatus === 'present'): ?>
                            <div class="mt-3 space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                            </svg>
                                            Clock In
                                        </span>
                                    </label>
                                    <input type="time" 
                                           wire:model.live="clockIn.<?php echo e($student->id); ?>" 
                                           class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Clock Out
                                        </span>
                                    </label>
                                    <input type="time" 
                                           wire:model.live="clockOut.<?php echo e($student->id); ?>" 
                                           class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                                <?php
                                    $clockInTime = $clockIn[$student->id] ?? null;
                                    $clockOutTime = $clockOut[$student->id] ?? null;
                                    $totalHours = 0;
                                    if ($clockInTime && $clockOutTime) {
                                        try {
                                            $start = \Carbon\Carbon::parse($clockInTime);
                                            $end = \Carbon\Carbon::parse($clockOutTime);
                                            $totalHours = $start->diffInHours($end, true);
                                        } catch (\Exception $e) {
                                            $totalHours = 0;
                                        }
                                    }
                                ?>
                                <!--[if BLOCK]><![endif]--><?php if($totalHours > 0): ?>
                                    <?php
                                        $hours = floor($totalHours);
                                        $minutes = round(($totalHours - $hours) * 60);
                                    ?>
                                    <div class="text-xs text-center py-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-blue-700 dark:text-blue-300 rounded-lg font-semibold border border-blue-200 dark:border-blue-700">
                                        ⏱️ Total: <?php echo e($hours); ?>h <?php echo e($minutes); ?>m
                                    </div>
                                <?php elseif($clockInTime && $clockOutTime): ?>
                                    <div class="text-xs text-center py-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 rounded-lg">
                                        ⚠️ Check times
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        
                        <!--[if BLOCK]><![endif]--><?php if($currentStatus === 'absent'): ?>
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                <input type="text" 
                                       wire:model="reasons.<?php echo e($student->id); ?>" 
                                       placeholder="Enter reason for absence..." 
                                       class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500">
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Marked</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($totalMarked); ?>/<?php echo e($students->count()); ?></p>
                        </div>
                        
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    <?php echo e(collect($attendance)->filter(fn($s) => $s === 'present')->count()); ?> Present
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    <?php echo e(collect($attendance)->filter(fn($s) => $s === 'absent')->count()); ?> Absent
                                </span>
                            </div>
                            <?php
                                $totalHoursSum = 0;
                                foreach ($attendance as $studentId => $status) {
                                    if ($status === 'present') {
                                        $clockInTime = $clockIn[$studentId] ?? null;
                                        $clockOutTime = $clockOut[$studentId] ?? null;
                                        if ($clockInTime && $clockOutTime) {
                                            try {
                                                $start = \Carbon\Carbon::parse($clockInTime);
                                                $end = \Carbon\Carbon::parse($clockOutTime);
                                                $totalHoursSum += $start->diffInHours($end, true);
                                            } catch (\Exception $e) {}
                                        }
                                    }
                                }
                            ?>
                            <!--[if BLOCK]><![endif]--><?php if($totalHoursSum > 0): ?>
                                <div class="flex items-center">
                                    <span class="w-3 h-3 bg-blue-600 rounded-full mr-2"></span>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <?php echo e(number_format($totalHoursSum, 1)); ?> Total Hours
                                    </span>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    
                    <button wire:click="saveAttendance" 
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-lg hover:shadow-xl">
                        Submit Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/livewire/attendance/student-attendance.blade.php ENDPATH**/ ?>
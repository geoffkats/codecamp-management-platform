<div class="flex flex-col gap-6 p-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive insights and performance metrics</p>
        </div>
        <div class="flex gap-2">
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'timeRange','label' => 'Time Range']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'timeRange','label' => 'Time Range']); ?>
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
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

    <!--[if BLOCK]><![endif]--><?php if($role === 'teacher'): ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold"><?php echo e($stats['total_courses']); ?></p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Enrollments</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['total_enrollments'])); ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Active Students</p>
                <p class="text-3xl font-bold"><?php echo e($stats['active_students']); ?></p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Completion Rate</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['completion_rate'], 1)); ?>%</p>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-pink-100 text-sm mb-1">Avg. Score</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['average_score'], 1)); ?>%</p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Enrollment Trends</h3>
                <canvas id="enrollmentChart" height="200"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Course Performance</h3>
                <canvas id="coursePerformanceChart" height="200"></canvas>
            </div>
        </div>
    <?php elseif($role === 'admin'): ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Users</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['total_users'])); ?></p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['total_courses'])); ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Enrollments</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['total_enrollments'])); ?></p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Completion Rate</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['completion_rate'], 1)); ?>%</p>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-pink-100 text-sm mb-1">Active Students</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['active_students'])); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">User Growth</h3>
                <canvas id="userGrowthChart" height="200"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Course Performance</h3>
                <canvas id="coursePerformanceChart" height="200"></canvas>
            </div>
        </div>
    <?php else: ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold"><?php echo e($stats['total_courses']); ?></p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Completed</p>
                <p class="text-3xl font-bold"><?php echo e($stats['completed_courses']); ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Avg. Progress</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['average_progress'], 1)); ?>%</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Avg. Score</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['average_score'], 1)); ?>%</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Progress Trend</h3>
            <canvas id="progressTrendChart" height="200"></canvas>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <!--[if BLOCK]><![endif]--><?php if($role === 'teacher' && isset($enrollmentTrends)): ?>
        const enrollmentCtx = document.getElementById('enrollmentChart');
        if (enrollmentCtx) {
            new Chart(enrollmentCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($enrollmentTrends->pluck('date'), 15, 512) ?>,
                    datasets: [{
                        label: 'Enrollments',
                        data: <?php echo json_encode($enrollmentTrends->pluck('count'), 15, 512) ?>,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!--[if BLOCK]><![endif]--><?php if($role === 'student' && isset($progressTrend)): ?>
        const progressCtx = document.getElementById('progressTrendChart');
        if (progressCtx) {
            new Chart(progressCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($progressTrend->pluck('date'), 15, 512) ?>,
                    datasets: [{
                        label: 'Progress %',
                        data: <?php echo json_encode($progressTrend->pluck('progress'), 15, 512) ?>,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                        }
                    }
                }
            });
        }
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/livewire/analytics/dashboard.blade.php ENDPATH**/ ?>
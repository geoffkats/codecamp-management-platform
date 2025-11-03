<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive insights and performance metrics</p>
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="timeRange" label="Time Range">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
            </flux:select>
        </div>
    </div>

    @if($role === 'teacher')
        {{-- Teacher Analytics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold">{{ $stats['total_courses'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Enrollments</p>
                <p class="text-3xl font-bold">{{ number_format($stats['total_enrollments']) }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Active Students</p>
                <p class="text-3xl font-bold">{{ $stats['active_students'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Completion Rate</p>
                <p class="text-3xl font-bold">{{ number_format($stats['completion_rate'], 1) }}%</p>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-pink-100 text-sm mb-1">Avg. Score</p>
                <p class="text-3xl font-bold">{{ number_format($stats['average_score'], 1) }}%</p>
            </div>
        </div>

        {{-- Charts --}}
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
    @elseif($role === 'admin')
        {{-- Admin Analytics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Users</p>
                <p class="text-3xl font-bold">{{ number_format($stats['total_users']) }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold">{{ number_format($stats['total_courses']) }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Enrollments</p>
                <p class="text-3xl font-bold">{{ number_format($stats['total_enrollments']) }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Completion Rate</p>
                <p class="text-3xl font-bold">{{ number_format($stats['completion_rate'], 1) }}%</p>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-pink-100 text-sm mb-1">Active Students</p>
                <p class="text-3xl font-bold">{{ number_format($stats['active_students']) }}</p>
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
    @else
        {{-- Student Analytics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-blue-100 text-sm mb-1">Total Courses</p>
                <p class="text-3xl font-bold">{{ $stats['total_courses'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-green-100 text-sm mb-1">Completed</p>
                <p class="text-3xl font-bold">{{ $stats['completed_courses'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-purple-100 text-sm mb-1">Avg. Progress</p>
                <p class="text-3xl font-bold">{{ number_format($stats['average_progress'], 1) }}%</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-orange-100 text-sm mb-1">Avg. Score</p>
                <p class="text-3xl font-bold">{{ number_format($stats['average_score'], 1) }}%</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Progress Trend</h3>
            <canvas id="progressTrendChart" height="200"></canvas>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($role === 'teacher' && isset($enrollmentTrends))
        const enrollmentCtx = document.getElementById('enrollmentChart');
        if (enrollmentCtx) {
            new Chart(enrollmentCtx, {
                type: 'line',
                data: {
                    labels: @json($enrollmentTrends->pluck('date')),
                    datasets: [{
                        label: 'Enrollments',
                        data: @json($enrollmentTrends->pluck('count')),
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
    @endif

    @if($role === 'student' && isset($progressTrend))
        const progressCtx = document.getElementById('progressTrendChart');
        if (progressCtx) {
            new Chart(progressCtx, {
                type: 'line',
                data: {
                    labels: @json($progressTrend->pluck('date')),
                    datasets: [{
                        label: 'Progress %',
                        data: @json($progressTrend->pluck('progress')),
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
    @endif
});
</script>
@endpush

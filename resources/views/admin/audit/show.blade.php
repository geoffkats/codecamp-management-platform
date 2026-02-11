<x-layouts.app title="Audit Details">
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.audit.logs') }}" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Logs
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            @if($model)
                {{ $model->getDisplayName() ?? "Deleted $modelType" }}
            @else
                Deleted {{ $modelType }}
            @endif
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Complete change history and details</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Status -->
            @if($model)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Current Status</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Model Type:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $modelType }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Model ID:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $modelId }}</span>
                        </div>
                        @if($model->trashed())
                            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-red-900 dark:text-red-200">This item has been deleted</span>
                                </div>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Created:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $model->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $model->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Change History -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Change History</h2>

                @if($logs->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No changes recorded for this item</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($logs as $log)
                            <div class="border-l-4 pl-4 py-4 rounded-r-lg transition-all hover:shadow-sm
                                @if($log->action === 'create') border-green-500 dark:border-green-600 bg-green-50 dark:bg-green-900/20
                                @elseif($log->action === 'update') border-blue-500 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/20
                                @elseif($log->action === 'delete') border-red-500 dark:border-red-600 bg-red-50 dark:bg-red-900/20
                                @elseif($log->action === 'restore') border-yellow-500 dark:border-yellow-600 bg-yellow-50 dark:bg-yellow-900/20
                                @else border-gray-500 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/20
                                @endif
                            ">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            @if($log->action === 'create') bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200
                                            @elseif($log->action === 'update') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200
                                            @elseif($log->action === 'delete') bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200
                                            @elseif($log->action === 'restore') bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                            @endif
                                        ">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            by <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'System' }}</span>
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                @if($log->action === 'update' && $log->old_values && $log->new_values)
                                    <details class="cursor-pointer group">
                                        <summary class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 flex items-center gap-2 py-2">
                                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Show changes
                                        </summary>
                                        <div class="mt-3 space-y-2">
                                            @php
                                                $oldValues = json_decode($log->old_values, true) ?? [];
                                                $newValues = json_decode($log->new_values, true) ?? [];
                                            @endphp
                                            @foreach($newValues as $key => $newValue)
                                                @if(isset($oldValues[$key]) && $oldValues[$key] != $newValue)
                                                    <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-600">
                                                        <div class="font-semibold text-sm text-gray-800 dark:text-gray-200 mb-2">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                                        <div class="space-y-1 text-xs">
                                                            <div class="p-2 bg-red-50 dark:bg-red-900/20 border-l-2 border-red-400 dark:border-red-600 rounded">
                                                                <span class="font-medium text-red-700 dark:text-red-300">Old:</span>
                                                                <span class="text-red-600 dark:text-red-400 font-mono">{{ substr(json_encode($oldValues[$key]), 0, 150) }}</span>
                                                            </div>
                                                            <div class="p-2 bg-green-50 dark:bg-green-900/20 border-l-2 border-green-400 dark:border-green-600 rounded">
                                                                <span class="font-medium text-green-700 dark:text-green-300">New:</span>
                                                                <span class="text-green-600 dark:text-green-400 font-mono">{{ substr(json_encode($newValue), 0, 150) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </details>
                                @elseif($log->action === 'create')
                                    <details class="cursor-pointer group">
                                        <summary class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 flex items-center gap-2 py-2">
                                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Show initial values
                                        </summary>
                                        <div class="mt-3 p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-600 overflow-x-auto">
                                            <pre class="text-xs text-gray-800 dark:text-gray-200 font-mono">{{ json_encode(json_decode($log->new_values, true) ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </details>
                                @endif

                                @if($log->action === 'update')
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                        <button class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition-colors revert-btn"
                                            data-log-id="{{ $log->id }}"
                                            data-model-type="{{ $modelType }}"
                                            data-model-id="{{ $modelId }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            Revert to this version
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-4">
            @if($model && $model->trashed())
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg shadow-sm p-6 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-green-100 dark:bg-green-900/40 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Restore Item</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        This item has been deleted. Click the button below to restore it to active status.
                    </p>
                    <button class="w-full px-4 py-3 bg-green-600 dark:bg-green-700 text-white font-semibold rounded-lg hover:bg-green-700 dark:hover:bg-green-800 transition-all transform hover:scale-105 shadow-md restore-btn"
                        data-model-type="{{ $modelType }}"
                        data-model-id="{{ $modelId }}">
                        Restore Item
                    </button>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg shadow-sm p-6 border-2 border-red-300 dark:border-red-800">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-red-100 dark:bg-red-900/40 rounded-lg">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-200">Permanent Delete</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Permanently delete this item from the database. <strong class="text-red-600 dark:text-red-400">This cannot be undone.</strong>
                    </p>
                    <button class="w-full px-4 py-3 bg-red-600 dark:bg-red-700 text-white font-semibold rounded-lg hover:bg-red-700 dark:hover:bg-red-800 transition-all shadow-md force-delete-btn"
                        data-model-type="{{ $modelType }}"
                        data-model-id="{{ $modelId }}"
                        onclick="return confirm('Are you absolutely sure? This action cannot be undone and will permanently delete all data.')">
                        Permanently Delete
                    </button>
                </div>
            @elseif($model)
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg shadow-sm p-6 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Item Status</h3>
                    </div>
                    <p class="text-sm font-medium text-green-900 dark:text-green-200">This item is active and accessible</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Details
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <dt class="text-sm text-gray-600 dark:text-gray-400">Model Type:</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono">{{ $modelType }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <dt class="text-sm text-gray-600 dark:text-gray-400">Model ID:</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono">{{ $modelId }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <dt class="text-sm text-gray-600 dark:text-gray-400">Total Changes:</dt>
                        <dd class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $logs->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restore button
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to restore this item?')) return;

            const modelType = this.dataset.modelType;
            const modelId = this.dataset.modelId;

            try {
                const response = await fetch('{{ route("admin.audit.restore") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ model_type: modelType, model_id: modelId })
                });

                const data = await response.json();
                if (response.ok) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    // Revert button
    document.querySelectorAll('.revert-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to revert to this version? Current data will be lost.')) return;

            const logId = this.dataset.logId;
            const modelType = this.dataset.modelType;
            const modelId = this.dataset.modelId;

            try {
                const response = await fetch('{{ route("admin.audit.revert") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ log_id: logId, model_type: modelType, model_id: modelId })
                });

                const data = await response.json();
                if (response.ok) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    // Force delete button
    document.querySelectorAll('.force-delete-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const modelType = this.dataset.modelType;
            const modelId = this.dataset.modelId;

            try {
                const response = await fetch('{{ route("admin.audit.force-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ model_type: modelType, model_id: modelId })
                });

                const data = await response.json();
                if (response.ok) {
                    alert(data.message);
                    window.location.href = '{{ route("admin.audit.logs") }}';
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
});
</script>
</x-layouts.app>

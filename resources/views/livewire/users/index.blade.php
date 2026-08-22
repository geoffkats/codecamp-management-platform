@php
    $audienceLabel = match ($filterAudience) {
        'students' => 'Students',
        'ict_students' => 'ICT Students',
        default => 'Staff',
    };
    $showStudentFilters = in_array($filterAudience, ['students', 'ict_students']);
    $hasFilters = $search
        || $filterRole !== 'all'
        || $filterStatus !== 'all'
        || $sortBy !== 'recent'
        || ($showStudentFilters && ($filterProgram !== 'all' || $filterSchool !== 'all'));
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-white">User Management</h1>
                    <p class="mt-0.5 text-sm text-orange-100">Accounts, roles, and access</p>
                </div>
                <flux:button href="{{ route('admin.users.create') }}" icon="plus" size="sm"
                    class="!bg-blue-600 !text-white hover:!bg-blue-700" wire:navigate>
                    Add User
                </flux:button>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6">

        {{-- Flash --}}
        @if(session()->has('message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Audience tabs --}}
        <div class="flex flex-wrap gap-2">
            @foreach([
                'staff'        => ['label' => 'Staff',         'count' => $audienceCounts['staff'] ?? 0],
                'students'     => ['label' => 'Students',      'count' => $audienceCounts['students'] ?? 0],
                'ict_students' => ['label' => 'ICT Students',  'count' => $audienceCounts['ict_students'] ?? 0],
            ] as $key => $option)
                <button type="button" wire:click="$set('filterAudience', '{{ $key }}')"
                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-medium transition
                        {{ $filterAudience === $key
                            ? 'border-orange-600 bg-orange-600 text-white'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                    {{ $option['label'] }}
                    <span class="rounded px-1.5 py-0.5 text-xs font-bold
                        {{ $filterAudience === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400' }}">
                        {{ number_format($option['count']) }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total {{ $audienceLabel }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Active</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($stats['active']) }}</p>
                <p class="text-xs text-slate-400">{{ number_format(($stats['active'] / max($stats['total'], 1)) * 100, 0) }}% of total</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Inactive</p>
                <p class="mt-1 text-2xl font-bold text-slate-600 dark:text-slate-300">{{ number_format($stats['inactive']) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <flux:input wire:model.live.debounce.300ms="search"
                        placeholder="{{ $showStudentFilters ? 'Search name, email, or student ID…' : 'Search name or email…' }}" />
                </div>
                @if($showStudentFilters)
                    <div class="w-full sm:w-44">
                        <flux:select wire:model.live="filterProgram" label="Program">
                            <option value="all">All programs</option>
                            @if($filterAudience !== 'ict_students')
                                <option value="codecamp">Code Camp</option>
                                <option value="codeclub">Code Club</option>
                            @endif
                            <option value="ict">ICT</option>
                        </flux:select>
                    </div>
                    <div class="w-full sm:w-48">
                        <flux:select wire:model.live="filterSchool" label="School">
                            <option value="all">All schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
                <div class="w-full sm:w-40">
                    <flux:select wire:model.live="filterRole" label="Role">
                        <option value="all">All roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="w-full sm:w-36">
                    <flux:select wire:model.live="filterStatus" label="Status">
                        <option value="all">All status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </flux:select>
                </div>
                <div class="w-full sm:w-40">
                    <flux:select wire:model.live="sortBy" label="Sort by">
                        <option value="recent">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="name">Name A–Z</option>
                        <option value="email">Email A–Z</option>
                    </flux:select>
                </div>
                @if($hasFilters)
                    <button type="button" wire:click="clearFilters"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:text-slate-400 dark:hover:bg-zinc-800">
                        Clear filters
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        @if($users->count() > 0)
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $audienceLabel }}</h2>
                    <span class="text-xs text-slate-500">{{ $users->total() }} results · page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Contact</th>
                                @if($showStudentFilters)
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Program / School</th>
                                @endif
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Last login</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach($users as $user)
                                @php
                                    $roleStyles = [
                                        'admin'           => 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',
                                        'teacher'         => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-300',
                                        'codecamp_trainer'=> 'bg-orange-50 text-orange-700 dark:bg-orange-950/30 dark:text-orange-300',
                                        'ict_teacher'     => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/30 dark:text-cyan-300',
                                        'student'         => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
                                        'supervisor'      => 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300',
                                    ];
                                @endphp
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('admin.users.show', $user) }}" wire:navigate class="flex items-center gap-2.5 group">
                                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-orange-600 text-xs font-bold text-white">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-slate-900 group-hover:text-orange-600 dark:text-white dark:group-hover:text-orange-400">
                                                    {{ $user->name }}
                                                    @if($user->id === auth()->id())
                                                        <span class="text-xs font-normal text-slate-400">(you)</span>
                                                    @endif
                                                </p>
                                                @if($user->student_id && $showStudentFilters)
                                                    <p class="text-xs text-slate-400">{{ $user->student_id }}</p>
                                                @endif
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <p class="truncate text-slate-600 dark:text-slate-400">{{ $user->email ?: '—' }}</p>
                                        @if($user->email_verified_at)
                                            <p class="text-xs text-emerald-600">Verified</p>
                                        @endif
                                    </td>
                                    @if($showStudentFilters)
                                        <td class="px-4 py-2.5">
                                            @php
                                                $program = $user->studentProfile?->program_type ?? $user->student_type ?? '—';
                                            @endphp
                                            <p class="text-slate-700 dark:text-slate-300">{{ ucfirst($program) }}</p>
                                            <p class="text-xs text-slate-400">{{ $user->studentProfile?->school?->name ?? 'No school' }}</p>
                                        </td>
                                    @endif
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($user->roles as $role)
                                                <span class="rounded px-2 py-0.5 text-xs font-medium {{ $roleStyles[strtolower($role->name)] ?? 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-400">No role</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-500">
                                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.users.show', $user) }}" wire:navigate
                                               class="rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800">
                                                View
                                            </a>
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                                <flux:menu>
                                                    <flux:menu.item href="{{ route('admin.users.edit', $user) }}" icon="pencil-square" wire:navigate>
                                                        Edit
                                                    </flux:menu.item>
                                                    <flux:menu.item wire:click="openResetModal({{ $user->id }})" icon="key">
                                                        Reset password
                                                    </flux:menu.item>
                                                    <flux:menu.item wire:click="toggleStatus({{ $user->id }})" icon="{{ $user->is_active ? 'x-circle' : 'check-circle' }}">
                                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                    </flux:menu.item>
                                                    @can('delete', $user)
                                                        <flux:menu.separator />
                                                        <flux:menu.item wire:click="deleteUser({{ $user->id }})" icon="trash" variant="danger"
                                                            wire:confirm="Delete {{ $user->name }}? Their login account and related enrollments will be removed. This cannot be undone.">
                                                            Delete
                                                        </flux:menu.item>
                                                    @endcan
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $users->links() }}</div>
        @else
            <div class="rounded-lg border border-slate-200 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm font-medium text-slate-900 dark:text-white">No users found</p>
                <p class="mt-1 text-xs text-slate-500">Try a different search or clear your filters.</p>
                @if($hasFilters)
                    <button type="button" wire:click="clearFilters"
                        class="mt-4 rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                        Clear filters
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Password reset modal --}}
    @if($showResetModal)
        <flux:modal name="reset-password" :show="$showResetModal" wire:model="showResetModal">
            <form wire:submit.prevent="confirmResetPassword">
                <div class="p-6">
                    @if(!$newPassword)
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Reset password</h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            Generate a new password for <strong>{{ $selectedUser->name ?? '' }}</strong>
                            ({{ $selectedUser->email ?? $selectedUser->student_id ?? '' }})?
                        </p>
                        <p class="mt-1 text-xs text-slate-500">The new password will be shown once — copy it before closing.</p>
                        <div class="mt-6 flex justify-end gap-2">
                            <flux:button type="button" wire:click="closeResetModal" variant="ghost">Cancel</flux:button>
                            <flux:button type="submit" variant="primary">Reset password</flux:button>
                        </div>
                    @else
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Password updated</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Share this with {{ $selectedUser->name ?? 'the user' }} securely.</p>
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <label class="mb-2 block text-xs font-medium text-slate-500">New password</label>
                            <div class="flex gap-2">
                                <input type="text" value="{{ $newPassword }}" id="new-password-field" readonly
                                    class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 font-mono text-sm font-bold dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                <button type="button" id="copy-btn-index" onclick="copyPasswordIndex(this)"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    Copy
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">This will not be shown again after you close this dialog.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <flux:button type="button" wire:click="closeResetModal" variant="primary">Done</flux:button>
                        </div>
                        <script>
                            function copyPasswordIndex(btn) {
                                const field = document.getElementById('new-password-field');
                                if (!field) return;
                                field.select();
                                field.setSelectionRange(0, 99999);
                                const done = () => {
                                    btn.textContent = 'Copied!';
                                    btn.classList.replace('bg-blue-600', 'bg-emerald-600');
                                    btn.classList.replace('hover:bg-blue-700', 'hover:bg-emerald-700');
                                    setTimeout(() => {
                                        btn.textContent = 'Copy';
                                        btn.classList.replace('bg-emerald-600', 'bg-blue-600');
                                        btn.classList.replace('hover:bg-emerald-700', 'hover:bg-blue-700');
                                    }, 2000);
                                };
                                navigator.clipboard?.writeText(field.value).then(done).catch(() => { document.execCommand('copy'); done(); });
                            }
                        </script>
                    @endif
                </div>
            </form>
        </flux:modal>
    @endif
</div>

@props(['user'])

@php
    $badges = [
        'pending_approvals' => $pendingApprovalCount ?? 0,
        'pending_feedback' => $pendingFeedbackCount ?? 0,
    ];
@endphp

<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Menu')">
        <flux:navlist.item
            icon="home"
            :href="route('dashboard')"
            :current="request()->routeIs('dashboard')"
            wire:navigate
        >
            {{ __('Dashboard') }}
        </flux:navlist.item>
    </flux:navlist.group>

    @if($showStudentSection && $user->studentProfile)
        <flux:navlist.group :heading="$showIctStudentSection ? __('Learning') : ($showCodeClubStudentSection ? __('Code Club') : __('My Learning'))">
            @foreach(($showIctStudentSection ? $ictStudentNav : ($showCodeClubStudentSection ? $codeclubStudentNav : $codecampStudentNav)) as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @endif

    @if($showIctTeacherSection)
        <flux:navlist.group :heading="__('ICT Teaching')">
            @foreach($ictTeacherNav as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @elseif($showCodeClubFacilitatorSection)
        <flux:navlist.group :heading="__('Code Club')">
            @foreach($codeclubFacilitatorNav as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @elseif($showCodecampTeacherSection)
        <flux:navlist.group :heading="__('Teaching')">
            @foreach($codecampTeacherNav as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @endif

    @if($isAdmin)
        @if(count($adminPrimaryNav) > 0)
            <flux:navlist.group :heading="__('People & Camps')">
                @foreach($adminPrimaryNav as $item)
                    <x-navigation.nav-item :item="$item" :badges="$badges" />
                @endforeach
            </flux:navlist.group>
        @endif

        @if(count($adminProgramsNav) > 0)
            <flux:navlist.group :heading="__('Programs')">
                @foreach($adminProgramsNav as $item)
                    <x-navigation.nav-item :item="$item" :badges="$badges" />
                @endforeach
            </flux:navlist.group>
        @endif

        @if(count($adminMoreNav) > 0)
            <details class="group/nav px-2">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-2 py-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    {{ __('More tools') }}
                    <svg class="h-4 w-4 transition group-open/nav:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <flux:navlist variant="outline" class="mt-1">
                    @foreach($adminMoreNav as $item)
                        <x-navigation.nav-item :item="$item" :badges="$badges" />
                    @endforeach
                </flux:navlist>
            </details>
        @endif
    @endif

    @if($user->hasRole('operations_manager') && !$isAdmin && count($operationsManagerNav) > 0)
        <flux:navlist.group :heading="__('Operations')">
            @foreach($operationsManagerNav as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @endif

    @if($showSupervisorSection && count($supervisorNav) > 0)
        <flux:navlist.group :heading="__('Supervision')">
            @foreach($supervisorNav as $item)
                <x-navigation.nav-item :item="$item" :badges="$badges" />
            @endforeach
        </flux:navlist.group>
    @endif

    <flux:navlist.group :heading="__('Account')">
        <flux:navlist.item
            icon="bell"
            :href="route('notifications.index')"
            :current="request()->routeIs('notifications.*')"
            wire:navigate
        >
            {{ __('Notifications') }}
            @if(($unreadNotificationsCount ?? 0) > 0)
                <flux:badge size="sm" variant="primary">{{ $unreadNotificationsCount }}</flux:badge>
            @endif
        </flux:navlist.item>

        <flux:navlist.item
            icon="cog-6-tooth"
            :href="route('profile.edit')"
            :current="request()->routeIs('profile.edit')"
            wire:navigate
        >
            {{ __('Settings') }}
        </flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>

<?php

use Illuminate\Support\Facades\Route;

dataset('staff sidebars', [
    'admin' => ['admin', 'admin'],
    'supervisor' => ['supervisor', 'supervisor'],
    'codecamp trainer' => ['codecamp_trainer', 'codecamp_teacher'],
    'operations manager' => ['operations_manager', 'operations_manager'],
]);

it('opens every sidebar link without a 403', function (string $role, string $navKey) {
    $user = userWithRole($role);

    $forbidden = collect(config("navigation.$navKey"))
        ->flatMap(fn ($section) => $section['items'])
        ->reject(fn ($item) => isset($item['feature']) && ! config('features.'.$item['feature']))
        ->reject(fn ($item) => isset($item['roles']) && ! $user->hasAnyRole($item['roles']))
        ->filter(fn ($item) => Route::has($item['route']))
        ->filter(fn ($item) => $this->actingAs($user)->get(route($item['route']))->status() === 403)
        ->pluck('label')
        ->values()
        ->all();

    expect($forbidden)->toBe([]);
})->with('staff sidebars');

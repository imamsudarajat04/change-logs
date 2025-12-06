<?php

use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Tests\Models\User;

test('model with trait has change logs relationship', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    expect($user->changeLogs())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

test('model can access change logs', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    // Should have 1 CREATE log
    expect($user->changeLogs)->toHaveCount(1)
        ->and($user->changeLogs->first()->action)->toBe('CREATE');
});

test('model has change logs returns correct boolean', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    expect($user->hasChangeLogs())->toBeTrue();
});

test('model can get last change log', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->update(['name' => 'Jane Doe']);

    $lastLog = $user->lastChangeLog();

    expect($lastLog)->toBeInstanceOf(ChangeLog::class)
        ->and($lastLog->action)->toBe('UPDATE');
});
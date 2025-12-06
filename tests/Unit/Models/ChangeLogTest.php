<?php

use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\tests\Models\User;

test('change log has correct fillable attributes', function () {
    $fillable = (new ChangeLog())->getFillable();

    expect($fillable)->toContain('loggable_type')
        ->and($fillable)->toContain('action')
        ->and($fillable)->toContain('old_value')
        ->and($fillable)->toContain('new_value');
});

test('change log casts attributes correctly', function () {
    $log = new ChangeLog();
    $casts = $log->getCasts();

    expect($casts['old_value'])->toBe('array')
        ->and($casts['new_value'])->toBe('array')
        ->and($casts['tags'])->toBe('array');
});

test('change log belongs to user', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $log = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => $user->id,
        'action' => 'CREATE',
        'user_id' => $user->id,
    ]);

    expect($log->user)->toBeInstanceOf(User::class)
        ->and($log->user->id)->toBe($user->id);
});

test('change log has loggable polymorphic relation', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $log = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => $user->id,
        'action' => 'CREATE',
    ]);

    expect($log->loggable)->toBeInstanceOf(User::class)
        ->and($log->loggable->id)->toBe($user->id);
});
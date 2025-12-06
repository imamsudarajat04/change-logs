<?php

use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Tests\Models\User;

test('can query logs by date', function () {
    // Create logs on different dates
    $today = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => '1',
        'action' => 'CREATE',
        'date' => today(),
    ]);

    $yesterday = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => '2',
        'action' => 'CREATE',
        'date' => today()->subDay(),
    ]);

    $lastWeek = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => '3',
        'action' => 'CREATE',
        'date' => today()->subWeek(),
    ]);

    // Test scopes
    expect(ChangeLog::query()->today()->count())->toBe(1)
        ->and(ChangeLog::query()->yesterday()->count())->toBe(1)
        ->and(ChangeLog::query()->withinDays(7)->count())->toBe(3)
        ->and(ChangeLog::query()->olderThanDays(2)->count())->toBe(1);
});

test('date is auto-set when creating log', function () {
    $log = ChangeLog::query()->create([
        'loggable_type' => User::class,
        'loggable_id' => '999',
        'action' => 'CREATE',
        // date not provided, should auto-set
    ]);

    expect($log->date)->not->toBeNull()
        ->and($log->date->toDateString())->toBe(today()->toDateString());
});
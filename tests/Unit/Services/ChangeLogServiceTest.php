<?php

use Imamsudarajat04\ChangeLogs\Services\ChangeLogService;
use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Tests\Models\User;

test('service can log create action', function () {
    $service = new ChangeLogService();
    $user = new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);
    $user->save();

    $service->logCreate($user);

    expect(ChangeLog::query()->count())->toBeGreaterThan(0);
});

test('service can query logs with filters', function () {
    $service = new ChangeLogService();

    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $logs = $service->query(['action' => 'CREATE'])->get();

    expect($logs)->toHaveCount(1)
        ->and($logs->first()->action)->toBe('CREATE');
});

test('service can get statistics', function () {
    $service = new ChangeLogService();

    User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $stats = $service->getStatistics();

    expect($stats)->toHaveKeys(['total', 'by_action', 'by_user', 'recent'])
        ->and($stats['total'])->toBeGreaterThan(0);
});

test('service excludes hidden fields', function () {
    $service = new ChangeLogService();

    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $log = ChangeLog::query()->where('loggable_id', $user->id)->first();

    expect($log->new_value)->not->toHaveKey('password');
});
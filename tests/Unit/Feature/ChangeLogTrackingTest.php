<?php

use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Tests\Models\User;
use Imamsudarajat04\ChangeLogs\Tests\Models\Post;

test('creating model logs change', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    expect(ChangeLog::query()->count())->toBe(1);

    $log = ChangeLog::query()->first();
    expect($log->action)->toBe('CREATE')
        ->and($log->loggable_type)->toBe(User::class)
        ->and($log->loggable_id)->toBe($user->id);
});

test('updating model logs changes', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->update(['name' => 'Jane Doe']);

    $updateLog = ChangeLog::query()->where('action', 'UPDATE')->first();

    expect($updateLog)->not->toBeNull()
        ->and($updateLog->old_value)->toHaveKey('name')
        ->and($updateLog->old_value['name'])->toBe('John Doe')
        ->and($updateLog->new_value['name'])->toBe('Jane Doe');
});

test('deleting model logs change', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $userId = $user->id;
    $user->delete();

    $deleteLog = ChangeLog::query()->where('action', 'DELETE')
        ->where('loggable_id', $userId)
        ->first();

    expect($deleteLog)->not->toBeNull()
        ->and($deleteLog->old_value)->toHaveKey('name')
        ->and($deleteLog->old_value['name'])->toBe('John Doe');
});

test('soft delete and restore logs correctly', function () {
    $post = Post::query()->create([
        'title' => 'Test Post',
        'content' => 'Content here',
        'status' => 'published',
    ]);

    $postId = $post->id;

    // Soft delete
    $post->delete();

    $deleteLog = ChangeLog::query()->where('action', 'DELETE')
        ->where('loggable_id', $postId)
        ->first();

    expect($deleteLog)->not->toBeNull();

    // Restore
    $post->restore();

    $restoreLog = ChangeLog::query()->where('action', 'RESTORE')
        ->where('loggable_id', $postId)
        ->first();

    expect($restoreLog)->not->toBeNull();
});

test('timestamps are excluded by default', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $log = ChangeLog::query()->where('loggable_id', $user->id)->first();

    expect($log->new_value)->not->toHaveKey('created_at')
        ->and($log->new_value)->not->toHaveKey('updated_at');
});

test('multiple updates create separate logs', function () {
    $user = User::query()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->update(['name' => 'Jane Doe']);
    $user->update(['email' => 'jane@example.com']);

    $updateLogs = ChangeLog::query()->where('action', 'UPDATE')
        ->where('loggable_id', $user->id)
        ->get();

    expect($updateLogs)->toHaveCount(2);
});
<?php

declare(strict_types=1);

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('audit log cannot be updated via model', function (): void {
    $log = AuditLog::create([
        'action' => 'test_action',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    expect(fn () => $log->update(['action' => 'tampered']))
        ->toThrow(LogicException::class, 'immutable');
});

it('audit log cannot be deleted via model', function (): void {
    $log = AuditLog::create([
        'action' => 'test_action',
        'ip_address' => '127.0.0.1',
    ]);

    expect(fn () => $log->delete())
        ->toThrow(LogicException::class, 'immutable');
});

it('audit log has no updated_at column', function (): void {
    expect(AuditLog::UPDATED_AT)->toBeNull();
});

it('audit log can be created with all required fields', function (): void {
    $log = AuditLog::create([
        'action' => 'login',
        'object_type' => 'User',
        'object_id' => 1,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'before_value' => null,
        'after_value' => ['status' => 'active'],
    ]);

    expect($log->id)->toBeInt()
        ->and($log->action)->toBe('login')
        ->and($log->created_at)->not->toBeNull();
});

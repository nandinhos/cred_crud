<?php

namespace Tests\Unit;

use App\Enums\BadgeColor;
use Carbon\Carbon;

test('forType retorna cor correta para CRED', function () {
    expect(BadgeColor::forType('CRED'))->toBe('info');
});

test('forType retorna cor correta para TCMS', function () {
    expect(BadgeColor::forType('TCMS'))->toBe('warning');
});

test('forType retorna gray para tipo desconhecido', function () {
    expect(BadgeColor::forType('UNKNOWN'))->toBe('gray');
});

test('forSecrecy retorna cor correta para Reservado', function () {
    expect(BadgeColor::forSecrecy('R'))->toBe('success');
});

test('forSecrecy retorna cor correta para Secreto', function () {
    expect(BadgeColor::forSecrecy('S'))->toBe('danger');
});

test('forSecrecy retorna gray para sigilo desconhecido', function () {
    expect(BadgeColor::forSecrecy('X'))->toBe('gray');
});

test('forRole retorna cor correta para super_admin', function () {
    expect(BadgeColor::forRole('super_admin'))->toBe('danger');
});

test('forRole retorna cor correta para admin', function () {
    expect(BadgeColor::forRole('admin'))->toBe('warning');
});

test('forRole retorna cor correta para operador', function () {
    expect(BadgeColor::forRole('operador'))->toBe('success');
});

test('forRole retorna cor correta para consulta', function () {
    expect(BadgeColor::forRole('consulta'))->toBe('primary');
});

test('forRole retorna gray para role desconhecido', function () {
    expect(BadgeColor::forRole('unknown_role'))->toBe('gray');
});

test('forValidity retorna gray quando validade é null', function () {
    expect(BadgeColor::forValidity(null))->toBe('gray');
});

test('forValidity retorna danger quando data está vencida', function () {
    $pastDate = Carbon::now()->subDays(10);
    expect(BadgeColor::forValidity($pastDate))->toBe('danger');
});

test('forValidity retorna warning quando faltam 30 dias ou menos', function () {
    $soonDate = Carbon::now()->addDays(15);
    expect(BadgeColor::forValidity($soonDate))->toBe('warning');
});

test('forValidity retorna warning quando falta exatamente 30 dias', function () {
    $exactDate = Carbon::now()->addDays(30);
    expect(BadgeColor::forValidity($exactDate))->toBe('warning');
});

test('forValidity retorna success quando faltam mais de 30 dias', function () {
    $futureDate = Carbon::now()->addDays(60);
    expect(BadgeColor::forValidity($futureDate))->toBe('success');
});

test('enum possui todos os cases necessários', function () {
    $cases = BadgeColor::cases();
    $values = array_map(fn ($case) => $case->value, $cases);
    
    expect($values)->toContain('danger');
    expect($values)->toContain('warning');
    expect($values)->toContain('success');
    expect($values)->toContain('info');
    expect($values)->toContain('primary');
    expect($values)->toContain('gray');
});

test('enum tem exatamente 6 cases', function () {
    expect(BadgeColor::cases())->toHaveCount(6);
});

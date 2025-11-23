<?php

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;

it('has correct values', function () {
    expect(CredentialSecrecy::ACESSO_RESTRITO->value)->toBe('AR');
    expect(CredentialSecrecy::RESERVADO->value)->toBe('R');
    expect(CredentialSecrecy::SECRETO->value)->toBe('S');
});

it('returns correct label for acesso restrito', function () {
    expect(CredentialSecrecy::ACESSO_RESTRITO->label())->toBe('Acesso Restrito');
});

it('returns correct label for reservado', function () {
    expect(CredentialSecrecy::RESERVADO->label())->toBe('Reservado');
});

it('returns correct label for secreto', function () {
    expect(CredentialSecrecy::SECRETO->label())->toBe('Secreto');
});

it('returns correct color for acesso restrito', function () {
    expect(CredentialSecrecy::ACESSO_RESTRITO->color())->toBe('info');
});

it('returns correct color for reservado', function () {
    expect(CredentialSecrecy::RESERVADO->color())->toBe('success');
});

it('returns correct color for secreto', function () {
    expect(CredentialSecrecy::SECRETO->color())->toBe('danger');
});

it('returns all options as array', function () {
    $options = CredentialSecrecy::options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(3);
    expect($options['AR'])->toBe('Acesso Restrito');
    expect($options['R'])->toBe('Reservado');
    expect($options['S'])->toBe('Secreto');
});

it('returns options for CRED type', function () {
    $options = CredentialSecrecy::optionsForType(CredentialType::CRED);

    expect($options)->toBeArray();
    expect($options)->toHaveCount(2);
    expect($options['R'])->toBe('Reservado');
    expect($options['S'])->toBe('Secreto');
    expect($options)->not->toHaveKey('AR');
});

it('returns options for TCMS type', function () {
    $options = CredentialSecrecy::optionsForType(CredentialType::TCMS);

    expect($options)->toBeArray();
    expect($options)->toHaveCount(1);
    expect($options['AR'])->toBe('Acesso Restrito');
    expect($options)->not->toHaveKey('R');
    expect($options)->not->toHaveKey('S');
});

it('validates reservado is valid for CRED', function () {
    expect(CredentialSecrecy::isValidForType('R', CredentialType::CRED))->toBeTrue();
});

it('validates secreto is valid for CRED', function () {
    expect(CredentialSecrecy::isValidForType('S', CredentialType::CRED))->toBeTrue();
});

it('validates acesso restrito is not valid for CRED', function () {
    expect(CredentialSecrecy::isValidForType('AR', CredentialType::CRED))->toBeFalse();
});

it('validates acesso restrito is valid for TCMS', function () {
    expect(CredentialSecrecy::isValidForType('AR', CredentialType::TCMS))->toBeTrue();
});

it('validates reservado is not valid for TCMS', function () {
    expect(CredentialSecrecy::isValidForType('R', CredentialType::TCMS))->toBeFalse();
});

it('validates secreto is not valid for TCMS', function () {
    expect(CredentialSecrecy::isValidForType('S', CredentialType::TCMS))->toBeFalse();
});

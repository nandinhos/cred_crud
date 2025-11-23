<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;
use Illuminate\Http\Request;

it('redirects authenticated user to home', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $request = Request::create('/login', 'GET');
    $middleware = new RedirectIfAuthenticated;

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toContain('/credentials');
});

it('allows guest to continue', function () {
    $request = Request::create('/login', 'GET');
    $middleware = new RedirectIfAuthenticated;

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

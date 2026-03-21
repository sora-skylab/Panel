<?php

namespace Pterodactyl\Tests\Integration\Http\Controllers\Auth;

use Pterodactyl\Tests\Integration\Http\HttpTestCase;

class AuthenticationCaptchaControllerTest extends HttpTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('recaptcha.provider', 'turnstile');
    }

    public function testLoginEndpointRequiresCaptchaWhenProviderEnabled(): void
    {
        $this->postJson('/auth/login', [
            'user' => 'demo@example.com',
            'password' => 'Password123!',
        ])
            ->assertBadRequest()
            ->assertJsonPath('errors.0.detail', 'Failed to validate captcha data.');
    }

    public function testForgotPasswordEndpointRequiresCaptchaWhenProviderEnabled(): void
    {
        $this->postJson(route('auth.post.forgot-password'), [
            'email' => 'demo@example.com',
        ])
            ->assertBadRequest()
            ->assertJsonPath('errors.0.detail', 'Failed to validate captcha data.');
    }

    public function testResetPasswordEndpointRequiresCaptchaWhenProviderEnabled(): void
    {
        $this->postJson(route('auth.reset-password'), [
            'email' => 'demo@example.com',
            'token' => 'reset-token',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertBadRequest()
            ->assertJsonPath('errors.0.detail', 'Failed to validate captcha data.');
    }
}

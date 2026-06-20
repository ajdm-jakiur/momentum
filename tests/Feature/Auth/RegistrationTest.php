<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_register_route_redirects_to_login(): void
    {
        $response = $this->get('/register');
        $response->assertRedirect('/login');
    }

    public function test_registration_screen_renders_with_valid_referral_code(): void
    {
        $referrer = User::factory()->create([
            'referral_code' => 'TESTCODE123',
        ]);

        $response = $this->get('/register/TESTCODE123');
        $response->assertOk()->assertSeeVolt('pages.auth.register');
    }

    public function test_invalid_referral_code_redirects_to_login(): void
    {
        $response = $this->get('/register/INVALIDCODE');
        $response->assertRedirect('/login');
    }

    public function test_new_users_can_register_with_valid_referral(): void
    {
        $referrer = User::factory()->create([
            'referral_code' => 'TESTREF456',
        ]);

        $component = Volt::test('pages.auth.register', ['code' => 'TESTREF456'])
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $newUser = User::where('email', 'test@example.com')->first();
        $this->assertEquals($referrer->id, $newUser->referred_by);
    }
}

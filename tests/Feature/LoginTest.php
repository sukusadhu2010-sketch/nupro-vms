<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_guest_can_see_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_valid_login_redirects_to_dashboard(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    public function test_logout_clears_session(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

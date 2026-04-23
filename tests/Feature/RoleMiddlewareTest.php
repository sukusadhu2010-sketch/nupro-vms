<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_role_middleware_allows_correct_role(): void
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::factory()->create(['name' => 'admin']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_role_middleware_denies_incorrect_role(): void
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::factory()->create(['name' => 'vendor']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_role_middleware_denies_guest(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }
}

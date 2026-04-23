<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_role_relationships(): void
    {
        $role = \App\Models\Role::factory()->create();
        $permission = \App\Models\Permission::factory()->create();
        $user = \App\Models\User::factory()->create();
        
        $role->permissions()->attach($permission);
        $role->users()->attach($user);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $role->permissions);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $role->users);
        $this->assertTrue($role->permissions->contains($permission));
        $this->assertTrue($role->users->contains($user));
    }
}

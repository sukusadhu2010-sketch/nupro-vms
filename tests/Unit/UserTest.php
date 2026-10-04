<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role(): void
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::factory()->create(['name' => 'test-role']);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('test-role'));
        $this->assertFalse($user->hasRole('non-existent'));
    }

    public function test_user_has_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $permission = \App\Models\Permission::factory()->create(['name' => 'test-permission']);
        $user->permissions()->attach($permission);

        $this->assertTrue($user->hasPermission('test-permission'));
        $this->assertFalse($user->hasPermission('non-existent'));
    }

    public function test_user_has_any_role(): void
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Role::factory()->create(['name' => 'role1']);
        $role2 = \App\Models\Role::factory()->create(['name' => 'role2']);
        $user->roles()->attach($role2);

        $this->assertTrue($user->hasAnyRole('role1', 'role2'));
        $this->assertFalse($user->hasAnyRole('role3', 'role4'));
    }

    public function test_user_relationships(): void
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Role::factory()->create();

        // User linkage is optional: entities created with the withUser() state
        $customer = \App\Models\Customer::factory()->withUser()->create();
        $vendor = \App\Models\Vendor::factory()->withUser()->create();

        $this->assertInstanceOf(\App\Models\User::class, $customer->user);
        $this->assertInstanceOf(\App\Models\User::class, $vendor->user);
        // And independently: entities can exist without any user
        $this->assertNull(\App\Models\Customer::factory()->create()->user);
        $this->assertNull(\App\Models\Vendor::factory()->create()->user);
    }
}

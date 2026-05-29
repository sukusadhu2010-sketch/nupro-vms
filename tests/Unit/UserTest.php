<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

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

        $this->assertTrue($user->hasAnyRole(['role1', 'role2']));
        $this->assertFalse($user->hasAnyRole(['role3', 'role4']));
    }

    public function test_user_relationships(): void
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::factory()->create();
        $customer = \App\Models\Customer::factory()->create(['user_id' => $user->id]);
        $vendor = \App\Models\Vendor::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->roles());
        $this->assertInstanceOf(\App\Models\Customer::class, $user->customer);
        $this->assertInstanceOf(\App\Models\Vendor::class, $user->vendor);
    }
}

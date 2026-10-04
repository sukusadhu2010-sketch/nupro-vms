<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Customers, Sub-Vendors and Foundry Management entities are independent
 * of the User entity. Sub-Vendor and Foundry Management are both Vendor
 * rows distinguished by vendor_type (SUB VENDOR / FOUNDRY).
 */
class EntityUserIndependenceTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Customer ----------

    public function test_customer_can_be_created_without_a_user(): void
    {
        $customer = Customer::factory()->create();

        $this->assertNull($customer->user_id);
        $this->assertNull($customer->user);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_customer_crud_works_without_a_user(): void
    {
        $customer = Customer::factory()->create();

        // Update without a user
        $customer->update(['name' => 'Updated Name', 'status' => 'inactive']);
        $this->assertSame('Updated Name', $customer->fresh()->name);
        $this->assertSame('inactive', $customer->fresh()->status);

        // Soft delete / restore without a user
        $customer->delete();
        $this->assertSoftDeleted($customer);
        $customer->restore();
        $this->assertNotSoftDeleted($customer);

        // Force delete without a user raises no FK error
        $customer->forceDelete();
        $this->assertDatabaseCount('customers', 0);
    }

    // ---------- Sub-Vendor ----------

    public function test_sub_vendor_can_be_created_without_a_user(): void
    {
        $vendor = Vendor::factory()->create(['vendor_type' => Vendor::VENDOR_TYPES[1]]);

        $this->assertSame('SUB VENDOR', $vendor->vendor_type);
        $this->assertNull($vendor->user_id);
        $this->assertNull($vendor->user);
        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_sub_vendor_crud_works_without_a_user(): void
    {
        $vendor = Vendor::factory()->create(['vendor_type' => 'SUB VENDOR']);

        $vendor->update(['name' => 'Sub Vendor Updated', 'status' => 'pending']);
        $this->assertSame('Sub Vendor Updated', $vendor->fresh()->name);

        $vendor->delete();
        $this->assertSoftDeleted($vendor);
        $vendor->restore();

        $vendor->forceDelete();
        $this->assertDatabaseCount('vendors', 0);
    }

    // ---------- Foundry Management ----------

    public function test_foundry_vendor_can_be_created_without_a_user(): void
    {
        $vendor = Vendor::factory()->create(['vendor_type' => Vendor::VENDOR_TYPES[0]]);

        $this->assertSame('FOUNDRY', $vendor->vendor_type);
        $this->assertNull($vendor->user_id);
        $this->assertNull($vendor->user);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_foundry_vendor_crud_works_without_a_user(): void
    {
        $vendor = Vendor::factory()->create(['vendor_type' => 'FOUNDRY']);

        $vendor->update(['name' => 'Foundry Updated', 'status' => 'suspended']);
        $this->assertSame('Foundry Updated', $vendor->fresh()->name);

        $vendor->delete();
        $this->assertSoftDeleted($vendor);
        $vendor->restore();

        $vendor->forceDelete();
        $this->assertDatabaseCount('vendors', 0);
    }

    // ---------- Optional linkage semantics ----------

    public function test_deleting_the_linked_user_nulls_user_id_instead_of_cascading(): void
    {
        $customer = Customer::factory()->withUser()->create();
        $vendor = Vendor::factory()->withUser()->create(['vendor_type' => 'FOUNDRY']);

        $customerUserId = $customer->user_id;
        $vendorUserId = $vendor->user_id;

        User::find($customerUserId)->forceDelete();

        $this->assertNull($customer->fresh()->user_id, 'Customer must survive its user being deleted');
        $this->assertNotNull(Vendor::find($vendor->id), 'Vendor must survive its user being deleted');

        User::find($vendorUserId)->forceDelete();

        $this->assertNull($vendor->fresh()->user_id, 'Vendor user_id must be nulled when its user is deleted');
    }

    public function test_entities_with_and_without_users_coexist(): void
    {
        Customer::factory()->count(3)->create();
        Customer::factory()->withUser()->count(2)->create();
        Vendor::factory()->count(3)->create();
        Vendor::factory()->withUser()->count(2)->create(['vendor_type' => 'SUB VENDOR']);

        $this->assertSame(5, Customer::count());
        $this->assertSame(5, Vendor::count());
    }
}

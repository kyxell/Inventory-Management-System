<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Administrator'],
            ['id' => 2, 'name' => 'Staff'],
        ]);
    }

    public function test_legacy_login_creates_a_session_for_inventory_api_requests(): void
    {
        User::factory()->create([
            'username' => 'inventory_staff',
            'role_id' => 2,
            'password_hash' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/process/login_process.php', [
            'username' => 'inventory_staff',
            'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('success', true);

        $this->getJson('/api/process/get_data.php?type=categories')
            ->assertOk()
            ->assertJsonPath('categories', []);
    }

    public function test_product_creation_stock_movements_and_dashboard_data_work(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $category = Category::create(['name' => 'Drinks']);
        $this->actingAs($admin);

        $this->postJson('/api/process/add_process.php', [
            'name' => 'Calamansi juice',
            'description' => 'Fresh citrus drink',
            'category_id' => $category->id,
            'unit_price' => 45,
            'cost_price' => 20,
            'quantity' => 8,
        ])->assertOk()->assertJsonPath('success', true);

        $productId = (int) DB::table('products')->value('id');

        $this->postJson('/api/process/import_process.php', [
            'id' => $productId,
            'quantity' => 3,
        ])->assertOk()->assertJsonPath('newQuantity', 11);

        $this->postJson('/api/process/deliver_process.php', [
            'id' => $productId,
            'quantity' => 4,
        ])->assertOk()->assertJsonPath('newQuantity', 7);

        $this->getJson('/api/process/get_data.php?type=products')
            ->assertOk()
            ->assertJsonPath('products.0.isAdmin', true)
            ->assertJsonPath('products.0.quantity', 7);

        $this->getJson('/api/process/get_data.php?type=inventory_summary')
            ->assertOk()
            ->assertJsonPath('totalProducts', 1)
            ->assertJsonPath('totalInventory', 7)
            ->assertJsonPath('lowStock', 1);

        $this->getJson('/api/process/get_data.php?type=logs&per_page=25')
            ->assertOk()
            ->assertJsonPath('pagination.total', 3);

        $this->getJson('/api/process/get_data.php?type=inventory_charts')
            ->assertOk()
            ->assertJsonPath("chartsData.{$productId}.name", 'Calamansi juice')
            ->assertJsonCount(3, "chartsData.{$productId}.values");

        $this->getJson("/api/process/get_data.php?type=product_for_edit&id={$productId}")
            ->assertOk()
            ->assertJsonPath('name', 'Calamansi juice');
    }

    public function test_non_admin_cannot_edit_products(): void
    {
        $staff = User::factory()->create(['role_id' => 2]);
        $category = Category::create(['name' => 'Snacks']);
        $this->actingAs($staff);

        $this->postJson('/api/process/edit_process.php', [
            'id' => 1,
            'name' => 'Chips',
            'description' => 'Potato chips',
            'category_id' => $category->id,
            'unit_price' => 10,
            'cost_price' => 5,
        ])->assertForbidden();
    }

    public function test_admin_can_edit_and_delete_other_accounts_but_not_their_own(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $staff = User::factory()->create(['role_id' => 2]);
        $this->actingAs($admin);

        $this->getJson('/api/process/get_account_data.php?type=roles')
            ->assertOk()
            ->assertJsonCount(2, 'roles');

        $this->getJson('/api/process/get_account_data.php?type=users')
            ->assertOk()
            ->assertJsonCount(2, 'users');

        $this->postJson('/api/process/account_edit_process.php', [
            'id' => $staff->id,
            'username' => 'updated_staff',
            'email' => 'updated@example.com',
            'role_id' => 2,
            'last_login' => '2025-04-11 09:30',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'username' => 'updated_staff',
            'email' => 'updated@example.com',
        ]);

        $this->postJson('/api/process/account_delete_process.php', ['id' => $admin->id])
            ->assertUnprocessable();

        $this->postJson('/api/process/account_delete_process.php', ['id' => $staff->id])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_legacy_account_registration_and_logout_work(): void
    {
        $this->postJson('/api/process/register_process.php', [
            'username' => 'new_staff',
            'email' => 'new-staff@example.com',
            'first_name' => 'New',
            'last_name' => 'Staff',
            'password' => 'secret-password',
            'confirm_password' => 'secret-password',
            'role_id' => 2,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/process/login_process.php', [
            'username' => 'new_staff',
            'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/process/logout_process.php')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/process/get_data.php?type=categories')->assertUnauthorized();
    }
}

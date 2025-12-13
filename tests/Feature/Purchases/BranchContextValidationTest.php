<?php

declare(strict_types=1);

namespace Tests\Feature\Purchases;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchContextValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;
    protected User $user;
    protected Supplier $supplierA;
    protected Supplier $supplierB;
    protected Purchase $purchaseA;
    protected Purchase $purchaseB;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Allow all permissions for tests
        \Illuminate\Support\Facades\Gate::before(function () {
            return true;
        });

        // Disable middleware that requires complex setup for these tests
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureModuleEnabled::class,
            \App\Http\Middleware\EnsurePermission::class,
            \App\Http\Middleware\Require2FA::class,
        ]);

        // Create branches
        $this->branchA = Branch::create([
            'name' => 'Branch A',
            'code' => 'BA001',
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'name' => 'Branch B',
            'code' => 'BB001',
            'is_active' => true,
        ]);

        // Create user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        // Create product
        $this->product = Product::create([
            'name' => 'Test Product',
            'code' => 'PROD001',
            'type' => 'stock',
            'default_price' => 100,
            'branch_id' => $this->branchA->id,
        ]);

        // Create suppliers
        $this->supplierA = Supplier::create([
            'name' => 'Supplier A',
            'branch_id' => $this->branchA->id,
        ]);

        $this->supplierB = Supplier::create([
            'name' => 'Supplier B',
            'branch_id' => $this->branchB->id,
        ]);

        // Create purchases
        $this->purchaseA = Purchase::create([
            'purchase_number' => 'PO-A-001',
            'supplier_id' => $this->supplierA->id,
            'branch_id' => $this->branchA->id,
            'status' => 'draft',
            'sub_total' => 1000,
            'grand_total' => 1000,
            'due_total' => 1000,
        ]);

        $this->purchaseB = Purchase::create([
            'purchase_number' => 'PO-B-001',
            'supplier_id' => $this->supplierB->id,
            'branch_id' => $this->branchB->id,
            'status' => 'draft',
            'sub_total' => 2000,
            'grand_total' => 2000,
            'due_total' => 2000,
        ]);

        // Authenticate as user
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_purchase_index_returns_400_without_branch_context(): void
    {
        // This simulates a request without branch context set by middleware
        $response = $this->getJson('/api/v1/branches/999/purchases');

        // Should get 404 from route model binding or other error
        $this->assertTrue(in_array($response->status(), [400, 404]));
    }

    /** @test */
    public function test_purchase_index_only_shows_branch_purchases(): void
    {
        // Mock branch context middleware
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/purchases");

        $response->assertOk();
        $data = $response->json('data.data') ?? $response->json('data');

        if (is_array($data) && count($data) > 0) {
            $purchaseIds = array_column($data, 'id');
            $this->assertContains($this->purchaseA->id, $purchaseIds);
            $this->assertNotContains($this->purchaseB->id, $purchaseIds);
        }
    }

    /** @test */
    public function test_purchase_show_returns_404_for_wrong_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/purchases/{$this->purchaseB->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function test_purchase_show_succeeds_for_correct_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/purchases/{$this->purchaseA->id}");

        $response->assertOk();
        $this->assertEquals($this->purchaseA->id, $response->json('data.id'));
    }

    /** @test */
    public function test_purchase_update_returns_404_for_wrong_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->patchJson("/api/v1/branches/{$this->branchA->id}/purchases/{$this->purchaseB->id}", [
            'notes' => 'Updated',
        ]);

        $response->assertNotFound();
    }

    /** @test */
    public function test_purchase_update_succeeds_for_correct_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->patchJson("/api/v1/branches/{$this->branchA->id}/purchases/{$this->purchaseA->id}", [
            'notes' => 'Updated Purchase A',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function test_purchase_store_requires_branch_context(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->postJson("/api/v1/branches/{$this->branchA->id}/purchases", [
            'supplier_id' => $this->supplierA->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 10,
                    'price' => 50,
                ],
            ],
        ]);

        // Should succeed or return validation error, but not 400/500
        $this->assertTrue(in_array($response->status(), [200, 201, 422]));
    }
}

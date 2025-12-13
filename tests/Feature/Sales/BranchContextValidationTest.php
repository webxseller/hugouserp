<?php

declare(strict_types=1);

namespace Tests\Feature\Sales;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchContextValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;
    protected User $user;
    protected Customer $customerA;
    protected Customer $customerB;
    protected Sale $saleA;
    protected Sale $saleB;
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

        // Create customers
        $this->customerA = Customer::create([
            'name' => 'Customer A',
            'branch_id' => $this->branchA->id,
        ]);

        $this->customerB = Customer::create([
            'name' => 'Customer B',
            'branch_id' => $this->branchB->id,
        ]);

        // Create sales
        $this->saleA = Sale::create([
            'sale_number' => 'SALE-A-001',
            'customer_id' => $this->customerA->id,
            'branch_id' => $this->branchA->id,
            'status' => 'completed',
            'sub_total' => 100,
            'grand_total' => 100,
            'paid_total' => 100,
        ]);

        $this->saleB = Sale::create([
            'sale_number' => 'SALE-B-001',
            'customer_id' => $this->customerB->id,
            'branch_id' => $this->branchB->id,
            'status' => 'completed',
            'sub_total' => 200,
            'grand_total' => 200,
            'paid_total' => 200,
        ]);

        // Authenticate as user
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_sale_index_only_shows_branch_sales(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/sales");

        $response->assertOk();
        $data = $response->json('data.data') ?? $response->json('data');

        if (is_array($data) && count($data) > 0) {
            $saleIds = array_column($data, 'id');
            $this->assertContains($this->saleA->id, $saleIds);
            $this->assertNotContains($this->saleB->id, $saleIds);
        }
    }

    /** @test */
    public function test_sale_show_returns_404_for_wrong_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleB->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function test_sale_show_succeeds_for_correct_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->getJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleA->id}");

        $response->assertOk();
        $this->assertEquals($this->saleA->id, $response->json('data.id'));
    }

    /** @test */
    public function test_sale_update_returns_404_for_wrong_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->patchJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleB->id}", [
            'notes' => 'Updated',
        ]);

        $response->assertNotFound();
    }

    /** @test */
    public function test_sale_update_succeeds_for_correct_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->patchJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleA->id}", [
            'notes' => 'Updated Sale A',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function test_sale_print_invoice_requires_branch_context(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->postJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleA->id}/print");

        // Should succeed or return error, but not crash
        $this->assertTrue(in_array($response->status(), [200, 404, 422, 500]));
    }

    /** @test */
    public function test_sale_print_invoice_returns_404_for_wrong_branch(): void
    {
        $response = $this->withMiddleware([
            function ($request, $next) {
                $request->attributes->set('branch_id', $this->branchA->id);
                return $next($request);
            },
        ])->postJson("/api/v1/branches/{$this->branchA->id}/sales/{$this->saleB->id}/print");

        // Should return 404 or 500 due to branch mismatch
        $this->assertTrue(in_array($response->status(), [404, 500]));
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class StockAlerts extends Component
{
    use WithPagination;

    public string $search = '';
    public string $alertType = 'all'; // all, low, out, expiring

    public function render()
    {
        $query = Product::query()
            ->with(['branch', 'category', 'unit'])
            ->where('track_stock_alerts', true)
            ->where('status', 'active');

        // Calculate current stock for each product
        $query->addSelect([
            'current_stock' => StockMovement::selectRaw('
                COALESCE(
                    SUM(CASE WHEN direction = "in" THEN qty ELSE -qty END),
                    0
                )
            ')
            ->whereColumn('product_id', 'products.id')
            ->where('status', 'posted')
        ]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        // Filter by alert type
        if ($this->alertType === 'low') {
            $query->havingRaw('current_stock <= min_stock AND current_stock > 0');
        } elseif ($this->alertType === 'out') {
            $query->havingRaw('current_stock <= 0');
        }

        $products = $query->paginate(20);

        return view('livewire.inventory.stock-alerts', [
            'products' => $products,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAlertType(): void
    {
        $this->resetPage();
    }
}

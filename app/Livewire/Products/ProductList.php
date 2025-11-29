<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public function mount()
    {
        // Check permission
        if (! auth()->user()->can('view products')) {
            abort(403);
        }
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        session()->flash('message', 'Product deleted successfully.');
    }

    public function render()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $products = $query->withCount('campaigns')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.products.product-list', [
            'products' => $products,
        ])->layout('components.layouts.app', ['title' => 'Products']);
    }
}

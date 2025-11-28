<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    public Product $product;
    public $name = '';
    public $description = '';
    public $features = [];
    public $pricing_info = '';
    public $ai_prompt_context = '';
    public $common_objections = [];
    public $recommended_responses = [];
    public $cold_call_script_template = '';
    public $success_definition = '';
    public $status = 'active';
    public $newFeature = '';
    public $newObjection = ['type' => '', 'objection' => '', 'response' => ''];

    public function mount($id = null)
    {
        if (!auth()->user()->can($id ? 'edit products' : 'create products')) {
            abort(403);
        }

        if ($id) {
            $this->product = Product::findOrFail($id);
            $this->name = $this->product->name;
            $this->description = $this->product->description ?? '';
            $this->features = $this->product->features ?? [];
            $this->pricing_info = $this->product->pricing_info ?? '';
            $this->ai_prompt_context = $this->product->ai_prompt_context ?? '';
            $this->common_objections = $this->product->common_objections ?? [];
            $this->recommended_responses = $this->product->recommended_responses ?? [];
            $this->cold_call_script_template = $this->product->cold_call_script_template ?? '';
            $this->success_definition = $this->product->success_definition ?? '';
            $this->status = $this->product->status;
        } else {
            $this->product = new Product();
        }
    }

    public function addFeature()
    {
        if ($this->newFeature) {
            $this->features[] = $this->newFeature;
            $this->newFeature = '';
        }
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function addObjection()
    {
        if ($this->newObjection['type'] && $this->newObjection['objection'] && $this->newObjection['response']) {
            $this->common_objections[] = $this->newObjection;
            $this->newObjection = ['type' => '', 'objection' => '', 'response' => ''];
        }
    }

    public function removeObjection($index)
    {
        unset($this->common_objections[$index]);
        $this->common_objections = array_values($this->common_objections);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pricing_info' => 'nullable|string',
            'ai_prompt_context' => 'nullable|string',
            'cold_call_script_template' => 'nullable|string',
            'success_definition' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $this->product->fill([
            'name' => $this->name,
            'description' => $this->description,
            'features' => $this->features,
            'pricing_info' => $this->pricing_info,
            'ai_prompt_context' => $this->ai_prompt_context,
            'common_objections' => $this->common_objections,
            'recommended_responses' => $this->recommended_responses,
            'cold_call_script_template' => $this->cold_call_script_template,
            'success_definition' => $this->success_definition,
            'status' => $this->status,
        ])->save();

        session()->flash('message', $this->product->wasRecentlyCreated ? 'Product created successfully.' : 'Product updated successfully.');

        return $this->redirect(route('products.index'));
    }

    public function render()
    {
        return view('livewire.products.product-form')
            ->layout('components.layouts.app', ['title' => $this->product->exists ? 'Edit Product' : 'Create Product']);
    }
}

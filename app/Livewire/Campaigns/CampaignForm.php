<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Product;
use Livewire\Component;

class CampaignForm extends Component
{
    public Campaign $campaign;
    public $name = '';
    public $product_id = null;
    public $product_name = '';
    public $script = '';
    public $ai_prompt_context = '';
    public $success_definition = '';
    public $status = 'draft';

    public function mount($id = null)
    {
        if ($id) {
            $this->campaign = Campaign::with('product')->findOrFail($id);
            $this->name = $this->campaign->name;
            $this->product_id = $this->campaign->product_id;
            $this->product_name = $this->campaign->product_name;
            $this->script = $this->campaign->script;
            $this->ai_prompt_context = $this->campaign->ai_prompt_context;
            $this->success_definition = $this->campaign->success_definition;
            $this->status = $this->campaign->status;
        } else {
            $this->campaign = new Campaign();
        }
    }

    public function updatedProductId()
    {
        if ($this->product_id) {
            $product = Product::find($this->product_id);
            if ($product) {
                // Optionally pre-fill AI context from product
                if (empty($this->ai_prompt_context)) {
                    $this->ai_prompt_context = $product->ai_prompt_context ?? '';
                }
                if (empty($this->success_definition)) {
                    $this->success_definition = $product->success_definition ?? '';
                }
                if (empty($this->script)) {
                    $this->script = $product->cold_call_script_template ?? '';
                }
            }
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => $this->product_id ? 'nullable' : 'required|string|max:255',
            'script' => 'nullable|string',
            'ai_prompt_context' => 'nullable|string',
            'success_definition' => 'nullable|string',
            'status' => 'required|in:draft,active,paused,completed',
        ], [
            'product_name.required' => 'Please select a product or enter a product name.',
        ]);

        $this->campaign->fill([
            'name' => $this->name,
            'product_id' => $this->product_id,
            'product_name' => $this->product_id ? null : $this->product_name, // Only use product_name if no product_id
            'script' => $this->script,
            'ai_prompt_context' => $this->ai_prompt_context,
            'success_definition' => $this->success_definition,
            'status' => $this->status,
        ])->save();

        session()->flash('message', $this->campaign->wasRecentlyCreated ? 'Campaign created successfully.' : 'Campaign updated successfully.');

        return $this->redirect(route('campaigns.index'));
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-form', [
            'products' => Product::where('status', 'active')->get(),
        ])->layout('components.layouts.app', ['title' => $this->campaign->exists ? 'Edit Campaign' : 'Create Campaign']);
    }
}

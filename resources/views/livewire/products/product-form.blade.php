<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ $product->exists ? 'Edit Product' : 'Create Product' }}</h1>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6 space-y-6">
        <div>
            <label class="block text-sm font-medium mb-2">Product Name *</label>
            <input type="text" wire:model="name" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
            @error('name') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Description</label>
            <textarea wire:model="description" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Features</label>
            <div class="space-y-2">
                <div class="flex gap-2">
                    <input type="text" wire:model="newFeature" wire:keydown.enter.prevent="addFeature" placeholder="Add feature..." class="flex-1 px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    <button type="button" wire:click="addFeature" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-4 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">Add</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($features as $index => $feature)
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded flex items-center gap-2">
                            {{ $feature }}
                            <button type="button" wire:click="removeFeature({{ $index }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">×</button>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Pricing Information</label>
            <textarea wire:model="pricing_info" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="Pricing details, packages, etc."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">AI Prompt Context</label>
            <textarea wire:model="ai_prompt_context" rows="5" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="Product details, common objections, recommended responses..."></textarea>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">This context will be used to train the AI for call suggestions</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Cold Call Script Template</label>
            <textarea wire:model="cold_call_script_template" rows="8" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="Template script for cold calling..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Common Objections</label>
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" wire:model="newObjection.type" placeholder="Type (e.g., price)" class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    <input type="text" wire:model="newObjection.objection" placeholder="Objection" class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    <div class="flex gap-2">
                        <input type="text" wire:model="newObjection.response" placeholder="Response" class="flex-1 px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                        <button type="button" wire:click="addObjection" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-4 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">Add</button>
                    </div>
                </div>
                @foreach($common_objections as $index => $objection)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded flex items-center justify-between">
                        <div class="flex-1">
                            <strong>{{ $objection['type'] ?? 'N/A' }}:</strong> {{ $objection['objection'] ?? '' }}
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $objection['response'] ?? '' }}</div>
                        </div>
                        <button type="button" wire:click="removeObjection({{ $index }})" class="text-red-600 dark:text-red-400 hover:text-red-800">Remove</button>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Success Definition</label>
            <textarea wire:model="success_definition" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="What defines success for this product?"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Status *</label>
            <select wire:model="status" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 cursor-pointer">
                {{ $product->exists ? 'Update' : 'Create' }} Product
            </button>
            <a href="{{ route('products.index') }}" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-6 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">
                Cancel
            </a>
        </div>
    </form>
</div>




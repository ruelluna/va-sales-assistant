<div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ $campaign->exists ? 'Edit Campaign' : 'Create Campaign' }}</h1>
        </div>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit="save" class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Campaign Name *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    @error('name') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Product</label>
                    <select wire:model.live="product_id" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                        <option value="">Select a Product (or enter custom name below)</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Selecting a product will pre-fill script and AI context</p>
                </div>

                @if(!$product_id)
                    <div>
                        <label class="block text-sm font-medium mb-2">Product Name (if no product selected)</label>
                        <input type="text" wire:model="product_name" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="Enter product name">
                        @error('product_name') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-2">Script</label>
                    <textarea wire:model="script" rows="5" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800"></textarea>
                    @error('script') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">AI Prompt Context</label>
                    <textarea wire:model="ai_prompt_context" rows="5" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="Product details, common objections, recommended responses..."></textarea>
                    @error('ai_prompt_context') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Success Definition</label>
                    <textarea wire:model="success_definition" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800" placeholder="What defines success for this campaign?"></textarea>
                    @error('success_definition') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Status *</label>
                    <select wire:model="status" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                    </select>
                    @error('status') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 cursor-pointer">
                        {{ $campaign->exists ? 'Update' : 'Create' }} Campaign
                    </button>
                    <a href="{{ route('campaigns.index') }}" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-6 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

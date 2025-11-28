<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ $contact->exists ? 'Edit Contact' : 'Create Contact' }}</h1>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2">Campaign *</label>
                <select wire:model="campaign_id" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    <option value="">Select a Campaign</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                @error('campaign_id') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">First Name *</label>
                    <input type="text" wire:model="first_name" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    @error('first_name') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Last Name *</label>
                    <input type="text" wire:model="last_name" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                    @error('last_name') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Phone *</label>
                <input type="text" wire:model="phone" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                @error('phone') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" wire:model="email" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                @error('email') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Company</label>
                <input type="text" wire:model="company" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                @error('company') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tags</label>
                <input type="text" wire:model="tags" placeholder="Enter tags separated by commas" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                @error('tags') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Enter tags separated by commas (e.g., "lead, hot, follow-up")</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Timezone</label>
                <input type="text" wire:model="timezone" placeholder="e.g., America/New_York" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                @error('timezone') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 cursor-pointer">
                    {{ $contact->exists ? 'Update' : 'Create' }} Contact
                </button>
                <a href="{{ route('contacts.index') }}" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-6 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

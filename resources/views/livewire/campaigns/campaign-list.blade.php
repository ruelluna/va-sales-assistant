<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <flux:heading size="xl">Campaigns</flux:heading>
        <flux:button href="{{ route('campaigns.create') }}" variant="primary" class="cursor-pointer">
            Create Campaign
        </flux:button>
    </div>

    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search campaigns..." />
            <flux:select wire:model.live="statusFilter">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="draft">Draft</flux:select.option>
                <flux:select.option value="active">Active</flux:select.option>
                <flux:select.option value="paused">Paused</flux:select.option>
                <flux:select.option value="completed">Completed</flux:select.option>
            </flux:select>
        </div>
    </div>

    @if(session()->has('message'))
        <flux:callout variant="success" class="mb-6">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Contacts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Calls</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($campaigns as $campaign)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <td class="px-6 py-4 align-middle">
                            <flux:link href="{{ route('campaigns.show', $campaign->id) }}" class="cursor-pointer">
                                <flux:heading size="sm">{{ $campaign->name }}</flux:heading>
                            </flux:link>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <flux:text size="base">{{ $campaign->product_name }}</flux:text>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <flux:text size="base">{{ $campaign->contacts_count }}</flux:text>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <flux:text size="base">{{ $campaign->call_sessions_count }}</flux:text>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <flux:badge color="{{ match($campaign->status) {
                                'active' => 'green',
                                'paused' => 'yellow',
                                'completed' => 'blue',
                                default => 'zinc',
                            } }}" size="sm">
                                {{ ucfirst($campaign->status) }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <div class="flex items-center gap-3">
                                <flux:link href="{{ route('campaigns.edit', $campaign->id) }}" class="cursor-pointer">Edit</flux:link>
                                <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                <flux:link href="{{ route('campaigns.contacts.import', $campaign->id) }}" class="cursor-pointer">Import</flux:link>
                                <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                <button wire:click="delete({{ $campaign->id }})" wire:confirm="Are you sure?" class="text-red-600 dark:text-red-400 hover:underline cursor-pointer">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <flux:text size="base" class="text-zinc-500 dark:text-zinc-400">No campaigns found</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $campaigns->links() }}
    </div>
</div>

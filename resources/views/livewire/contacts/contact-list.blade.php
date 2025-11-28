<div>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Contacts</h1>
            <button wire:click="$set('showImportModal', true)" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 cursor-pointer">
                Import Contacts
            </button>
        </div>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search contacts..." class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                </div>
                <div>
                    <select wire:model.live="campaignFilter" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                        <option value="">All Campaigns</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->campaign->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <button wire:click="call({{ $contact->id }})" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Call</button>
                                    @if(auth()->user()->hasAnyRole(['admin', 'manager']))
                                        <a href="{{ route('contacts.edit', $contact->id) }}" class="text-green-600 dark:text-green-400 hover:underline cursor-pointer">Edit</a>
                                    @endif
                                    <button wire:click="delete({{ $contact->id }})" wire:confirm="Are you sure?" class="text-red-600 dark:text-red-400 hover:underline cursor-pointer">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">No contacts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $contacts->links() }}
        </div>
    </div>

    @if($showImportModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-6 max-w-md w-full">
                <h2 class="text-xl font-bold mb-4">Import Contacts</h2>
                <form wire:submit="import">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Campaign</label>
                            <select wire:model="importCampaignId" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                                <option value="">Select Campaign</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                            @error('importCampaignId') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">CSV File</label>
                            <input type="file" wire:model="importFile" accept=".csv,.txt" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                            @error('importFile') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">CSV format: first_name,last_name,phone,email,company,tags,timezone</p>
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer">Import</button>
                            <button type="button" wire:click="$set('showImportModal', false)" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-4 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:link href="{{ route('campaigns.index') }}" class="cursor-pointer">← Back to Campaigns</flux:link>
            <flux:heading size="xl">{{ $campaign->name }}</flux:heading>
        </div>
        <div class="flex gap-3">
            <flux:button href="{{ route('campaigns.edit', $campaign->id) }}" variant="ghost" class="cursor-pointer">Edit
            </flux:button>
            <flux:button href="{{ route('campaigns.contacts.import', $campaign->id) }}" variant="ghost"
                class="cursor-pointer">Import Contacts</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-8">
                <flux:heading size="lg" class="mb-6">Campaign Information</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Status</flux:text>
                        <flux:badge
                            color="{{ match ($campaign->status) {
                                'active' => 'green',
                                'paused' => 'yellow',
                                'completed' => 'blue',
                                default => 'zinc',
                            } }}"
                            size="base">
                            {{ ucfirst($campaign->status) }}
                        </flux:badge>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Total Contacts
                        </flux:text>
                        <flux:heading size="base">{{ $campaign->contacts()->count() }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Total Calls</flux:text>
                        <flux:heading size="base">{{ $campaign->callSessions()->count() }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Created</flux:text>
                        <flux:text size="base">{{ $campaign->created_at->format('M d, Y') }}</flux:text>
                    </div>
                </div>
                @if ($campaign->script)
                    <div class="mt-6">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">Script</flux:text>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                            <flux:text size="base" class="whitespace-pre-wrap">{{ $campaign->script }}</flux:text>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-8">
                <div class="mb-6 flex items-center justify-between">
                    <flux:heading size="lg">Contacts</flux:heading>
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search contacts..."
                        class="w-64" />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-max">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Phone</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Latest Note</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Calls</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($contacts as $contact)
                                <tr wire:key="contact-{{ $contact->id }}"
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                                        <flux:text size="base" class="font-medium">{{ $contact->full_name }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                                        <flux:text size="base">{{ $contact->phone }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                                        <flux:text size="base">{{ $contact->company ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        @if ($contact->latestNote)
                                            <div class="max-w-xs">
                                                <flux:text size="sm"
                                                    class="text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                                    {{ \Illuminate\Support\Str::limit($contact->latestNote->note, 100) }}
                                                </flux:text>
                                                <flux:text size="xs" class="text-zinc-400 dark:text-zinc-500 mt-1">
                                                    {{ $contact->latestNote->created_at->diffForHumans() }}</flux:text>
                                            </div>
                                        @else
                                            <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500">No notes
                                            </flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                                        <flux:text size="base">{{ $contact->call_sessions_count }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                                        <div class="flex gap-2">
                                            @if ($campaign->status === 'active')
                                                <button wire:click="callContact({{ $contact->id }})"
                                                    wire:key="call-btn-{{ $contact->id }}"
                                                    data-contact-id="{{ $contact->id }}"
                                                    data-contact-phone="{{ $contact->phone }}"
                                                    wire:loading.attr="disabled"
                                                    wire:target="callContact({{ $contact->id }})"
                                                    onclick="console.log('Call button clicked', { contactId: {{ $contact->id }}, phone: '{{ $contact->phone }}' });"
                                                    class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span wire:loading.remove
                                                        wire:target="callContact({{ $contact->id }})">Call</span>
                                                    <span wire:loading wire:target="callContact({{ $contact->id }})"
                                                        class="flex items-center gap-2">
                                                        <svg class="animate-spin h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Calling...
                                                    </span>
                                                </button>
                                            @endif
                                            @if ($campaign->status === 'active' && !app()->environment('production'))
                                                <button wire:click="mockCall({{ $contact->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="mockCall({{ $contact->id }})"
                                                    class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span wire:loading.remove
                                                        wire:target="mockCall({{ $contact->id }})">Mock Call</span>
                                                    <span wire:loading wire:target="mockCall({{ $contact->id }})"
                                                        class="flex items-center gap-2">
                                                        <svg class="animate-spin h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Seeding...
                                                    </span>
                                                </button>
                                            @endif
                                            <button wire:click="openNoteModal({{ $contact->id }})" type="button"
                                                class="px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg cursor-pointer transition-colors">
                                                Add Note
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center">
                                        <flux:text size="base" class="text-zinc-500 dark:text-zinc-400">No contacts
                                            found</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $contacts->links() }}
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if ($campaign->product)
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-8">
                    <flux:heading size="lg" class="mb-6">Product Information</flux:heading>

                    <div class="space-y-6">
                        <div>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">Name</flux:text>
                            <flux:heading size="base">{{ $campaign->product->name }}</flux:heading>
                        </div>

                        @if ($campaign->product->description)
                            <div>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">Description
                                </flux:text>
                                <flux:text size="base">{{ $campaign->product->description }}</flux:text>
                            </div>
                        @endif

                        @if ($campaign->product->features && count($campaign->product->features) > 0)
                            <div>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">Features
                                </flux:text>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($campaign->product->features as $feature)
                                        <li>
                                            <flux:text size="base">{{ $feature }}</flux:text>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($campaign->product->pricing_info)
                            <div>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">Pricing
                                    Information</flux:text>
                                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                                    <flux:text size="base" class="whitespace-pre-wrap">
                                        {{ $campaign->product->pricing_info }}</flux:text>
                                </div>
                            </div>
                        @endif

                        @if ($campaign->product->status)
                            <div>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Status
                                </flux:text>
                                <flux:badge
                                    color="{{ match ($campaign->product->status) {
                                        'active' => 'green',
                                        'inactive' => 'zinc',
                                        default => 'zinc',
                                    } }}"
                                    size="sm">
                                    {{ ucfirst($campaign->product->status) }}
                                </flux:badge>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-8">
                    <flux:heading size="lg" class="mb-4">Product Information</flux:heading>
                    <flux:text size="base" class="text-zinc-500 dark:text-zinc-400">No product associated with this
                        campaign</flux:text>
                </div>
            @endif
        </div>
    </div>

    @if ($showNoteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            wire:click="closeNoteModal">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-8 max-w-md w-full mx-4" wire:click.stop>
                <div class="mb-6">
                    <flux:heading size="lg">Add Note</flux:heading>
                </div>
                <form wire:submit="saveNote">
                    <div class="space-y-4">
                        <flux:textarea wire:model="note" label="Note" rows="5"
                            placeholder="Enter your note here..." />
                        @error('note')
                            <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}
                            </flux:text>
                        @enderror
                        <div class="flex gap-3 justify-end">
                            <flux:button type="button" wire:click="closeNoteModal" variant="ghost"
                                class="cursor-pointer">Cancel</flux:button>
                            <flux:button type="submit" variant="primary" class="cursor-pointer">Save Note
                            </flux:button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if (session()->has('message'))
        <flux:callout variant="success" class="fixed bottom-4 right-4 z-50 max-w-md">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" class="fixed bottom-4 right-4 z-50 max-w-md">
            {{ session('error') }}
        </flux:callout>
    @endif

    @livewire('dialer.dialer-modal')
</div>

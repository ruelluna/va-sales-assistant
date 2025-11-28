<x-layouts.app :title="__('Dashboard')">
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ __('Dashboard') }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Campaigns</p>
                        <p class="text-2xl font-bold mt-1">{{ \App\Models\Campaign::count() }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Contacts</p>
                        <p class="text-2xl font-bold mt-1">{{ \App\Models\Contact::count() }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Calls</p>
                        <p class="text-2xl font-bold mt-1">{{ \App\Models\CallSession::count() }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Active Campaigns</p>
                        <p class="text-2xl font-bold mt-1">{{ \App\Models\Campaign::where('status', 'active')->count() }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold mb-4">Recent Calls</h2>
                <div class="space-y-3">
                    @forelse(\App\Models\CallSession::with(['contact', 'campaign'])->latest()->limit(5)->get() as $call)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded">
                            <div>
                                <p class="font-medium">{{ $call->contact->full_name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $call->campaign->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $call->status)) }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $call->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-zinc-600 dark:text-zinc-400">No calls yet</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <a href="{{ route('calls.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">View all calls →</a>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold mb-4">Active Campaigns</h2>
                <div class="space-y-3">
                    @forelse(\App\Models\Campaign::where('status', 'active')->withCount('contacts')->latest()->limit(5)->get() as $campaign)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded">
                            <div>
                                <p class="font-medium">{{ $campaign->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $campaign->contacts_count }} contacts</p>
                            </div>
                            <div>
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs">Active</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-zinc-600 dark:text-zinc-400">No active campaigns</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <a href="{{ route('campaigns.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">View all campaigns →</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

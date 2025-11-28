<div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Call Detail</h1>
            <a href="{{ route('calls.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">← Back to Calls</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                    <h2 class="text-lg font-semibold mb-4">Call Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Contact</p>
                            <p class="font-medium">{{ $callSession->contact->full_name }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $callSession->contact->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Campaign</p>
                            <p class="font-medium">{{ $callSession->campaign->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Status</p>
                            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $callSession->status)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Duration</p>
                            <p class="font-medium">{{ $callSession->duration_seconds ? gmdate('H:i:s', $callSession->duration_seconds) : 'N/A' }}</p>
                        </div>
                        @if($callSession->outcome)
                            <div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Outcome</p>
                                <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $callSession->outcome)) }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($callSession->summary)
                    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-lg font-semibold mb-4">Summary</h2>
                        <p class="text-zinc-700 dark:text-zinc-300">{{ $callSession->summary }}</p>
                    </div>
                @endif

                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                    <h2 class="text-lg font-semibold mb-4">Transcript</h2>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($callSession->transcripts as $transcript)
                            <div class="p-3 rounded {{ $transcript->speaker === 'va' ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-zinc-50 dark:bg-zinc-800' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium">{{ ucfirst($transcript->speaker) }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ gmdate('H:i:s', (int)$transcript->timestamp) }}</span>
                                </div>
                                <p class="text-zinc-700 dark:text-zinc-300">{{ $transcript->text }}</p>
                            </div>
                        @empty
                            <p class="text-zinc-500 dark:text-zinc-400">No transcript available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @if($callSession->real_time_tags && count($callSession->real_time_tags) > 0)
                    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-lg font-semibold mb-4">Tags</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($callSession->real_time_tags as $tag)
                                <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-sm">
                                    {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($callSession->next_action)
                    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-lg font-semibold mb-4">Next Action</h2>
                        <p class="text-zinc-700 dark:text-zinc-300">{{ $callSession->next_action }}</p>
                        @if($callSession->next_action_due_at)
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                                Due: {{ $callSession->next_action_due_at->format('M d, Y H:i') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

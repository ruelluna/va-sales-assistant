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
                    <div class="space-y-3 max-h-96 overflow-y-auto p-2">
                        @forelse($callSession->transcripts as $transcript)
                            @php
                                $isVa = $transcript->speaker === 'va';
                                $isSystem = $transcript->speaker === 'system';
                            @endphp
                            @if ($isSystem)
                                {{-- System messages centered --}}
                                <div class="flex justify-center">
                                    <div class="px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm">
                                        {{ $transcript->text }}
                                    </div>
                                </div>
                            @else
                                {{-- Chat-like messages: VA on right, Prospect on left --}}
                                <div class="flex {{ $isVa ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[75%] flex gap-2 {{ $isVa ? 'flex-row-reverse' : 'flex-row' }}">
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold {{ $isVa ? 'bg-blue-500 text-white' : 'bg-zinc-500 text-white' }}">
                                            {{ $isVa ? 'VA' : 'P' }}
                                        </div>
                                        {{-- Message bubble --}}
                                        <div class="flex flex-col {{ $isVa ? 'items-end' : 'items-start' }}">
                                            <div class="px-4 py-2 rounded-lg {{ $isVa ? 'bg-blue-500 text-white rounded-br-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-bl-sm' }}">
                                                <p class="text-sm whitespace-pre-wrap break-words">{{ $transcript->text }}</p>
                                            </div>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 px-1">
                                                {{ gmdate('H:i:s', (int)$transcript->timestamp) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="flex items-center justify-center h-full text-zinc-500 dark:text-zinc-400">
                                <p>No transcript available</p>
                            </div>
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

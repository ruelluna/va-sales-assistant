<div x-data="{ show: @entangle('show') }" x-show="show" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
    <div class="absolute inset-0 bg-black bg-opacity-70"></div>
    <div class="absolute right-0 top-0 bottom-0 w-[85%] bg-white dark:bg-zinc-900 shadow-2xl overflow-y-auto"
        x-transition:enter="transition ease-in-out duration-500" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-500"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" @click.stop>
        <div
            class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4 flex items-center justify-between z-10 shadow-sm">
            <h2 class="text-xl font-bold">Dialer</h2>
            <button wire:click="closeDialer"
                class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer transition-colors"
                type="button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            @if ($show && $contactId)
                @livewire('dialer.dialer-interface', ['embedded' => true, 'initialContactId' => $contactId, 'shouldMock' => $this->shouldMock], key('dialer-interface-' . $contactId . '-' . ($this->shouldMock ? 'mock' : 'real') . '-' . now()->timestamp))
            @endif
        </div>
    </div>
</div>

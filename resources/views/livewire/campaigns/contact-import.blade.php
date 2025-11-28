<div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Import Contacts</h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-2">Campaign: {{ $campaign->name }}</p>
        </div>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
            <form wire:submit="import">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">CSV File *</label>
                        <input type="file" wire:model="importFile" accept=".csv,.txt" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800">
                        @error('importFile') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            CSV format should include headers: first_name,last_name,phone,email,company,tags,timezone
                        </p>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">CSV Format Example:</h3>
                        <pre class="text-xs text-zinc-700 dark:text-zinc-300">first_name,last_name,phone,email,company,tags,timezone
John,Doe,+1234567890,john@example.com,Acme Corp,"interested,qualified",America/New_York
Jane,Smith,+0987654321,jane@example.com,XYZ Inc,"follow-up",America/Los_Angeles</pre>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 cursor-pointer">
                            Import Contacts
                        </button>
                        <a href="{{ route('campaigns.index') }}" class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-6 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

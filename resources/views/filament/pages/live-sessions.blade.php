<x-filament-panels::page>
    @php $sessions = $this->getSessions(); @endphp

    <div class="text-sm text-gray-500 mb-2">
        {{ $sessions->count() }} active session{{ $sessions->count() === 1 ? '' : 's' }}
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                <tr class="text-left">
                    <th class="px-3 py-2">Username</th>
                    <th class="px-3 py-2">NAS IP</th>
                    <th class="px-3 py-2">Framed IP</th>
                    <th class="px-3 py-2">Started</th>
                    <th class="px-3 py-2 text-right">In (MB)</th>
                    <th class="px-3 py-2 text-right">Out (MB)</th>
                    <th class="px-3 py-2 text-right">Session</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $s)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2 font-mono">{{ $s->username }}</td>
                        <td class="px-3 py-2 font-mono">{{ $s->nasipaddress }}</td>
                        <td class="px-3 py-2 font-mono">{{ $s->framedipaddress ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $s->acctstarttime?->diffForHumans() }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($s->bytes_in_mb, 2) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($s->bytes_out_mb, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ gmdate('H:i:s', $s->acctsessiontime ?? 0) }}</td>
                        <td class="px-3 py-2 text-right">
                            <button
                                wire:click="kick({{ $s->radacctid }})"
                                wire:confirm="Disconnect {{ $s->username }} (RADIUS Disconnect-Message)?"
                                class="text-red-600 hover:text-red-700 text-xs font-medium">
                                Kick
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                            No active sessions yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>

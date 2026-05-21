<x-filament-panels::page>
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                <tr class="text-left">
                    <th class="px-3 py-2">Username</th>
                    <th class="px-3 py-2">NAS IP</th>
                    <th class="px-3 py-2">Framed IP</th>
                    <th class="px-3 py-2">Start</th>
                    <th class="px-3 py-2">Stop</th>
                    <th class="px-3 py-2 text-right">Session</th>
                    <th class="px-3 py-2 text-right">In (MB)</th>
                    <th class="px-3 py-2 text-right">Out (MB)</th>
                    <th class="px-3 py-2">Termination</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getRecent() as $r)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2 font-mono">{{ $r->username }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->nasipaddress }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->framedipaddress ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->acctstarttime?->format('M d H:i') }}</td>
                        <td class="px-3 py-2">{{ $r->acctstoptime?->format('M d H:i') ?? '(active)' }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ gmdate('H:i:s', $r->acctsessiontime ?? 0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($r->bytes_in_mb, 2) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($r->bytes_out_mb, 2) }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $r->acctterminatecause ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-8 text-center text-gray-500">
                            No accounting records yet. FreeRADIUS writes here when sessions start/stop.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>

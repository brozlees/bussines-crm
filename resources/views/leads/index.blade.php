<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Лиды на проверку
            </h2>
            <div class="text-sm text-gray-500">
                Всего: {{ $leads->total() }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            {{-- Фильтры --}}
            <form method="GET" class="mb-4 bg-white p-4 rounded shadow flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm text-gray-600">Статус</label>
                    <select name="status" class="mt-1 border-gray-300 rounded">
                        <option value="">— Активные (draft+approved) —</option>
                        @foreach (['draft','approved','sent','rejected','blocked'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Тип</label>
                    <select name="type" class="mt-1 border-gray-300 rounded">
                        <option value="">— любой —</option>
                        @foreach (['inbound','outbound_cold'] as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Режим</label>
                    <select name="delivery_mode" class="mt-1 border-gray-300 rounded">
                        <option value="">— любой —</option>
                        @foreach (['manual_required','api_allowed','email','sms','telegram'] as $m)
                            <option value="{{ $m }}" @selected(request('delivery_mode') === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>Применить</x-primary-button>
            </form>

            @if ($leads->isEmpty())
                <div class="bg-white p-6 rounded shadow text-gray-600">
                    Лидов пока нет.
                </div>
            @else
                <form method="POST" id="bulk-form">
                    @csrf
                    <div class="mb-3 flex gap-2">
                        <button type="submit" formaction="{{ route('leads.bulk-approve') }}"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Одобрить выбранные</button>
                        <button type="submit" formaction="{{ route('leads.bulk-reject') }}"
                                class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Отклонить выбранные</button>
                    </div>

                    <div class="bg-white rounded shadow overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="p-2 w-8"><input type="checkbox" id="select-all"></th>
                                    <th class="p-2 text-left">Источник</th>
                                    <th class="p-2 text-left">Объявление</th>
                                    <th class="p-2 text-left">Клиент</th>
                                    <th class="p-2 text-left">Релевантность</th>
                                    <th class="p-2 text-left">Режим</th>
                                    <th class="p-2 text-left">Статус</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($leads as $lead)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-2">
                                            <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="lead-checkbox">
                                        </td>
                                        <td class="p-2">{{ $lead->listing?->source?->name }}</td>
                                        <td class="p-2">
                                            <a href="{{ route('leads.show', $lead) }}" class="text-blue-600 hover:underline">
                                                {{ \Illuminate\Support\Str::limit($lead->listing?->title, 50) }}
                                            </a>
                                        </td>
                                        <td class="p-2">
                                            {{ $lead->client?->name ?? '—' }}
                                            @if ($lead->client?->email)
                                                <div class="text-xs text-gray-500">{{ $lead->client->email }}</div>
                                            @endif
                                        </td>
                                        <td class="p-2">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs
                                                {{ $lead->relevance_score >= 70 ? 'bg-green-100 text-green-800' : ($lead->relevance_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $lead->relevance_score }}
                                            </span>
                                        </td>
                                        <td class="p-2">{{ $lead->delivery_mode }}</td>
                                        <td class="p-2">{{ $lead->status }}</td>
                                        <td class="p-2 text-right">
                                            <a href="{{ route('leads.show', $lead) }}" class="text-sm text-gray-600 hover:text-gray-900">Открыть</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="mt-4">{{ $leads->links() }}</div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('select-all')?.addEventListener('change', function (e) {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = e.target.checked);
        });
    </script>
</x-app-layout>
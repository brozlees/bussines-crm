<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Лид #{{ $lead->id }}
            </h2>
            <a href="{{ route('leads.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← К списку</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            {{-- Предупреждение площадки --}}
            @if ($lead->platform_warning)
                <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900">
                    <strong>Внимание:</strong> {{ $lead->platform_warning }}
                </div>
            @endif

            {{-- Объявление --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-semibold mb-2">Чужое объявление</h3>
                <div class="text-sm text-gray-600 mb-1">
                    Площадка: <strong>{{ $lead->listing?->source?->name }}</strong>
                </div>
                <div class="mb-2">
                    <a href="{{ $lead->listing?->url }}" target="_blank" class="text-blue-600 hover:underline">
                        {{ $lead->listing?->title }}
                    </a>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $lead->listing?->description }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    Город: {{ $lead->listing?->city ?? '—' }} ·
                    Категория: {{ $lead->listing?->category ?? '—' }}
                </div>
            </div>

            {{-- Анализ ИИ --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-semibold mb-2">Анализ ИИ</h3>
                <div class="mb-2">
                    Оценка релевантности:
                    <span class="inline-block px-2 py-0.5 rounded text-sm
                        {{ $lead->relevance_score >= 70 ? 'bg-green-100 text-green-800' : ($lead->relevance_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $lead->relevance_score }} / 100
                    </span>
                </div>
                @if ($lead->ai_reasoning)
                    <div class="text-sm text-gray-700 whitespace-pre-line mb-3">{{ $lead->ai_reasoning }}</div>
                @endif
            </div>

            {{-- Предложение --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-semibold mb-2">Сгенерированное предложение</h3>
                @if ($lead->proposal_text)
                    <textarea id="proposal-text" readonly rows="8"
                              class="w-full border-gray-300 rounded text-sm">{{ $lead->proposal_text }}</textarea>
                    <div class="mt-2">
                        <button type="button" onclick="copyProposal()"
                                class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Скопировать текст</button>
                        <span id="copy-status" class="ml-2 text-sm text-green-700"></span>
                    </div>
                @else
                    <div class="text-sm text-gray-500">Текст предложения ещё не сгенерирован.</div>
                @endif
            </div>

            {{-- Действия --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-semibold mb-2">Действия</h3>
                <div class="text-sm text-gray-600 mb-3">
                    Текущий статус: <strong>{{ $lead->status }}</strong> ·
                    Режим: <strong>{{ $lead->delivery_mode }}</strong>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($lead->status === 'draft')
                        <form method="POST" action="{{ route('leads.approve', $lead) }}">
                            @csrf
                            <button class="px-4 py-2 bg-blue-600 text-white rounded">Одобрить</button>
                        </form>
                    @endif

                    @if (in_array($lead->status, ['draft','approved']))
                        <form method="POST" action="{{ route('leads.reject', $lead) }}">
                            @csrf
                            <button class="px-4 py-2 bg-gray-600 text-white rounded">Отклонить</button>
                        </form>
                    @endif

                    @if ($lead->delivery_mode === 'manual_required' && $lead->status === 'approved')
                        <form method="POST" action="{{ route('leads.mark-as-sent', $lead) }}">
                            @csrf
                            <button class="px-4 py-2 bg-green-600 text-white rounded">
                                Отмечено: отправлено вручную
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        function copyProposal() {
            const el = document.getElementById('proposal-text');
            if (!el) return;
            navigator.clipboard.writeText(el.value).then(() => {
                document.getElementById('copy-status').textContent = 'Скопировано';
                setTimeout(() => document.getElementById('copy-status').textContent = '', 2000);
            });
        }
    </script>
</x-app-layout>
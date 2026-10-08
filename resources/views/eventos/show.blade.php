@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <!-- Formularço de envio de Pergunta -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Pergunta</h4>
            <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">Texto da Pergunta</label>

                    <textarea
                        name="texto"
                        id="texto"
                        rows="4"
                        class="w-full rounded-md bg-gray-900 p-3 text-white border
                            @error('texto') border-red-500! @else border-gray-600! @enderror"
                        placeholder="Digite sua dúvida ou comentário para o palestrante..."
                    >{{ old('texto') }}</textarea>

                    @error('texto')
                        <p class="text-red-500 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                <button
                        type="submit"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md! hover:bg-blue-700 transition-colors"
                    >
                        Enviar Pergunta
                    </button>
            </form>
        </div>
    </div>

    <!-- Lista de Perguntas (TICKET #002) -->
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">📋 Perguntas do Evento</h4>
            <span class="text-secondary small">Total no Banco: {{ $evento->perguntas->count() }}</span>
        </div>

        @forelse($perguntas as $pergunta)
            <div class="mb-4 p-4 bg-gray-800 rounded-2xl rounded-tl-none border border-gray-700 shadow-md">
                <p class="text-lg text-white mb-3">
                    {{ $pergunta->texto }}
                </p>

                <div class="flex flex-wrap justify-between gap-2 text-sm text-gray-400">
                    <span>
                        Status:
                        <span class="bg-green-700 text-white px-2 py-1 rounded-md">
                            {{ $pergunta->status }}
                        </span>
                    </span>

                    <span>
                        {{ $pergunta->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-4 rounded-lg bg-gray-800 text-gray-300 text-center">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse

        <!-- TICKET #002: Renderização dos Botões de Paginação -->
        @if(method_exists($perguntas, 'links'))
            <div class="d-flex justify-content-center mt-4">
                
            </div>
        @endif
    </div>
</div>
@endsection

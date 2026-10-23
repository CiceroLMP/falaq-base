@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold">{{ $evento->titulo }}</h2>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="badge bg-primary">
                {{ $evento->participantes->count() }} participante(s)
            </span>

            @auth
                <form
                    action="{{ route('eventos.participar', $evento) }}"
                    method="POST"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-sm {{ $evento->participantes->contains(auth()->user()) ? 'btn-danger' : 'btn-success' }}"
                    >
                        {{ $evento->participantes->contains(auth()->user()) ? 'Cancelar inscrição' : 'Inscrever-se no evento' }}
                    </button>
                </form>
            @endauth
        </div>

        @if(session('status'))
            <div class="alert alert-success mt-3">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua pergunta</h4>

            <form
                action="{{ route('eventos.perguntas.store', $evento->id) }}"
                method="POST"
            >
                @csrf

                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">
                        Texto da pergunta
                    </label>

                    <textarea
                        name="texto"
                        id="texto"
                        rows="4"
                        class="form-control bg-dark text-white border-secondary @error('texto') is-invalid @enderror"
                        placeholder="Digite sua dúvida ou comentário para o palestrante..."
                    >{{ old('texto') }}</textarea>

                    @error('texto')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100 fw-bold"
                >
                    Enviar pergunta
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">📋 Ranking de perguntas</h4>

            <span class="text-secondary small">
                {{ $perguntas->count() }} pergunta(s)
            </span>
        </div>

        @forelse($perguntas as $pergunta)
            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <p class="fs-5 mb-3 text-white">
                        {{ $pergunta->texto }}
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <span class="badge bg-primary fs-6">
                            👍 {{ $pergunta->votos_count }} voto(s)
                        </span>

                        @auth
                            @php
                                $jaVotou = $pergunta->votos->contains(auth()->id());
                            @endphp

                            <form
                                action="{{ route('perguntas.votar', $pergunta) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-sm {{ $jaVotou ? 'btn-success' : 'btn-outline-light' }}"
                                    aria-pressed="{{ $jaVotou ? 'true' : 'false' }}"
                                >
                                    {{ $jaVotou ? '✓ Remover voto' : '👍 Votar' }}
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('login.create') }}"
                                class="text-info small"
                            >
                                Entre para votar
                            </a>
                        @endauth
                    </div>

                    <div class="d-flex justify-content-between align-items-center text-secondary small">
                        <span>
                            Status:
                            <span class="badge bg-success">
                                {{ $pergunta->status }}
                            </span>
                        </span>

                        <span>
                            {{ $pergunta->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-dark text-center p-4">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse
    </div>
</div>
@endsection
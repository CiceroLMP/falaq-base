<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function criarPergunta(
        string $texto = 'Como funciona o sistema de votos?',
        ?Evento $evento = null
    ): Pergunta {
        if ($evento === null) {
            $evento = Evento::create([
                'titulo' => 'Evento de teste',
                'user_id' => User::factory()->create()->id,
            ]);
        }

        return Pergunta::create([
            'evento_id' => $evento->id,
            'texto' => $texto,
            'status' => 'pendente',
            'is_public' => true,
        ]);
    }

    public function test_visitante_nao_pode_votar(): void
    {
        $pergunta = $this->criarPergunta();

        $this->post(route('perguntas.votar', $pergunta))
            ->assertRedirect(route('login.create'));

        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_usuario_autenticado_pode_votar(): void
    {
        $usuario = User::factory()->create();
        $pergunta = $this->criarPergunta();

        $this->actingAs($usuario)
            ->from(route('eventos.show', $pergunta->evento_id))
            ->post(route('perguntas.votar', $pergunta))
            ->assertRedirect(route('eventos.show', $pergunta->evento_id))
            ->assertSessionHas('sucesso', 'Voto registrado com sucesso!');

        $this->assertDatabaseHas('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $usuario->id,
        ]);

        $this->assertTrue(
            $pergunta->votos()->whereKey($usuario->id)->exists()
        );

        $this->assertTrue(
            $usuario->perguntasVotadas()->whereKey($pergunta->id)->exists()
        );

        $voto = $pergunta->votos()->first()->pivot;

        $this->assertNotNull($voto->created_at);
        $this->assertNotNull($voto->updated_at);
    }

    public function test_segundo_clique_remove_o_voto(): void
    {
        $usuario = User::factory()->create();
        $pergunta = $this->criarPergunta();

        $this->actingAs($usuario);

        $this->post(route('perguntas.votar', $pergunta));

        $this->post(route('perguntas.votar', $pergunta))
            ->assertSessionHas('sucesso', 'Voto removido com sucesso!');

        $this->assertDatabaseMissing('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $usuario->id,
        ]);
    }

    public function test_banco_impede_voto_duplicado(): void
    {
        $usuario = User::factory()->create();
        $pergunta = $this->criarPergunta();

        $pergunta->votos()->attach($usuario->id);

        $this->expectException(QueryException::class);

        $pergunta->votos()->attach($usuario->id);
    }

    public function test_ranking_ordena_por_votos_e_filtra_o_evento(): void
    {
        $menosVotada = $this->criarPergunta('Pergunta com menos votos');

        $maisVotada = $this->criarPergunta(
            'Pergunta com mais votos',
            $menosVotada->evento
        );

        $outroEvento = $this->criarPergunta('Pergunta de outro evento');

        $usuarios = User::factory()->count(3)->create();

        $menosVotada->votos()->attach($usuarios->first()->id);
        $maisVotada->votos()->attach($usuarios->modelKeys());
        $outroEvento->votos()->attach($usuarios->modelKeys());

        $this->get(route('eventos.show', $menosVotada->evento_id))
            ->assertOk()
            ->assertSeeInOrder([
                'Pergunta com mais votos',
                'Pergunta com menos votos',
            ])
            ->assertDontSee('Pergunta de outro evento')
            ->assertViewHas('perguntas', function ($perguntas) use (
                $maisVotada,
                $menosVotada
            ) {
                return $perguntas->pluck('id')->all() === [
                    $maisVotada->id,
                    $menosVotada->id,
                ] && $perguntas->pluck('votos_count')->all() === [3, 1];
            });
    }

    public function test_tela_indica_quando_usuario_ja_votou(): void
    {
        $usuario = User::factory()->create();
        $pergunta = $this->criarPergunta();

        $this->actingAs($usuario)
            ->get(route('eventos.show', $pergunta->evento_id))
            ->assertOk()
            ->assertSee('aria-pressed="false"', false)
            ->assertDontSee('Remover voto');

        $pergunta->votos()->attach($usuario->id);

        $this->get(route('eventos.show', $pergunta->evento_id))
            ->assertOk()
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('Remover voto');
    }

    public function test_excluir_pergunta_ou_usuario_remove_seus_votos(): void
    {
        $usuario = User::factory()->create();

        $primeira = $this->criarPergunta();
        $segunda = $this->criarPergunta(
            'Outra pergunta para testar',
            $primeira->evento
        );

        $primeira->votos()->attach($usuario->id);
        $segunda->votos()->attach($usuario->id);

        $primeira->delete();

        $this->assertDatabaseMissing('pergunta_user', [
            'pergunta_id' => $primeira->id,
        ]);

        $this->assertDatabaseHas('pergunta_user', [
            'pergunta_id' => $segunda->id,
            'user_id' => $usuario->id,
        ]);

        $usuario->delete();

        $this->assertDatabaseCount('pergunta_user', 0);
    }
}
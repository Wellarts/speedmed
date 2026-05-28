<?php

namespace App\Filament\Resources\AtendimentoClinicoNewResource\Pages;

use App\Filament\Resources\AtendimentoClinicoNewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAtendimentoClinicoNew extends CreateRecord
{
    protected static string $resource = AtendimentoClinicoNewResource::class;

    protected static ?string $title = 'Novo Atendimento Clínico';

    /**
     * Intercepta o submit do formulário.
     *
     * - Se não há gravação/processamento em curso: salva normalmente.
     * - Se há: despacha evento para o JS confirmar o estado; o JS devolve
     *   'audio-check-result' informando se precisa ou não de espera.
     */
    public function create(bool $another = false): void
    {
        $this->dispatch('check-audio-state', another: $another);
    }

    /**
     * Recebe o resultado da verificação do JS.
     *
     * @param  bool  $another      Parâmetro original do Filament.
     * @param  bool  $needsWait    true se havia gravação/processamento em curso.
     */
    #[\Livewire\Attributes\On('audio-check-result')]
    public function handleAudioCheckResult(bool $another = false, bool $needsWait = false): void
    {
        if (! $needsWait) {
            // Sem áudio pendente — salva direto
            parent::create($another);
            return;
        }

        // Exibe modal de confirmação ANTES de processar
        $this->replaceMountedAction('confirmAudioSave', ['another' => $another]);
    }

    /**
     * Ação de confirmação exibida quando há áudio em curso.
     * Ao confirmar, o JS é acionado para parar/aguardar e então salvar.
     */
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('confirmAudioSave')
                ->label('Salvar atendimento')
                ->modalHeading('Gravação em andamento. Ao confirmar, a gravação será encerrada e o áudio transcrito automaticamente antes de salvar o atendimento. Aguarde até a conclusão.')
                ->modalDescription('Ao confirmar, a gravação será encerrada e o áudio transcrito automaticamente antes de salvar o atendimento. Aguarde até a conclusão.')
                ->modalSubmitActionLabel('Confirmar e aguardar')
                ->modalCancelActionLabel('Cancelar')
                ->modalIcon('heroicon-o-microphone')
                ->modalIconColor('warning')
                ->requiresConfirmation()
                ->action(function (array $arguments) {
                    $another = $arguments['another'] ?? false;
                    // Aciona o JS para parar a gravação e aguardar o processamento
                    $this->dispatch('check-audio-before-save', another: $another);
                }),
        ];
    }

    /**
     * Chamado pelo JS após a gravação ser parada e o áudio totalmente processado.
     */
    #[\Livewire\Attributes\On('audio-ready')]
    public function proceedCreate(bool $another = false): void
    {
        parent::create($another);
    }
}
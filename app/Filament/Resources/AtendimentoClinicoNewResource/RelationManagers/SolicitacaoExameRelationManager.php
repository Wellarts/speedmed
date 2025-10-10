<?php

namespace App\Filament\Resources\AtendimentoClinicoNewResource\RelationManagers;

use App\Models\Exame;
use App\Models\GrupoExame;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitacaoExameRelationManager extends RelationManager
{
    protected static string $relationship = 'SolicitacaoExames';

    protected static ?string $title = 'Solicitação de Exames';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('atendimento_clinico_id')
                    ->default((function ($livewire): int {
                        return $livewire->ownerRecord->id;
                    })),
                Forms\Components\Select::make('exames')
                    ->label('Exames Solicitados')
                    ->relationship('exames', 'nome')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome . ' (' . $record->tipo . ')')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $livewire) {
                        if (!empty($state)) {
                            $exames = Exame::whereIn('id', $state)->pluck('nome')->map(function ($nome) {
                                return $nome . ':';
                            })->implode("\n");

                            $livewire->ownerRecord->resultado_exames = $exames;
                            $livewire->ownerRecord->save();
                        }
                    })
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->columnSpanFull()
                    ->createOptionForm([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nome')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('descricao')
                                    ->label('Descrição')
                                    ->maxLength(255),
                                Forms\Components\Select::make('tipo')
                                    ->label('Tipo')
                                    ->options([
                                        '1' => 'Laboratorial',
                                        '2' => 'Imagem',
                                    ])
                                    ->required()
                                    ->default('1'),
                            ]),
                    ]),




            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('atendimento_clinico_id')
            ->columns([
                Tables\Columns\TextColumn::make('exames.nome')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('exames.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->alignCenter()
                    ->listWithLineBreaks()

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Solicitar Exames')
                    ->modalHeading('Solicitar Exames')
                    ->icon('heroicon-o-plus')
                    ->createAnother(false)
                    ->visible(fn($livewire) => $livewire->ownerRecord->solicitacaoExames()->count() == 0),
                // No arquivo SolicitacaoExameRelationManager.php

                // No arquivo SolicitacaoExameRelationManager.php

                Tables\Actions\Action::make('grupoExames')
                    ->label('Solicitar Exames por Grupo')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('grupo_id')
                            ->label('Grupo de Exames')
                            ->options(GrupoExame::pluck('nome', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Quando um grupo é selecionado, mostra os exames do grupo
                                if (!empty($state)) {
                                    $grupo = GrupoExame::find($state);
                                    $exameIds = $grupo->exame_id ?? [];

                                    // Busca os nomes dos exames para mostrar como preview
                                    $examesPreview = Exame::whereIn('id', $exameIds)
                                        ->get()
                                        ->map(function ($exame) {
                                            return $exame->nome . ' (' . ($exame->tipo == '1' ? 'Laboratorial' : 'Imagem') . ')';
                                        })
                                        ->implode("\n");

                                    $set('exames_preview', $examesPreview);
                                } else {
                                    $set('exames_preview', '');
                                }
                            }),

                        Forms\Components\Textarea::make('exames_preview')
                            ->label('Exames do Grupo')
                            ->disabled()
                            ->autosize()
                            ->helperText('Estes exames serão adicionados ao atendimento'),
                    ])
                    ->action(function (array $data, $livewire) {
                        $grupo = GrupoExame::find($data['grupo_id']);
                        $exameIds = $grupo->exame_id ?? [];

                        if (!empty($exameIds)) {
                            $record = $livewire->ownerRecord;

                            // CORREÇÃO: Usar o relacionamento através de SolicitacaoExame
                            // Primeiro, verifica se já existe uma solicitação de exame
                            $solicitacaoExame = $record->SolicitacaoExames()->first();

                            if (!$solicitacaoExame) {
                                // Se não existe, cria uma nova solicitação
                                $solicitacaoExame = $record->SolicitacaoExames()->create([
                                    'atendimento_clinico_id' => $record->id,
                                ]);
                            }

                            // Adiciona os exames através do relacionamento correto
                            $solicitacaoExame->exames()->syncWithoutDetaching($exameIds);

                            // Atualiza o campo resultado_exames no atendimento clínico
                            $examesNomes = Exame::whereIn('id', $exameIds)
                                ->pluck('nome')
                                ->map(function ($nome) {
                                    return $nome . ':';
                                })
                                ->implode("\n");

                            // Mantém os exames existentes e adiciona os novos
                            $resultadoAtual = $record->resultado_exames ?? '';
                            $novoResultado = trim($resultadoAtual . "\n" . $examesNomes);
                            $record->resultado_exames = $novoResultado;
                            $record->save();

                            // Mostra mensagem de sucesso
                            Notification::make()
                                ->title('Exames adicionados com sucesso!')
                                ->success()
                                ->send();
                        }
                    })
                    ->modalDescription('Selecione um grupo de exames para adicionar todos os exames do grupo ao atendimento.'),
                Tables\Actions\Action::make('print')
                    ->label('Imprimir Exames')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn($livewire) => route('documentos.solicitacaoExames.print', $livewire->ownerRecord->id))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

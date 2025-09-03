<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoResource\Pages;
use App\Filament\Resources\DocumentoResource\RelationManagers;
use App\Models\Documento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Atendimentos';

    protected static ?string $navigationLabel = 'Atestados/Outros';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                            ->label('Tipo de Documento')
                            ->options([
                                '1' => 'Declaração de Comparecimento',
                                '2' => 'Atestado',
                                '3' => 'Receituário',
                                '4' => 'Encaminhamento',
                                '5' => 'Exames',
                                '6' => 'Orientações',
                                '7' => 'Outros',
                                '8' => 'Laudo',
                            ])
                            ->default('2')
                            ->required(),
                    
                Forms\Components\Select::make('paciente_id')
                    ->relationship('paciente', 'nome')
                    ->required(),
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'nome')
                    ->label('Médico Responsável')
                    
                    ->default(auth()->user()->id) // Associa o médico logado
                    ->required(),
                Forms\Components\DateTimePicker::make('data_hora')
                    ->label('Data e Hora')
                     ->default(now()->format('Y-m-d H:i:s'))                                
                    ->required(),
                Forms\Components\MarkdownEditor::make('descricao')
                    ->columnSpanFull()
                    ->disableToolbarButtons([
                            'attachFiles'
                        ])                    
                    ->label('Descrição'),
                        
                    ]);
                   
           
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            '1' => 'Declaração de Comparecimento',
                            '2' => 'Atestado',
                            '3' => 'Receituário',
                            '4' => 'Encaminhamento',
                            '5' => 'Exames',
                            '6' => 'Orientações',
                            '7' => 'Outros',
                            default => 'Desconhecido',
                        };
                    })
                    ->searchable()
                    ->sortable(),                    
                Tables\Columns\TextColumn::make('paciente.nome')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('medico.nome')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_hora')
                    ->label('Data e Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('Gerar PDF')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Documento $record) => route('documentos.documento.print', $record))
                    ->openUrlInNewTab(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocumentos::route('/'),
        ];
    }
}

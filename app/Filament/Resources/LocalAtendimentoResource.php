<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocalAtendimentoResource\Pages;
use App\Filament\Resources\LocalAtendimentoResource\RelationManagers;
use App\Models\LocalAtendimento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocalAtendimentoResource extends Resource
{
    protected static ?string $model = LocalAtendimento::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Locais de Atendimento';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Local de Atendimento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required()
                            ->placeHolder('Ex: Clínica Central')
                            ->columnSpanFull(),                    
                        Forms\Components\TextInput::make('endereco')
                            ->label('Endereço Completo')
                            ->placeHolder('Ex: Rua das Flores, 123, Centro, Cidade - Estado, CEP')
                            ->columnSpanFull(),                    
                        Forms\Components\TextInput::make('telefone')
                            ->mask('(99) 99999-9999'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->placeHolder('Ex: contato@clinica.com'),
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('endereco')->label('Endereço')->limit(50),
                Tables\Columns\TextColumn::make('telefone')->label('Telefone'),
                Tables\Columns\TextColumn::make('email')->label('Email')->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),                    
                Tables\Actions\DeleteAction::make(),                    
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Excluir Selecionados'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLocalAtendimentos::route('/'),
        ];
    }
}

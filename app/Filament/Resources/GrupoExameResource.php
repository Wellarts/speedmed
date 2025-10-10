<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrupoExameResource\Pages;
use App\Filament\Resources\GrupoExameResource\RelationManagers;
use App\Models\GrupoExame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Livewire\wrap;

class GrupoExameResource extends Resource
{
    protected static ?string $model = GrupoExame::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $navigationLabel = 'Grupos de Exames'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('exame_id')
                    ->label('Exames')
                    ->relationship('listExames', 'nome')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->searchable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGrupoExames::route('/'),
        ];
    }
}

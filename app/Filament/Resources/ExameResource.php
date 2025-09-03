<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExameResource\Pages;
use App\Filament\Resources\ExameResource\RelationManagers;
use App\Models\Exame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;

class ExameResource extends Resource
{
    protected static ?string $model = Exame::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $navigationLabel = 'Exames';



    public static function form(Form $form): Form
    {
        return $form
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
                        'Laboratorial' => 'Laboratorial',
                        'Imagem' => 'Imagem',
                        
                    ])
                    ->required()
                    ->default('1'),
                   

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
            ])
            ->filters([
                Filter::make('Laboratorial')
                    ->query(fn(Builder $query): Builder => $query->where('tipo', 'Laboratorial')),
                Filter::make('Imagem')
                    ->query(fn(Builder $query): Builder => $query->where('tipo', 'Imagem')),

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
            'index' => Pages\ManageExames::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\GrupoExameResource\Pages;

use App\Filament\Resources\GrupoExameResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGrupoExames extends ManageRecords
{
    protected static string $resource = GrupoExameResource::class;

    protected static ?string $title = 'Grupos de Exames';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Grupo de Exames')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Novo Grupo de Exames'),
        ];
    }
}

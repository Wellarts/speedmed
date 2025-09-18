<?php

namespace App\Filament\Resources\LocalAtendimentoResource\Pages;

use App\Filament\Resources\LocalAtendimentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLocalAtendimentos extends ManageRecords
{
    protected static string $resource = LocalAtendimentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar Local de Atendimento')
                ->icon('heroicon-o-plus')
                ->modalHeading('Adicionar Novo Local de Atendimento'),
        ];
    }
}

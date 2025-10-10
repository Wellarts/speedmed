<?php

namespace App\Filament\Widgets;

use App\Models\AtendimentoClinico;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AtendimentoMesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Atendimento por Mês';

    protected function getData(): array
    {
        // Dados para todos os atendimentos
        $dataTotal = Trend::query(AtendimentoClinico::query())
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count('id');

        // Dados para atendimentos com status = 1
        $dataStatus1 = Trend::query(AtendimentoClinico::where('status', 1))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()            
            ->count('id');

        // Dados para atendimentos com status = 2
        $dataStatus2 = Trend::query(AtendimentoClinico::where('status', 2))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()            
            ->count('id');

        return [
            'datasets' => [
                [
                    'label' => 'Total de Atendimentos',
                    'data' => $dataTotal->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#3498db',
                    'backgroundColor' => 'rgba(52, 152, 219, 0.1)',
                ],
                [
                    'label' => 'Atendimentos Iniciados',
                    'data' => $dataStatus1->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#27ae60',
                    'backgroundColor' => 'rgba(39, 174, 96, 0.1)',
                ],
                [
                    'label' => 'Atendimentos Finalizados',
                    'data' => $dataStatus2->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#e74c3c',
                    'backgroundColor' => 'rgba(231, 76, 60, 0.1)',
                ],
            ],
            'labels' => $dataTotal->map(function (TrendValue $value) {
                return \Carbon\Carbon::parse($value->date)->format('m/Y');
            }),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
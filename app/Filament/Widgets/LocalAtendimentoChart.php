<?php

namespace App\Filament\Widgets;

use App\Models\AtendimentoClinico;
use App\Models\LocalAtendimento;
use Filament\Widgets\ChartWidget;

class LocalAtendimentoChart extends ChartWidget
{
    protected static ?string $heading = 'Atendimentos por Local';

    protected function getData(): array
    {
        // Buscar os locais de atendimento e contar os atendimentos de cada um
        $locais = LocalAtendimento::withCount('atendimentos')->get();
        
        $labels = [];
        $data = [];

        foreach ($locais as $local) {
            $labels[] = $local->nome;
            $data[] = $local->atendimentos_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade de Atendimentos',
                    'data' => $data,
                    'backgroundColor' => $this->generateColors(count($data)),
                    'borderColor' => '#3498db',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Gera cores aleatórias para o gráfico
     */
    private function generateColors(int $count): array
    {
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = '#' . substr(md5(rand()), 0, 6);
        }
        return $colors;
    }

    /**
     * Opcional: Tamanho do widget
     */
    protected function getColumns(): int
    {
        return 2;
    }
}
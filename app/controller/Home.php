<?php

declare(strict_types=1);

namespace App\Controller;

use app\database\DB;


final class Home extends Base
{
    public function home($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('home'), [
                'titulo' => 'Início',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function charts($request, $response)
    {
        try {
            $vendasMesAno = DB::select('ano', 'mes', 'total_unidades', 'total_valor')
                ->from('vw_vendas_mes_ano')
                ->orderBy('ano', 'ASC')
                ->orderBy('mes', 'ASC')
                ->fetchAllAssociative();

            $abc = DB::select('nome', 'valor', 'classe_valor', 'unidades', 'pct_valor')
                ->from('vw_curva_abc_produtos')
                ->orderBy('valor', 'DESC')
                ->fetchAllAssociative();

            // Série de vendas: mês x valor (ou unidades, se preferir)
            // Vamos construir 12 meses do ano atual.
            $now = new \DateTimeImmutable('now');
            $year = (int) $now->format('Y');

            $months = range(1, 12);
            $monthLabels = [];
            $monthValues = [];

            foreach ($months as $m) {
                $dt = $now->setDate($year, $m, 1);
                $label = $dt->format('M'); // ex: Jun
                // Padroniza para pt-BR abreviado — sem Intl depende do SO, então usamos map fixo.
                $monthLabels[] = $this->monthLabelPt($m);
                $found = array_values(array_filter($vendasMesAno, static fn($r) => (int) $r['ano'] === $year && (int) $r['mes'] === $m));
                $monthValues[] = $found[0]['total_valor'] ?? 0;
            }

            // Pie: ABC por classe_valor (A/B/C) somando VALOR
            $pie = ['A' => 0, 'B' => 0, 'C' => 0];
            foreach ($abc as $row) {
                $classe = (string) ($row['classe_valor'] ?? '');
                $classe = strtoupper($classe);
                if (!isset($pie[$classe])) continue;
                $pie[$classe] += (float) ($row['valor'] ?? 0);
            }

            $pieData = [
                ['value' => $pie['A'], 'name' => 'Classe A'],
                ['value' => $pie['B'], 'name' => 'Classe B'],
                ['value' => $pie['C'], 'name' => 'Classe C'],
            ];

            return $this->json($response, [
                'status' => true,
                'year' => $year,
                'vendas' => [
                    'months' => $monthLabels,
                    'values' => $monthValues,
                ],
                'abc' => [
                    'pieData' => $pieData,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    private function monthLabelPt(int $month): string
    {
        return match ($month) {
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
            default => (string) $month,
        };
    }
}


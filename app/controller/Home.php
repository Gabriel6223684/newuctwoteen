<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database\Connection;

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
            // 1. Usa o QueryBuilder do seu DB.php apontando para a sua View
            // O método select() já traz o objeto preparado do Phinx
            $vendasMesAno = Connection::select()
                ->from('vw_vendas_mes_ano')
                ->orderBy('ano', 'ASC')
                ->addOrderBy('mes', 'ASC')
                ->execute()
                ->fetchAll(\PDO::FETCH_ASSOC);

            // Normalizar as chaves do banco para minúsculo
            $vendasMesAno = array_map(function ($row) {
                return array_change_key_case($row, CASE_LOWER);
            }, $vendasMesAno);

            // Definir o ano atual (2026)
            $now = new \DateTimeImmutable('now');
            $year = (int) $now->format('Y');

            // Fallback de ano caso não haja vendas no ano corrente
            if (!empty($vendasMesAno)) {
                $anosDisponiveis = array_unique(array_column($vendasMesAno, 'ano'));
                $anosDisponiveis = array_filter($anosDisponiveis, fn($v) => (int)$v > 0);

                if (!empty($anosDisponiveis) && !in_array($year, $anosDisponiveis)) {
                    $year = (int) max($anosDisponiveis);
                }
            }

            $months = range(1, 12);
            $monthLabels = [];
            $monthValues = [];

            // Monta o array linear de 12 meses para o Gráfico de Barras
            foreach ($months as $m) {
                $monthLabels[] = $this->monthLabelPt($m);

                $found = array_values(array_filter(
                    $vendasMesAno,
                    static fn($r) => (int)($r['ano'] ?? 0) === $year && (int)($r['mes'] ?? 0) === $m
                ));

                if (isset($found[0])) {
                    $valor = (float)($found[0]['total_valor'] ?? 0);
                    if ($valor === 0.0) {
                        $valor = (float)($found[0]['total_unidades'] ?? 0);
                    }
                    $monthValues[] = $valor;
                } else {
                    $monthValues[] = 0;
                }
            }

            // Monta o array de fatias para o Gráfico Redondo com os mesmos dados de meses
            $pieData = [];
            foreach ($months as $m) {
                $nomeMes = $this->monthLabelPt($m);
                $valorMes = $monthValues[$m - 1] ?? 0.0;

                // Só joga no gráfico redondo os meses que de fato tiveram faturamento
                if ($valorMes > 0.0) {
                    $pieData[] = [
                        'value' => $valorMes,
                        'name'  => $nomeMes
                    ];
                }
            }

            // Fallback para o gráfico redondo não quebrar vazio se o ano não tiver vendas
            if (empty($pieData)) {
                $pieData = [
                    ['value' => 0, 'name' => 'Sem Vendas']
                ];
            }

            $payload = json_encode([
                'status' => true,
                'year'   => $year,
                'vendas' => [
                    'months' => $monthLabels,
                    'values' => $monthValues,
                ],
                'abc' => [
                    'pieData' => $pieData,
                ],
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Throwable $e) {
            $payload = json_encode([
                'status' => false,
                'msg' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile()
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
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

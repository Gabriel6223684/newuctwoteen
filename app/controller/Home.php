<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database\Connection;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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

    public function charts(Request $request, Response $response): Response
    {
        try {
            $pdo = Connection::get();

            // =========================
            // DADOS DA VIEW (NOVO)
            // =========================
            $stmt = $pdo->prepare("
            SELECT *
            FROM vw_vendas_mes_ano
            WHERE ano = 2026
            ORDER BY mes
        ");
            $stmt->execute();
            $dados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $mesesLabels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

            $valoresVendas = array_fill(0, 12, 0);
            $valoresFaturamento = array_fill(0, 12, 0);

            foreach ($dados as $row) {
                $i = ((int)$row['mes']) - 1;

                $valoresVendas[$i] = (int) $row['total_vendas'];
                $valoresFaturamento[$i] = (float) $row['total_valor'];
            }

            // ================================
            // PIZZA (mantém igual)
            // ================================
            $stmtPie = $pdo->prepare("
            SELECT
                p.nome AS produto,
                SUM(si.unidades) AS total_vendido
            FROM sale_items si
            INNER JOIN products p ON p.id = si.product_id
            INNER JOIN sales s ON s.id = si.sale_id
            WHERE EXTRACT(YEAR FROM s.sale_date) = 2026
            GROUP BY p.id, p.nome
            ORDER BY total_vendido DESC
            LIMIT 3
        ");
            $stmtPie->execute();

            $pieData = [];

            foreach ($stmtPie->fetchAll(\PDO::FETCH_ASSOC) as $item) {
                $pieData[] = [
                    'name' => $item['produto'],
                    'value' => (int) $item['total_vendido']
                ];
            }

            // ================================
            // PAYLOAD (NOVO CAMPO: FATURAMENTO)
            // ================================
            $payload = [
                "status" => true,
                "year" => 2026,
                "vendas" => [
                    "months" => $mesesLabels,
                    "values" => $valoresVendas
                ],
                "faturamento" => [
                    "months" => $mesesLabels,
                    "values" => $valoresFaturamento
                ],
                "abc" => [
                    "pieData" => $pieData
                ]
            ];

            $response->getBody()->write(json_encode($payload));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "msg" => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
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

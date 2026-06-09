<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVwCurvaAbcProdutos extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<'SQL'
CREATE OR REPLACE VIEW vw_curva_abc_produtos AS
WITH base AS (
    SELECT
        p.nome,
        SUM(si.unidades) AS unidades,
        SUM(si.unidades * si.valor_unitario) AS valor
    FROM products p
    JOIN sale_items si
      ON si.product_id = p.id
    GROUP BY p.nome
),
calc AS (
    SELECT
        nome,
        unidades,
        valor,
        SUM(valor) OVER () AS total_valor,
        SUM(valor) OVER (ORDER BY valor DESC) AS valor_acumulado
    FROM base
),
final AS (
    SELECT
        nome,
        unidades,
        valor,
        CASE
            WHEN total_valor = 0 THEN 0
            ELSE (valor_acumulado / total_valor) * 100
        END AS pct_valor,
        CASE
            WHEN total_valor = 0 THEN 'C'
            WHEN (valor_acumulado / total_valor) * 100 <= 80 THEN 'A'
            WHEN (valor_acumulado / total_valor) * 100 <= 95 THEN 'B'
            ELSE 'C'
        END AS classe_valor
    FROM calc
)
SELECT
    nome,
    valor::numeric(14,2) AS valor,
    classe_valor,
    unidades::bigint AS unidades,
    pct_valor::numeric(14,2) AS pct_valor
FROM final
ORDER BY valor DESC;
SQL);
    }

    public function down(): void
    {
        $this->execute('DROP VIEW IF EXISTS vw_curva_abc_produtos');
    }
}


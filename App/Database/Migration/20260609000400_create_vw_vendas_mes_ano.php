<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVwVendasMesAno extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<'SQL'
CREATE OR REPLACE VIEW vw_vendas_mes_ano AS
SELECT
    v.ano,
    v.mes,
    v.total_vendas,
    COALESCE(i.total_unidades, 0) AS total_unidades,
    COALESCE(i.total_valor, 0)::numeric(14,2) AS total_valor

FROM (
    SELECT
        EXTRACT(YEAR FROM sale_date)::int AS ano,
        EXTRACT(MONTH FROM sale_date)::int AS mes,
        COUNT(*) AS total_vendas
    FROM sales
    GROUP BY
        EXTRACT(YEAR FROM sale_date),
        EXTRACT(MONTH FROM sale_date)
) v

LEFT JOIN (
    SELECT
        EXTRACT(YEAR FROM s.sale_date)::int AS ano,
        EXTRACT(MONTH FROM s.sale_date)::int AS mes,
        SUM(si.unidades) AS total_unidades,
        SUM(si.unidades * si.valor_unitario)::numeric(14,2) AS total_valor
    FROM sales s
    JOIN sale_items si ON si.sale_id = s.id
    GROUP BY
        EXTRACT(YEAR FROM s.sale_date),
        EXTRACT(MONTH FROM s.sale_date)
) i
ON i.ano = v.ano AND i.mes = v.mes;
SQL);
    }

    public function down(): void
    {
        $this->execute('DROP VIEW IF EXISTS vw_vendas_mes_ano');
    }
}

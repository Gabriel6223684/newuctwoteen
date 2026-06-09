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
    EXTRACT(YEAR FROM s.sale_date)::int AS ano,
    EXTRACT(MONTH FROM s.sale_date)::int AS mes,
    SUM(si.unidades)::bigint AS total_unidades,
    COALESCE(SUM(si.unidades * si.valor_unitario), 0)::numeric(14,2) AS total_valor
FROM sales s
JOIN sale_items si
  ON si.sale_id = s.id
GROUP BY
    EXTRACT(YEAR FROM s.sale_date)::int,
    EXTRACT(MONTH FROM s.sale_date)::int;
SQL);
    }

    public function down(): void
    {
        $this->execute('DROP VIEW IF EXISTS vw_vendas_mes_ano');
    }
}


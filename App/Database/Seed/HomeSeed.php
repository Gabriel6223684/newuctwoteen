<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class HomeSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        // Para comandos diretos de limpeza (DDL), usamos o PDO nativo
        $conn = $this->getAdapter()->getConnection();
        $year = (int) (new \DateTimeImmutable('now'))->format('Y');

        // Limpa tabelas usando o PDO correto (exec)
        $conn->exec('TRUNCATE TABLE sale_items RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE sales RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE products RESTART IDENTITY CASCADE');

        // Cards do home
        $conn->exec('TRUNCATE TABLE contact RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE customer RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE suppliers RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE enterprises RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE users RESTART IDENTITY CASCADE');
        $conn->exec('TRUNCATE TABLE countries RESTART IDENTITY CASCADE');

        // --- Countries ---
        $countries = [
            ['Brasil', 'BRA', true],
            ['Argentina', 'ARG', true],
            ['Portugal', 'PRT', true],
            ['Espanha', 'ESP', true],
            ['Estados Unidos', 'USA', true],
            ['França', 'FRA', false],
        ];

        $countriesTable = $this->table('countries');
        foreach ($countries as [$nome, $iso, $ativo]) {
            $countriesTable->insert([
                'nome' => $nome,
                'codigo_iso' => $iso,
                'ativo' => $ativo,
            ]);
        }
        $countriesTable->saveData();

        // --- Customers ---
        $customers = [
            ['ACME Serviços LTDA', '00.111.222/0001-33'],
            ['Beta Logística', '22.333.444/0001-55'],
            ['Gamma Comércio', '11.222.333/0001-44'],
            ['Delta Indústria', '44.555.666/0001-77'],
            ['Epsilon Marketing', '55.666.777/0001-88'],
            ['Zeta Tecnologia', '66.777.888/0001-99'],
        ];

        $customerTable = $this->table('customer');
        foreach ($customers as [$nomeFantasia, $cpfCnpj]) {
            $customerTable->insert([
                'nome_fantasia' => $nomeFantasia,
                'cpf_cnpj' => $cpfCnpj,
            ]);
        }
        $customerTable->saveData();

        // --- Users + contact ---
        $users = [
            ['Ana', 'Souza', '123.456.789-00', 'senha123', true, true, 'ana.souza@example.com'],
            ['Bruno', 'Lima', '987.654.321-00', 'senha123', true, false, 'bruno.lima@example.com'],
            ['Carla', 'Nogueira', '111.222.333-44', 'senha123', false, false, 'carla.n@example.com'],
            ['Diego', 'Barros', '555.666.777-88', 'senha123', true, false, 'diego.b@example.com'],
        ];

        foreach ($users as [$nome, $sobrenome, $cpf, $senha, $ativo, $adm, $email]) {
            $usersTable = $this->table('users');
            $usersTable->insert([
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'cpf' => $cpf,
                'rg' => '',
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'ativo' => $ativo,
                'administrador' => $adm,
            ])->saveData();

            $userId = (int) $conn->query('SELECT MAX(id) FROM users')->fetchColumn();

            $this->table('contact')->insert([
                'id_usuario' => $userId,
                'tipo' => 'EMAIL',
                'contato' => $email,
            ])->saveData();
        }

        // --- Enterprises + Suppliers ---
        $enterprises = [
            ['InovaTech', '12.345.678/0001-90', true],
            ['Solux', '98.765.432/0001-10', true],
            ['Vento Norte', '45.678.901/0001-12', false],
        ];

        foreach ($enterprises as [$nome, $cnpj, $ativo]) {
            $this->table('enterprises')->insert([
                'nome' => $nome,
                'cnpj' => $cnpj,
                'ativo' => $ativo,
            ])->saveData();

            $entId = (int) $conn->query('SELECT MAX(id) FROM enterprises')->fetchColumn();

            $this->table('suppliers')->insert([
                'enterprise_id' => $entId,
                'nome_fantasia' => $nome . ' Fornecedor',
                'cpf_cnpj' => $cnpj,
                'ativo' => true,
            ])->saveData();
        }

        // --- Products ---
        $products = [
            ['Produto A', true],
            ['Produto B', true],
            ['Produto C', true],
            ['Produto D', true],
            ['Produto E', false],
            ['Produto F', true],
            ['Produto G', true],
            ['Produto H', true],
        ];

        $productsTable = $this->table('products');
        foreach ($products as [$nome, $ativo]) {
            $productsTable->insert([
                'nome' => $nome,
                'ativo' => $ativo,
            ]);
        }
        $productsTable->saveData();

        // --- LOOP DE VENDAS, PRODUTOS E ITENS ---
        // 1. Busca os produtos que acabamos de inserir
        $productIds = $conn->query('SELECT id, nome FROM products ORDER BY id ASC')->fetchAll(\PDO::FETCH_ASSOC);

        $weights = [
            'Produto A' => 1.8,
            'Produto B' => 1.6,
            'Produto C' => 1.4,
            'Produto D' => 1.2,
            'Produto E' => 0.6,
            'Produto F' => 1.0,
            'Produto G' => 0.9,
            'Produto H' => 0.8,
        ];

        // 2. Loop para criar as vendas (uma para cada mês do ano)
        for ($m = 1; $m <= 12; $m++) {
            $saleDate = sprintf('%04d-%02d-15', $year, $m);

            // Insere a Venda principal
            $this->table('sales')->insert([
                'sale_date' => $saleDate,
            ])->saveData();

            // Pega o ID da venda que acabou de ser criada
            $saleId = (int) $conn->query('SELECT MAX(id) FROM sales')->fetchColumn();

            // 3. Loop dos produtos: Insere cada produto como um item desta venda
            $saleItemsTable = $this->table('sale_items');
            foreach ($productIds as $row) {
                $pid = (int) $row['id'];
                $pname = (string) $row['nome'];

                // Cálculos matemáticos simulados para gerar dados realistas nos gráficos
                $base = 10 + $m;
                $mult = $weights[$pname] ?? 1.0;
                $unidades = (int) max(1, round($base * $mult));
                $valorUnit = (float) (20 + (int) ($pid % 7) * 10);

                // Insere o Item da Venda (relacionando sale_id e product_id)
                $saleItemsTable->insert([
                    'sale_id' => $saleId,
                    'product_id' => $pid,
                    'unidades' => $unidades,
                    'valor_unitario' => $valorUnit,
                ]);
            }
            $saleItemsTable->saveData();
        }
    }
}

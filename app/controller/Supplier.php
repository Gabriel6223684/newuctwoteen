<?php

declare(strict_types=1);

namespace App\Controller;

final class Supplier extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-supplier'), [
                'titulo' => 'Lista de fornecedores',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $supplier = [];

        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('suppliers');
            $supplier = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();

            // Garante que se não encontrar nada no banco, vira um array vazio
            if ($supplier === false) {
                $supplier = [];
            }
        }

        return $this->getTwig()
            ->render($response, $this->setView('supplier'), [
                'titulo' => 'Detalhes do fornecedor',
                'id' => $id,
                'action' => $action,
                'supplier' => $supplier,
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();

        // Captura os dados do formulário incluindo o enterprise_id obrigatório
        $fieldsAndValues = [
            'enterprise_id' => $form['enterprise_id'] ?? null, // Ou buscar da sessão/auth se necessário
            'nome_fantasia' => $form['nome_fantasia'] ?? '',
            'cpf_cnpj'      => $form['cpf_cnpj'] ?? '',
            'ativo'         => $this->normalizeAtivo($form['ativo'] ?? $form['active'] ?? null),
        ];

        // Validação preventiva: Se enterprise_id for nulo, o banco vai rejeitar
        if (is_null($fieldsAndValues['enterprise_id']) || $fieldsAndValues['enterprise_id'] === '') {
            return $this->json($response, [
                'status' => false,
                'msg' => 'O campo ID da Empresa (enterprise_id) é obrigatório.',
                'id' => 0
            ], 400); // 400 Bad Request
        }

        try {
            // Adicionado enterprise_id no SQL
            $sql = 'INSERT INTO suppliers (enterprise_id, nome_fantasia, cpf_cnpj, ativo)
                    VALUES (:enterprise_id, :nome_fantasia, :cpf_cnpj, :ativo)
                    RETURNING id';

            $row = \app\database\DB::connection()->fetchAssociative(
                $sql,
                [
                    'enterprise_id' => (int) $fieldsAndValues['enterprise_id'],
                    'nome_fantasia' => $fieldsAndValues['nome_fantasia'],
                    'cpf_cnpj'      => $fieldsAndValues['cpf_cnpj'],
                    'ativo'         => $fieldsAndValues['ativo'],
                ],
                [
                    'enterprise_id' => \Doctrine\DBAL\ParameterType::INTEGER,
                    'nome_fantasia' => \Doctrine\DBAL\ParameterType::STRING,
                    'cpf_cnpj'      => \Doctrine\DBAL\ParameterType::STRING,
                    'ativo'         => \Doctrine\DBAL\ParameterType::BOOLEAN,
                ]
            );

            $id = is_array($row) && isset($row['id']) ? (int) $row['id'] : 0;
            if ($id <= 0) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao inserir', 'id' => 0], 500);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function update($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;

        if (is_null($id) || $id === '') {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }

        $fieldsAndValues = [
            'enterprise_id'      => !empty($form['enterprise_id']) ? $form['enterprise_id'] : null,
            'address_id'         => !empty($form['address_id']) ? $form['address_id'] : null,
            'nome_fantasia'      => $form['nome_fantasia'] ?? null,
            'razao_social'       => $form['razao_social'] ?? null,
            'cpf_cnpj'           => $form['cpf_cnpj'] ?? null,
            'inscricao_estadual' => $form['inscricao_estadual'] ?? null,
            'ativo'              => $this->normalizeAtivo($form['ativo'] ?? $form['active'] ?? null), // Corrigido para 'ativo'
        ];

        try {
            // O método update retorna o número de linhas afetadas. 
            // Se o usuário salvar sem alterar nada, pode retornar 0. Mas ainda assim é sucesso.
            \app\database\DB::connection()->update('suppliers', $fieldsAndValues, ['id' => $id]);

            return $this->json($response, ['status' => true, 'msg' => 'Atualizado!', 'id' => (int) $id], 200);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function delete($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;

        if (is_null($id) || $id === '') {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }

        try {
            $isDeleted = \app\database\DB::connection()->delete('suppliers', ['id' => $id]);
            if (!$isDeleted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao excluir ou registro não encontrado', 'id' => (int) $id], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Excluído!', 'id' => (int) $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    private function normalizeAtivo($value): bool
    {
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }
        if ($value === null || $value === false) {
            return false;
        }

        $valueStr = strtolower(trim((string) $value));
        if ($valueStr === '') {
            return false;
        }
        return in_array($valueStr, ['true', 'on', 'yes'], true);
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term = $form['search']['value'] ?? null;
        $start = (int) ($form['start'] ?? 0);
        $length = (int) ($form['length'] ?? 10);

        // Corrigido de 'active' para 'ativo' para bater com o seu banco Postgres
        $columns = [
            0 => 'id',
            1 => 'nome_fantasia',
            2 => 'cpf_cnpj',
            3 => 'ativo',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            // Contagem total limpa
            $totalRecords = \app\database\DB::connection()->fetchOne('SELECT COUNT(*) FROM suppliers');
            $totalRecords = $totalRecords !== false ? (int) $totalRecords : 0;

            $query = \app\database\DB::select('*')->from('suppliers');

            if (!is_null($term) && $term !== '') {
                $query->where(
                    $query->expr()->or(
                        'nome_fantasia ILIKE :term',
                        'razao_social ILIKE :term',
                        'cpf_cnpj ILIKE :term',
                        'inscricao_estadual ILIKE :term'
                    )
                )->setParameter('term', '%' . $term . '%');
            }

            // Clonando a query montada de forma segura para fazer o COUNT dos filtrados
            $countQuery = clone $query;
            $filteredRecords = $countQuery->select('COUNT(*)')->fetchOne();
            $filteredRecords = $filteredRecords !== false ? (int) $filteredRecords : 0;

            // Executa a busca paginada redefinindo os campos que queremos trazer
            $suppliers = $query->select('*')
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($suppliers as $key => $value) {
                // Corrigido para buscar 'ativo' (que retorna do banco)
                $status = ($value['ativo'] === true || $value['ativo'] === 1 || $value['ativo'] === 't') ? 'Ativo' : 'Inativo';

                $id = (int) $value['id'];
                $rows[$key] = [
                    $id,
                    $value['nome_fantasia'] ?? '',
                    $value['cpf_cnpj'] ?? '',
                    $status,
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/fornecedor/detalhes/{$id}'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>
                        <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal({$id});'> <i class='fa-solid fa-trash'></i> Excluir</button>
                    </td>",
                ];
            }

            return $this->json($response, [
                'draw'            => (int)($form['draw'] ?? 0),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => array_values($rows),
            ], 200);
        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg'    => 'Restrição: ' . $e->getMessage(),
                'id'     => 0,
            ], 500);
        }
    }
}

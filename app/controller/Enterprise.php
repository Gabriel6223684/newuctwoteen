<?php

declare(strict_types=1);

namespace app\controller;

final class Enterprise extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-enterprise'), [
                'titulo' => 'Lista de empresas',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $enterprise = [];

        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('enterprises');
            $enterprise = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();

            if ($enterprise === false) {
                $enterprise = [];
            }
        }

        return $this->getTwig()
            ->render($response, $this->setView('enterprise'), [
                'titulo' => 'Detalhes da empresa',
                'id' => $id,
                'action' => $action,
                'enterprise' => $enterprise,
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();

        // Mapeando apenas as colunas reais que existem na tabela enterprises
        $fieldsAndValues = [
            'nome'  => $form['nomeExibicao'] ?? $form['nomeLegal'] ?? $form['nome'] ?? '',
            'cnpj'  => !empty($form['cnpj']) ? $form['cnpj'] : null,
            'ativo' => $this->normalizeAtivo($form['ativo'] ?? null),
        ];

        try {
            $connection = \app\database\DB::connection();

            // O Doctrine DBAL cuidará do insert de forma segura
            $isInserted = $connection->insert('enterprises', $fieldsAndValues, [
                'ativo' => \Doctrine\DBAL\ParameterType::BOOLEAN
            ]);

            if (!$isInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao inserir no banco de dados', 'id' => 0], 500);
            }

            $id = (int) $connection->lastInsertId();

            return $this->json($response, ['status' => true, 'msg' => 'Empresa salva com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Erro no Banco: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function update($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;

        if (is_null($id) || $id === '') {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }

        // Mapeando apenas as colunas reais para o update
        $fieldsAndValues = [
            'nome'  => $form['nomeExibicao'] ?? $form['nomeLegal'] ?? $form['nome'] ?? null,
            'cnpj'  => !empty($form['cnpj']) ? $form['cnpj'] : null,
            'ativo' => $this->normalizeAtivo($form['ativo'] ?? null),
        ];

        try {
            \app\database\DB::connection()->update('enterprises', $fieldsAndValues, ['id' => $id], [
                'ativo' => \Doctrine\DBAL\ParameterType::BOOLEAN
            ]);

            return $this->json($response, ['status' => true, 'msg' => 'Empresa atualizada!', 'id' => (int) $id], 200);
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
            $isDeleted = \app\database\DB::connection()->delete('enterprises', ['id' => $id]);
            if (!$isDeleted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao excluir ou registro não encontrado', 'id' => (int) $id], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Excluída!', 'id' => (int) $id]);
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

        $columns = [
            0 => 'id',
            1 => 'nome', // Ajustado para a coluna real
            2 => 'cnpj',
            3 => 'ativo',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            $totalRecords = (int) \app\database\DB::connection()->fetchOne('SELECT COUNT(*) FROM enterprises');

            $query = \app\database\DB::select('*')->from('enterprises');

            if (!is_null($term) && $term !== '') {
                $query->where(
                    $query->expr()->or(
                        'nome ILIKE :term', // Filtrando na coluna real
                        'cnpj ILIKE :term'
                    )
                )->setParameter('term', '%' . $term . '%');
            }

            $countQuery = clone $query;
            $filteredRecords = (int) $countQuery->select('COUNT(*)')->fetchOne();

            $enterprises = $query->select('*')
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($enterprises as $key => $value) {
                $status = ($value['ativo'] === true || $value['ativo'] === 1 || $value['ativo'] === 't') ? 'Ativo' : 'Inativo';

                $id = (int) $value['id'];
                $rows[$key] = [
                    $id,
                    $value['nome'] ?? '', // Ajustado para ler 'nome'
                    $value['cnpj'] ?? '',
                    $status,
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/enterprise/detalhes/{$id}'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>
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

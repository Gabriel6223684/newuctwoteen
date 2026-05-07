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

        $fieldsAndValues = [
            'numeroDocumento' => $form['numeroDocumento'] ?? '',
            'nomeExibicao' => $form['nomeExibicao'] ?? '',
            'nomeLegal' => $form['nomeLegal'] ?? '',
            'registroSecundario' => $form['registroSecundario'] ?? null,
            'dataRegistro' => $form['dataRegistro'] ?? null,
            'regimeTributario' => $form['regimeTributario'] ?? null,
            'codigoAtividadeEconomica' => $form['codigoAtividadeEconomica'] ?? null,
            'cnpj' => $form['cnpj'] ?? null,
            'cnae' => $form['cnae'] ?? null,
            'ativo' => $this->normalizeAtivo($form['ativo'] ?? null),
        ];

        try {
            $isInserted = \app\database\DB::connection()->insert('enterprises', $fieldsAndValues);
            if (!$isInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao inserir', 'id' => 0], 500);
            }

            $row = \app\database\DB::select('id')->from('enterprises')->orderBy('id', 'DESC')->fetchAssociative();
            $id = is_array($row) && isset($row['id']) ? (int) $row['id'] : 0;

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
            'numeroDocumento' => $form['numeroDocumento'] ?? null,
            'nomeExibicao' => $form['nomeExibicao'] ?? null,
            'nomeLegal' => $form['nomeLegal'] ?? null,
            'registroSecundario' => $form['registroSecundario'] ?? null,
            'dataRegistro' => $form['dataRegistro'] ?? null,
            'regimeTributario' => $form['regimeTributario'] ?? null,
            'codigoAtividadeEconomica' => $form['codigoAtividadeEconomica'] ?? null,
            'cnpj' => $form['cnpj'] ?? null,
            'cnae' => $form['cnae'] ?? null,
            'ativo' => $this->normalizeAtivo($form['ativo'] ?? null),
        ];

        try {
            $isUpdated = \app\database\DB::connection()->update('enterprises', $fieldsAndValues, ['id' => $id]);
            if (!$isUpdated) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao atualizar', 'id' => (int) $id], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Atualizado!', 'id' => (int) $id], 201);
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
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao excluir', 'id' => (int) $id], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Excluída!', 'id' => (int) $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    private function normalizeAtivo($value): bool
    {
        if ($value === true) {
            return true;
        }
        if ($value === null) {
            return false;
        }

        $valueStr = strtolower(trim((string) $value));
        return in_array($valueStr, ['1', 'true', 'on', 'yes'], true);
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term = $form['search']['value'] ?? null;
        $start = (int) ($form['start'] ?? 0);
        $length = (int) ($form['length'] ?? 10);

        $columns = [
            0 => 'id',
            1 => 'nomeExibicao',
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
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')->from('enterprises')->fetchOne(0);

            $query = \app\database\DB::select('*')->from('enterprises');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');
                $query->where('nomeExibicao ILIKE :term')
                    ->orWhere('nomeLegal ILIKE :term')
                    ->orWhere('cnpj ILIKE :term')
                    ->orWhere('numeroDocumento ILIKE :term');
            }

            $filteredRecords = (int) (clone $query)
                ->select('COUNT(*)')
                ->fetchOne(0);

            $enterprises = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($enterprises as $key => $value) {
                $status = ($value['ativo'] === true || $value['ativo'] === 1) ? 'Ativo' : 'Inativo';

                $id = (int) $value['id'];
                $rows[$key] = [
                    $id,
                    $value['nomeExibicao'] ?? '',
                    $value['cnpj'] ?? '',
                    $status,
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/enterprise/detalhes/{$id}'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>
                        <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal({$id});'> <i class='fa-solid fa-trash'></i> Excluir</button>
                    </td>",
                ];
            }

            return $this->json($response, [
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($rows),
            ], 200);
        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage(),
                'id' => 0,
            ], 500);
        }
    }
}


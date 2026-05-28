<?php

declare(strict_types=1);

namespace app\controller;

final class Country extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-country'), [
                'titulo' => 'Lista de países',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {

        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $country = [];

        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('countries');
            $country = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();
        }

        return $this->getTwig()
            ->render($response, $this->setView('country'), [
                'titulo' => 'Detalhes do país',
                'id' => $id,
                'action' => $action,
                'country' => $country,
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();
        // colunas reais no banco (migration)
        $nome = $form['name'] ?? '';
        $codigoIso = $form['iso_code'] ?? '';

        try {
            $sql = 'insert into countries (nome, codigo_iso) values (:nome, :codigo_iso) returning id';
            $row = \app\database\DB::connection()->fetchAssociative($sql, [
                'nome' => $nome,
                'codigo_iso' => $codigoIso,
            ]);

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

        $nome = $form['name'] ?? '';
        $codigoIso = $form['iso_code'] ?? '';

        try {
            $fieldsAndValues = [
                'nome' => $nome,
                'codigo_iso' => $codigoIso,
            ];

            \app\database\DB::connection()->update('countries', $fieldsAndValues, ['id' => $id]);

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
            \app\database\DB::connection()->delete('countries', ['id' => $id]);
            return $this->json($response, ['status' => true, 'msg' => 'Excluído!', 'id' => (int) $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term = $form['search']['value'] ?? null;
        $start = (int) ($form['start'] ?? 0);
        $length = (int) ($form['length'] ?? 10);

        $columns = [
            0 => 'id',
            1 => 'nome',
            2 => 'codigo_iso',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')->from('countries')->fetchOne(0);

            $query = \app\database\DB::select('*')->from('countries');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');
                $query->where('nome ILIKE :term')
                    ->orWhere('codigo_iso ILIKE :term');
            }

            $filteredRecords = (int) (clone $query)->select('COUNT(*)')->fetchOne(0);

            $countries = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($countries as $key => $value) {
                $rows[$key] = [
                    $value['id'],
                    $value['nome'],
                    $value['codigo_iso'],
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/pais/detalhes/{$value['id']}'><i class='fa-solid fa-pen-to-square'></i> Editar</a>
                        <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal({$value['id']});'><i class='fa-solid fa-trash'></i> Excluir</button>
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
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
}


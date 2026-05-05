<?php

declare(strict_types=1);

namespace app\controller;

final class User extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-user'), [
                'titulo' => 'Lista de usuários',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $user = [];
        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('users');

            $user = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();
        }
        return $this->getTwig()
            ->render($response, $this->setView('user'), [
                'titulo' => 'Detalhes do usuário',
                'id' => $id,
                'action' => $action,
                'user' => $user
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();
        $FieldsAndValues = [
            'name' => $form['name'],
            'email' => $form['email'],
            'password' => password_hash($form['password'], PASSWORD_DEFAULT),
            'active' => 1
        ];
        try {
            $IsInserted = \app\database\DB::connection()->insert('users', $FieldsAndValues);
            if (!$IsInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao inserir', 'id' => 0], 500);
            }
            $id = \app\database\DB::select('id')->from('users')->orderBy('id', 'DESC')->fetchOne(0);

            return $this->json($response, ['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function update($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;
        if (is_null($id)) {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }
        $FieldsAndValues = [
            'name' => $form['name'],
            'email' => $form['email']
        ];
        if (!empty($form['password'])) {
            $FieldsAndValues['password'] = password_hash($form['password'], PASSWORD_DEFAULT);
        }
        try {
            $IsUpdated = \app\database\DB::connection()->update('users', $FieldsAndValues, ['id' => $id]);
            return $this->json($response, ['status' => true, 'msg' => 'Atualizado!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function delete($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;
        if (is_null($id)) {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }
        try {
            \app\database\DB::connection()->delete('users', ['id' => $id]);
            return $this->json($response, ['status' => true, 'msg' => 'Excluído!', 'id' => $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => $id], 500);
        }
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term   = $form['search']['value'] ?? null;
        $start  = (int) ($form['start']  ?? 0);
        $length = (int) ($form['length'] ?? 10);

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'email',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')->from('users')->fetchOne(0);

            $query = \app\database\DB::select('*')->from('users');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');
                $query->where('name ILIKE :term')
                    ->orWhere('email ILIKE :term');
            }

            $filteredRecords = (int) (clone $query)->select('COUNT(*)')->fetchOne(0);

            $users = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($users as $key => $value) {
                $status = $value['active'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
                $rows[$key] = [
                    $value['id'],
                    $value['name'],
                    $value['email'],
                    $status,
                    "<td>
                    <a class='btn btn-sm btn-warning' href='/usuario/detalhes/" . $value['id'] . "'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>
                    <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal(" . $value['id'] . ");'> <i class='fa-solid fa-trash'></i> Excluir</button>
                </td>",
                ];
            }

            return $this->json($response, [
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $rows,
            ], 200);
        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
}


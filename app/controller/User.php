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
            $qb = \app\database\DB::select('*')->from('vw_user');
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

        $nomeCompleto = trim((string) ($form['name'] ?? ''));
        $email = trim((string) ($form['email'] ?? ''));
        $password = (string) ($form['password'] ?? '');
        $active = !empty($form['active']) ? 1 : 0;

        if ($nomeCompleto === '' || $email === '' || $password === '') {
            return $this->json($response, ['status' => false, 'msg' => 'Nome, email e senha são obrigatórios.', 'id' => 0], 400);
        }

        try {
            $FieldsAndValues = [
                'nome'          => $nomeCompleto,
                'sobrenome'     => '',
                'cpf'           => '',
                'rg'            => '',
                'senha'         => password_hash($password, PASSWORD_DEFAULT),
                'ativo'         => $active, // Removido o (bool), mantendo 1 ou 0
                'administrador' => 0,       // Alterado de false para 0
            ];

            $IsInserted = \app\database\DB::connection()->insert('users', $FieldsAndValues);
            if (!$IsInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Erro ao inserir', 'id' => 0], 500);
            }

            $id = \app\database\DB::connection()->lastInsertId();

            // Email fica na tabela contact (tipo EMAIL)
            $DataEmail = [
                'id_usuario' => $id,
                'tipo'       => 'EMAIL',
                'contato'    => $email
            ];
            \app\database\DB::connection()->insert('contact', $DataEmail);

            return $this->json($response, ['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => (int) $id], 201);
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

        $nomeCompleto = trim((string) ($form['name'] ?? ''));
        $email = trim((string) ($form['email'] ?? ''));
        $password = (string) ($form['password'] ?? '');
        $active = !empty($form['active']) ? 1 : 0;

        if ($nomeCompleto === '' || $email === '') {
            return $this->json($response, ['status' => false, 'msg' => 'Nome e email são obrigatórios.', 'id' => 0], 400);
        }

        try {
            $FieldsAndValues = [
                'nome'  => $nomeCompleto,
                'ativo' => $active, // Alterado de (bool) $active para apenas $active (1 ou 0)
            ];

            if ($password !== '') {
                $FieldsAndValues['senha'] = password_hash($password, PASSWORD_DEFAULT);
            }

            \app\database\DB::connection()->update('users', $FieldsAndValues, ['id' => $id]);

            // Atualiza o contato do email (contact.tipo = EMAIL)
            $updated = \app\database\DB::connection()->update(
                'contact',
                ['contato' => $email],
                ['id_usuario' => $id, 'tipo' => 'EMAIL']
            );

            // Se não existir registro de email, insere
            if (!$updated) {
                \app\database\DB::connection()->insert('contact', [
                    'id_usuario' => $id,
                    'tipo' => 'EMAIL',
                    'contato' => $email
                ]);
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

        if (is_null($id)) {
            return $this->json($response, ['status' => false, 'msg' => 'ID obrigatório', 'id' => 0], 403);
        }

        try {
            \app\database\DB::connection()->delete('users', ['id' => $id]);
            return $this->json($response, ['status' => true, 'msg' => 'Excluído!', 'id' => (int) $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => $e->getMessage(), 'id' => $id], 500);
        }
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term   = $form['search']['value'] ?? null;
        $start  = (int) ($form['start'] ?? 0);
        $length = (int) ($form['length'] ?? 10);

        // Colunas do DataTables (índices) -> colunas da view
        $columns = [
            0 => 'id',
            1 => 'nome',
            2 => 'email',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')->from('vw_user')->fetchOne(0);

            $query = \app\database\DB::select('*')->from('vw_user');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');
                $query->where('nome ILIKE :term')
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
                $status = $value['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';

                $rows[$key] = [
                    $value['id'],
                    $value['nome'],
                    $value['email'],
                    $status,
                    "<a class='btn btn-sm btn-warning' href='/usuario/detalhes/" . $value['id'] . "'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>"
                        . " <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal(" . $value['id'] . ");'> <i class='fa-solid fa-trash'></i> Excluir</button>",
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

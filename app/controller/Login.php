<?php

namespace app\controller;

final class Login extends Base
{
    public function authenticate($request, $response)
    {
        $form = $request->getParsedBody();

        $login = trim($form['login'] ?? '');
        $senha = $form['senha'] ?? '';

        if (empty($login) || empty($senha)) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'Credenciais ausentes!'
            ], 400);
        }

        try {

            $qb = \app\database\DB::select('*')
                ->from('vw_user');

            $param = $qb->createNamedParameter($login);

            $qb->where('cpf = ' . $param)
                ->orWhere('email = ' . $param)
                ->orWhere('whatsapp = ' . $param);

            $user = $qb
                ->executeQuery()
                ->fetchAssociative();

            $dummyHash = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8.k3.kK1m3Sv7lJ1uG9N9Yvb.MqYsa';

            $senhaValida = password_verify(
                $senha,
                $user['senha'] ?? $dummyHash
            );

            if (!$user || !$senhaValida) {
                return $this->json($response, [
                    'status' => false,
                    'msg' => 'Acesso negado.'
                ], 403);
            }

            $jwt = 'token-gerado';
            $lifetime = 3600;

            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'domain'   => 'seu-dominio.com',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            return $this->json($response, [
                'status' => true,
                'msg' => 'Bem-vindo!'
            ], 200);
        } catch (\Throwable $e) {

            error_log($e->getMessage());

            return $this->json($response, [
                'status' => false,
                'msg' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    public function preRegister($request, $response)
    {
        $form = $request->getParsedBody();

        // 4. MELHORIA: Validação rigorosa
        $email = filter_var($form['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return $this->json($response, ['status' => false, 'msg' => 'E-mail inválido.'], 400);
        }

        // Use apenas os campos estritamente necessários (Whitelist)
        $DataUser = [
            'nome'      => strip_tags($form['nome'] ?? ''),
            'cpf'       => preg_replace('/[^0-9]/', '', $form['cpf'] ?? ''), // Apenas números
            'senha'     => password_hash($form['senha'], PASSWORD_DEFAULT),
            'criado_em' => date('Y-m-d H:i:s')
        ];

        // 5. IMPORTANTE: Use Transações se inserir em múltiplas tabelas
        $conn = \app\database\DB::connection();
        $conn->beginTransaction();
        try {
            $conn->insert('users', $DataUser);
            $userId = $conn->lastInsertId();

            // Insere contatos...

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            return $this->json($response, ['status' => false, 'msg' => 'Erro ao cadastrar.'], 500);
        }
    }
}

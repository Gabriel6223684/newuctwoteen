<?php

declare(strict_types=1);

namespace App\Controller;

final class Login extends Base
{
    public function login($request, $response)
    {
        try {
            return $this->getTwig()
                ->render($response, $this->setView('login'), [
                    'titulo' => 'Início',
                ])
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            error_log('[login][VIEW] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro ao carregar a página.'], 500);
        }
    }

    public function authenticate($request, $response)
    {
        # Recupera as credenciais enviadas no corpo da requisição
        $form = $request->getParsedBody();
        $login = $form['login'] ?? null;
        $senha = $form['senha'] ?? null;

        # Bloqueia se algum campo veio vazio
        if (is_null($login) || is_null($senha)) {
            return $this->json($response, ['status' => false, 'msg' => 'Por favor informe seu usuário e senha!', 'id' => 0]);
        }

        # Verifica se a sessão está em "lockout" por excesso de tentativas falhas
        if (isset($_SESSION['login_locked_until']) && $_SESSION['login_locked_until'] > time()) {
            return $this->json($response, ['status' => false, 'msg' => 'Muitas tentativas. Tente novamente em alguns minutos.', 'id' => 0], 429);
        }

        try {
            # Começa a montar a query: SELECT * FROM vw_user
            $qb = \app\database\DB::select('*')
                ->from('vw_user');

            # Define o valor que será procurado nos três campos
            $placeholder = $qb->createNamedParameter($login);

            # Monta a cláusula WHERE com três condições ligadas por OR:
            $qb->where('cpf = ' . $placeholder)
                ->orWhere('email = '    . $placeholder)
                ->orWhere('whatsapp = ' . $placeholder);

            # Executa a query e busca um único registro
            $user = $qb->fetchAssociative();

            # Hash bcrypt pré-computado e inválido, usado quando o usuário não existe
            $dummyHash = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8.k3.kK1m3Sv7lJ1uG9N9Yvb.MqYsa';

            # Sempre executa password_verify, mesmo sem usuário, para manter tempo de resposta constante
            $senhaValida = password_verify($senha, $user['senha'] ?? $dummyHash);

            # Falha de autenticação: mensagem genérica + contador de tentativas
            if (!$user || !$senhaValida) {
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['login_locked_until'] = time() + 900;
                    $_SESSION['login_attempts'] = 0;
                }
                return $this->json($response, ['status' => false, 'msg' => 'Verifique seu e-mail e senha e tente novamente!', 'id' => 0], 403);
            }

            # Login válido: zera contadores de tentativa e lockout
            unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);

            # Regenera o ID da sessão para mitigar session fixation
            session_regenerate_id(true);

            # Renova o hash da senha se o algoritmo/custo padrão tiver mudado
            if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
                \app\database\DB::connection()->update(
                    'users',
                    [
                        'senha'         => password_hash($senha, PASSWORD_DEFAULT),
                        'atualizado_em' => date('Y-m-d H:i:s'),
                    ],
                    ['id' => $user['id']],
                );
            }

            # Remove o hash da senha antes de gravar o usuário na sessão
            unset($user['senha']);

            # Persiste o usuário autenticado na sessão
            $_SESSION['user'] = $user;
            $_SESSION['user']['logado'] = true;

            $lifetime = (int) (ini_get('session.gc_maxlifetime') ?: 3600);
            $now = time();
            $jti = bin2hex(random_bytes(16));

            # Monta o payload do JWT
            $payload = [
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + $lifetime,
                'sub' => (string) $user['id'],
                #'iss' => HOST,
                #'aud' => HOST,
                'jti' => $jti,
            ];

            $jwt = \Firebase\JWT\JWT::encode($payload, SECRET_KEY, 'HS256');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                #'domain'   => HOST,
                #'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            $agora = (new \DateTimeImmutable())->setTimestamp($now);
            $_SESSION['user']['sessao_criada_em'] = $agora->format('Y-m-d H:i:s');
            $_SESSION['user']['sessao_expira_em'] = $agora->modify("+{$lifetime} seconds")->format('Y-m-d H:i:s');

            return $this->json($response, [
                'status'           => true,
                'msg'              => 'Seja bem vindo de volta!',
                'id'               => $user['id'],
                'sessao_expira_em' => $_SESSION['user']['sessao_expira_em']
            ], 200);
        } catch (\PDOException $e) {
            error_log('[auth][DB] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\UnexpectedValueException | \DomainException $e) {
            error_log('[auth][JWT] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\Throwable $e) {
            error_log('[auth][GERAL] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro inesperado. Tente novamente: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function preRegister($request, $response)
    {
        $form = $request->getParsedBody();
        $nome      = $form['nome'] ?? null;
        $sobrenome = $form['sobrenome'] ?? null;
        $cpf       = $form['cpf'] ?? null;
        $rg        = $form['rg'] ?? null;
        $senha     = $form['cad-senha'] ?? null;
        $email     = $form['email'] ?? null;
        $telefone  = $form['telefone'] ?? null;

        $DataUser = [
            'nome'      => $nome,
            'sobrenome' => $sobrenome,
            'cpf'       => $cpf,
            'rg'        => $rg,
            'senha'     => password_hash($senha, PASSWORD_DEFAULT)
        ];

        # Insere os dados no database com o Doctrine e recupera o ID corretamente via conexão
        \app\database\DB::connection()->insert('users', $DataUser);
        $id_usuario = \app\database\DB::connection()->lastInsertId();

        # Insere os dados do email do usuário na base.
        $DataEmail = [
            'id_usuario' => $id_usuario,
            'tipo' => 'EMAIL',
            'contato' => $email
        ];
        \app\database\DB::connection()->insert('contact', $DataEmail);

        # Insere os dados do telefone do usuário na base.
        $DataTel = [
            'id_usuario' => $id_usuario,
            'tipo' => 'TELEFONE',
            'contato' => $telefone
        ];
        \app\database\DB::connection()->insert('contact', $DataTel);

        return $this->json($response, [
            'status' => true,
            'msg' => 'Usuário cadastrado com sucesso!'
        ], 200);
    }

    public function google($request, $response)
    {
        $form = $request->getParsedBody();
        $credential = $form['credential'] ?? null;
        $form_g_csrf_token = $form['g_csrf_token'] ?? null;
        $cookie_g_csrf_token = $_COOKIE['g_csrf_token'] ?? null;
        $google_client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? null;

        if (is_null($credential) || is_null($form_g_csrf_token) || is_null($cookie_g_csrf_token)) {
            return $this->json($response, ['status' => false, 'msg' => 'Credential do Google ausente', 'id' => 0], 400);
        }

        try {
            $provider = new \League\OAuth2\Client\Provider\Google([
                'clientId'     => $google_client_id,
                'clientSecret' => '',
                'redirectUri'  => '',
            ]);

            $httpResponse = $provider->getHttpClient()->request(
                'GET',
                'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential),
                ['timeout' => 3, 'connect_timeout' => 2]
            );

            $claims = json_decode((string) $httpResponse->getBody(), true, flags: jsON_THROW_ON_ERROR);

            $nome = (string) ($claims['given_name'] ?? '');
            $sobrenome = (string) ($claims['family_name'] ?? '');
            $email = (string) ($claims['email'] ?? '');
            $google_id = (string) ($claims['sub'] ?? '');

            if ($email === '') {
                return $this->json($response, ['status' => false, 'msg' => 'E-mail não informado na resposta do Google', 'id' => 0], 400);
            }

            // 1) Encontrar usuário existente via contact (EMAIL)
            $qb = \app\database\DB::select('*')
                ->from('vw_user');

            $placeholder = $qb->createNamedParameter($email);
            $user = $qb->where('email = ' . $placeholder)->fetchAssociative();

            if (!$user) {
                // 2) Se não existir: inserir em users + contact(EMAIL)
                $dummyHash = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8.k3.kK1m3Sv7lJ1uG9N9Yvb.MqYsa';

                $DataUser = [
                    'nome'          => $nome,
                    'sobrenome'     => $sobrenome,
                    'cpf'           => '',
                    'rg'            => '',
                    'senha'         => $dummyHash,
                    'ativo'         => 'false',
                    'administrador' => 'false',
                ];


                \app\database\DB::connection()->insert('users', $DataUser);
                $userId = \app\database\DB::connection()->lastInsertId();

                $DataEmail = [
                    'id_usuario' => $userId,
                    'tipo' => 'EMAIL',
                    'contato' => $email,
                ];
                \app\database\DB::connection()->insert('contact', $DataEmail);

                return $response
                    ->withHeader('Location', '/login')
                    ->withStatus(302);
            }

            if (!$user['ativo']) {
                return $this->json($response, [
                    'status' => false,
                    'msg' => 'Por enquanto você ainda não está autorizado, aguarde liberação',
                    'id' => $user['id']
                ], 403);
            }
            // 3) Login: seguir o mesmo fluxo do authenticate()
            unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
            session_regenerate_id(true);

            $lifetime = (int) (ini_get('session.gc_maxlifetime') ?: 3600);
            $now = time();
            $jti = bin2hex(random_bytes(16));

            $payload = [
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + $lifetime,
                'sub' => (string) $user['id'],
                'jti' => $jti
            ];

            $jwt = \Firebase\JWT\JWT::encode($payload, SECRET_KEY, 'HS256');


            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            $_SESSION['user'] = $user;
            $_SESSION['user']['logado'] = true;

            $agora = (new \DateTimeImmutable())->setTimestamp($now);
            $_SESSION['user']['sessao_criada_em'] = $agora->format('Y-m-d H:i:s');
            $_SESSION['user']['sessao_expira_em'] = $agora->modify("+{$lifetime} seconds")->format('Y-m-d H:i:s');

            return $response
                ->withHeader('Location', '/home')
                ->withStatus(302);
        } catch (\PDOException $e) {
            error_log('[google][DB] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro ao consultar/gravar usuário', 'id' => 0], 500);
        } catch (\UnexpectedValueException | \DomainException $e) {
            error_log('[google][JWT/VAL] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Falha ao processar autenticação do Google', 'id' => 0], 500);
        } catch (\Throwable $e) {
            error_log('[google][GERAL] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro inesperado na autenticação Google: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }


    public function logout($request, $response)
    {
        # Limpa os dados do array da sessão global
        $_SESSION = [];

        # Se desejar destruir o cookie da sessão PHP completamente
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        # Destrói a sessão no servidor
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        # Invalida/limpa o cookie JWT do lado do cliente
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => HOST,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        # Redireciona o cliente de volta para a tela de login
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}

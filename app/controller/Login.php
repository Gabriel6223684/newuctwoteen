<?php

namespace app\controller;

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
            var_dump($e->getMessage());
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
            # O Doctrine cria um "placeholder seguro" no lugar do valor real,
            # protegendo a aplicação contra SQL injection.
            $login = $qb->createNamedParameter($login);

            # Monta a cláusula WHERE com três condições ligadas por OR:
            # WHERE cpf = :login OR email = :login OR whatsapp = :login
            $qb->where('cpf = ' . $login)
                ->orWhere('email = '    . $login)
                ->orWhere('whatsapp = ' . $login);

            # Executa a query e busca um único registro (a primeira linha encontrada)
            $user = $qb->fetchAssociative();

            # Hash bcrypt pré-computado e inválido, usado quando o usuário não existe (proteção contra timing attack)
            $dummyHash = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8.k3.kK1m3Sv7lJ1uG9N9Yvb.MqYsa';

            # Sempre executa password_verify, mesmo sem usuário, para manter tempo de resposta constante
            $senhaValida = password_verify($senha, $user['senha'] ?? $dummyHash);

            # Falha de autenticação: mensagem genérica + contador de tentativas
            if (!$user || !$senhaValida) {
                # Incrementa o contador de tentativas falhas da sessão atual
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                # Após 5 falhas, bloqueia novas tentativas por 15 minutos (rate limiting básico)
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

            # Remove o hash da senha antes de gravar o usuário na sessão (evita expor credencial)
            unset($user['senha']);

            # Persiste o usuário autenticado na sessão (fonte de verdade do estado)
            $_SESSION['user'] = $user;
            $_SESSION['user']['logado'] = true;

            # Calcula o tempo de vida da sessão a partir do php.ini, com fallback de 3600s
            $lifetime = (int) (ini_get('session.gc_maxlifetime') ?: 3600);

            # Monta o payload do JWT usando o ID do usuário como subject (identificador estável e único)
            $payload = [
                'iat' => time(),                 # Momento de emissão
                'exp' => time() + $lifetime,     # Expiração alinhada à sessão
                'sub' => (string) $user['id'], # Subject = ID do usuário
            ];

            # Assina o token JWT com a chave secreta da aplicação
            $jwt = \Firebase\JWT\JWT::encode($payload, SECRET_KEY, 'HS256');

            # Determina se a conexão está em HTTPS (define o atributo Secure do cookie)
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

            # Define o cookie auth_token usando COOKIE_DOMAIN (constante de configuração, imune a Host Header Injection)
            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'domain' => $_SERVER['HTTP_HOST'], #Usa dinamicamente o domínio correto
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            # Registra na sessão o horário de criação e o horário previsto de expiração (formato H:i:s correto)
            $_SESSION['user']['sessao_criada_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $_SESSION['user']['sessao_expira_em'] = (new \DateTime())->modify("+{$lifetime} seconds")->format('Y-m-d H:i:s');

            # Retorna a resposta de sucesso ao cliente
            return $this->json($response, [
                'status'           => true,
                'msg'              => 'Seja bem vindo de volta!',
                'id'               => $user['id'],
                'sessao_expira_em' => $_SESSION['user']['sessao_expira_em']
            ], 200);
        } catch (\PDOException $e) {
            # Erro de banco: loga internamente e responde de forma genérica
            error_log('[auth][DB] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\UnexpectedValueException | \DomainException $e) {
            # Erro específico do Firebase JWT (chave inválida, payload malformado, etc.)
            error_log('[auth][JWT] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\Throwable $e) {
            # Qualquer outra falha inesperada: loga e responde de forma genérica
            error_log('[auth][GERAL] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro inesperado. Tente novamente.', 'id' => 0], 500);
        }
    }

    public function preRegister($request, $response)
    {
        $form = $request->getParsedBody();

        // 1. Sanitização e Coleta
        $data = [
            'nome'      => trim($form['nome'] ?? ''),
            'sobrenome' => trim($form['sobrenome'] ?? ''),
            'cpf'       => preg_replace('/\D/', '', $form['cpf'] ?? ''),
            'rg'        => trim($form['rg'] ?? ''),
            'senha'     => $form['senha'] ?? '',
            'email'     => filter_var(trim($form['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'telefone'  => preg_replace('/\D/', '', $form['telefone'] ?? ''),
        ];

        // 2. Validação consistente
        if (!$this->isValid($data)) {
            return $response->withJson([
                'success' => false,
                'message' => 'Dados inválidos ou campos obrigatórios ausentes.'
            ], 400);
        }

        try {
            $this->entityManager->beginTransaction();

            // 3. Criar Usuário
            $usuario = new Usuario();
            $usuario->setNome($data['nome']);
            $usuario->setSobrenome($data['sobrenome']);
            $usuario->setCpf($data['cpf']);
            $usuario->setRg($data['rg']);
            $usuario->setSenha(password_hash($data['senha'], PASSWORD_DEFAULT));

            $this->entityManager->persist($usuario);

            // 4. Criar Contatos (Usando o objeto $usuario diretamente)
            if (!empty($data['email'])) {
                $this->addContato($usuario, 'EMAIL', $data['email']);
            }

            if (!empty($data['telefone'])) {
                $this->addContato($usuario, 'TELEFONE', $data['telefone']);
            }

            // Um único flush ao final é mais performático e seguro dentro da transação
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $response->withJson(['success' => true, 'message' => 'Cadastro realizado!']);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            // Logar o erro real internamente e enviar mensagem genérica ao user
            error_log($e->getMessage());
            return $response->withJson([
                'success' => false,
                'message' => 'Erro interno ao processar cadastro.'
            ], 500);
        }
    }

    // Métodos auxiliares para limpar o código principal:
    private function addContato(Usuario $usuario, string $tipo, string $valor)
    {
        $contato = new Contato();
        $contato->setUsuario($usuario); // O ORM mapeia o ID automaticamente
        $contato->setTipo($tipo);
        $contato->setContato($valor);
        $this->entityManager->persist($contato);
    }

    private function isValid(array $data): bool
    {
        return !empty($data['nome']) &&
            !empty($data['cpf']) &&
            strlen($data['senha']) >= 6 &&
            filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }
}

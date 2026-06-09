<?php

declare(strict_types=1);

// Teste 1: conexão com PostgreSQL funciona — sem isso nada no sistema opera
test('conexão com PostgreSQL está ativa', function () {
    // Usa a classe de conexão real do projeto (phinx DBAL)
    $phinxConn = app\database\Connection::get();

    // Extrai o PDO nativo de dentro do phinx Connection
    $pdo = $phinxConn->getNativeConnection();

    // Verifica que retornou uma instância PDO válida
    expect($pdo)->toBeInstanceOf(PDO::class);

    // Executa um comando simples para confirmar que o banco responde
    $result = $pdo->query('SELECT 1 AS ok')->fetch(\PDO::FETCH_ASSOC);

    expect($result['ok'])->toBe(1);
});


// Teste 2: ciclo insert → select → delete funciona — garante integridade do CRUD
test('insert select e delete funcionam no PostgreSQL', function () {
    // Documento único usando uniqid para nunca colidir se o teste rodar em paralelo
    $cpfTeste = '999.999.' . rand(100, 999) . '-' . rand(10, 99);

    // INSERT — cria um registro temporário
    $inserido = app\database\Builder\InsertQuery::insert('customer')
        ->save([
            'nome_fantasia'       => 'Teste Integração',
            'sobrenome_razao'     => 'Razão Teste',
            'cpf_cnpj'            => $cpfTeste,
            'inscricao_estadual'  => '000000',
            'nascimento_fundacao' => '2025-01-01',
            'ativo'               => true,
        ]);

    expect($inserido)->toBeInt()->toBeGreaterThan(0);

    // SELECT — confirma que o registro foi salvo corretamente
    $customer = app\database\Builder\SelectQuery::select()
        ->from('customer')
        ->where('cpf_cnpj', '=', $cpfTeste)
        ->fetch();

    expect($customer)->not->toBeEmpty();
    expect($customer['nome_fantasia'])->toBe('Teste Integração');

    // DELETE — remove o registro de teste para não poluir o banco
    $deletado = app\database\Builder\DeleteQuery::table('customer')
        ->where('id', '=', $customer['id'])
        ->delete();

    expect($deletado)->toBeTruthy();
});

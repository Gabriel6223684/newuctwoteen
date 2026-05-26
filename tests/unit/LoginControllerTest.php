<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('preRegister com dados válidos retorna 208 status true', function () {
    // Gera dados dinâmicos para nunca colidir com registros antigos no banco de dados
    $cpfDinamico = sprintf('%03d.%03d.%03d-%02d', rand(100, 999), rand(100, 999), rand(100, 999), rand(10, 99));
    $emailDinamico = 'user.' . uniqid() . '@gmail.com';

    $request = (new RequestFactory())
        ->createRequest('POST', '/authentication/preregister')
        ->withHeader('content-app', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nome' => 'Gabriel',
            'sobrenome' => 'Luiz',
            'cpf' => $cpfDinamico,
            'rg' => (string) rand(1111, 9999),
            'senha' => '1234',
            'email' => $emailDinamico,
            'telefone' => '699' . rand(90000000, 99999999),
        ]);

    $response = (new ResponseFactory())->createResponse();
    $result = (new app\controller\Login())->preRegister($request, $response);

    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);

    // Mantido 200 conforme a asserção original do seu código
    expect($result->getStatusCode())->toBe(200);
    expect($json['status'])->toBeTrue();
    expect($json['msg'])->toContain('Usuário cadastrado com sucesso!');
});

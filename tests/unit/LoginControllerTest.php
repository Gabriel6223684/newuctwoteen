<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('preRegister com dados válidos retorna 208 status true', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/authentication/preregister')
        ->withHeader('content-app', 'application/x-www-form-urlecoded')
        ->withParsedBody([
            'nome' => 'Gabriel',
            'sobrenome' => 'Luiz',
            'cpf' => '123.123.123-12',
            'rg' => '1234',
            'senha' => '1234',
            'email' => 'user@gmail.com',
            'telefone' => '69900000000',
        ]);
    $response = (new ResponseFactory)->createResponse();
    $result = (new app\controller\Login())->preRegister($request, $response);
    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);
    expect($result->getStatusCode())->toBe(200);
    expect($json['status'])->toBeTrue();
    expect($json['msg'])->toContain('Usuário cadastrado com sucesso!');
    expect($json['status'])->toBeTrue();
});

<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use app\controller\Enterprise;

class EnterpriseTest extends TestCase
{
    private $enterpriseController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enterpriseController = new Enterprise();
    }

    public function testInsertEnterpriseSuccess()
    {
        $suffix = uniqid();
        // CNPJ Dinâmico para evitar violação de Unique Key no PostgreSQL
        $cnpjDinamico = rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '/0001-' . rand(10, 99);

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/enterprise/insert');
        $request = $request->withParsedBody([
            'nomeExibicao' => 'Empresa Unidade de Teste ' . $suffix,
            'cnpj'         => $cnpjDinamico,
            'ativo'        => 'true'
        ]);

        $response = new Response();
        $resultResponse = $this->enterpriseController->insert($request, $response);

        $this->assertEquals(201, $resultResponse->getStatusCode());

        $body = json_decode((string)$resultResponse->getBody(), true);
        $this->assertTrue($body['status']);
        $this->assertGreaterThan(0, $body['id']);
    }
}

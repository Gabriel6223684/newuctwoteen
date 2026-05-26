<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use app\controller\Customer;

class CustomerTest extends TestCase
{
    private Customer $customerController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerController = new Customer();
    }

    public function testInsertCustomerSuccess(): void
    {
        $suffix = uniqid();
        // Gera um CPF randômico para a API aceitar o insert sem violar Unique Key no banco
        $documentoDinamico = '123.123.' . rand(100, 999) . '-' . rand(10, 99);

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/cliente/insert');
        $request = $request->withParsedBody([
            'nomeExibicao' => 'Cliente Teste ' . $suffix,
            'nomeLegal' => 'Razão Cliente Teste ' . $suffix,
            'numeroDocumento' => $documentoDinamico,
            'registroSecundario' => 'IE' . $suffix,
            'dataRegistro' => '15/06/2025',
            'ativo' => 'true',
        ]);

        $response = new Response();
        $resultResponse = $this->customerController->insert($request, $response);

        $this->assertSame(201, $resultResponse->getStatusCode());

        $body = json_decode((string) $resultResponse->getBody(), true);
        $this->assertTrue($body['status']);
        $this->assertGreaterThan(0, (int) $body['id']);

        // cleanup
       // $this->cleanupCustomer((int) $body['id']);
    }

   /* private function cleanupCustomer(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $deleteReq = (new ServerRequestFactory())->createServerRequest('POST', '/cliente/delete');
        $deleteReq = $deleteReq->withParsedBody(['id' => $id]);
        $deleteRes = new Response();
        $this->customerController->delete($deleteReq, $deleteRes);
    } */
}

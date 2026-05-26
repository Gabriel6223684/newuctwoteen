<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use app\controller\Supplier;
use app\controller\Enterprise;

class SuppliersTest extends TestCase
{
    private $supplierController;
    private $enterpriseController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplierController = new Supplier();
        $this->enterpriseController = new Enterprise();
    }

    public function testInsertSupplierValidationErrorMissingEnterpriseId()
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/fornecedor/insert');
        $request = $request->withParsedBody([
            'nome_fantasia' => 'Fornecedor Sem Empresa Vinculada',
            'cpf_cnpj'      => '00.000.000/0001-' . rand(10, 99),
            'ativo'         => '1'
        ]);

        $response = new Response();
        $resultResponse = $this->supplierController->insert($request, $response);

        $this->assertEquals(400, $resultResponse->getStatusCode());

        $body = json_decode((string)$resultResponse->getBody(), true);
        $this->assertFalse($body['status']);
        $this->assertStringContainsString('enterprise_id', $body['msg']);
    }

    public function testInsertSupplierSuccess()
    {
        // 1. Cria uma empresa dinâmica primeiro para garantir um ID real válido no banco de testes
        $cnpjEmpresa = rand(10, 99) . '.111.222/0001-' . rand(10, 99);
        $empresaReq = (new ServerRequestFactory())->createServerRequest('POST', '/enterprise/insert')
            ->withParsedBody([
                'nomeExibicao' => 'Empresa Pai do Fornecedor ' . uniqid(),
                'cnpj'         => $cnpjEmpresa,
                'ativo'        => 'true'
            ]);
        $empresaRes = $this->enterpriseController->insert($empresaReq, new Response());
        $empresaBody = json_decode((string)$empresaRes->getBody(), true);
        $enterpriseId = (int) $empresaBody['id'];

        // 2. Agora insere o fornecedor atrelado a essa empresa criada
        $cnpjFornecedor = rand(10, 99) . '.333.444/0001-' . rand(10, 99);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/fornecedor/insert');
        $request = $request->withParsedBody([
            'enterprise_id' => $enterpriseId,
            'nome_fantasia' => 'Fornecedor Homologado ' . uniqid(),
            'cpf_cnpj'      => $cnpjFornecedor,
            'ativo'         => 'true'
        ]);

        $response = new Response();
        $resultResponse = $this->supplierController->insert($request, $response);

        $this->assertEquals(201, $resultResponse->getStatusCode());

        $body = json_decode((string)$resultResponse->getBody(), true);
        $this->assertTrue($body['status']);
        $this->assertGreaterThan(0, $body['id']);
    }
}

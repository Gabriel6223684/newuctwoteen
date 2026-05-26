<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use app\controller\Country;

class CountryTest extends TestCase
{
    private Country $countryController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->countryController = new Country();
    }

    public function testInsertCountrySuccess(): void
    {
        $suffix = uniqid();

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/pais/insert');

        // O payload deve continuar simulando exatamente o que o formulário envia para o Controller
        $request = $request->withParsedBody([
            'name'     => 'País Teste ' . $suffix,
            'iso_code' => 'TT' . $suffix, // O Controller vai receber 'iso_code' e mapear para 'codigo_iso' no banco
        ]);

        $response = new Response();
        $resultResponse = $this->countryController->insert($request, $response);

        $this->assertSame(201, $resultResponse->getStatusCode());

        $body = json_decode((string) $resultResponse->getBody(), true);
        $this->assertTrue($body['status']);
        $this->assertGreaterThan(0, (int) $body['id']);

        // cleanup - Garante que o teste não vai deixar sujeira no banco
       // $this->cleanupCountry((int) $body['id']);
    }

   /* private function cleanupCountry(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $deleteReq = (new ServerRequestFactory())->createServerRequest('POST', '/pais/delete');
        $deleteReq = $deleteReq->withParsedBody(['id' => $id]);
        $deleteRes = new Response();
        $this->countryController->delete($deleteReq, $deleteRes);
    } */
}

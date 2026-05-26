<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use app\controller\User;

class UserTest extends TestCase
{
    private User $userController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userController = new User();
    }

    private function getValidPayload(string $name, string $email, string $password): array
    {
        return [
            'name'     => $name,
            'nome'     => $name,
            'email'    => $email,
            'password' => $password,
            'senha'    => $password,
            'active'   => 1, // Alterado de true para 1
            'ativo'    => 1, // Alterado de true para 1
            'status'   => 1, // Alterado de true para 1
            'master'   => 1, // Alterado de true para 1
            'admin'    => 0, // Alterado de false para 0
            'is_admin' => 0  // Alterado de false para 0
        ];
    }

    public function testInsertUserSuccess(): void
    {
        $password = 'Senha#1234';
        $email = 'user.' . uniqid() . '.' . rand(1, 999) . '@example.com';
        $name = 'Usuário ' . uniqid();

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/insert');
        $request = $request->withParsedBody($this->getValidPayload($name, $email, $password));

        $response = new Response();
        $resultResponse = $this->userController->insert($request, $response);

        if ($resultResponse->getStatusCode() === 500) {
            $resultResponse->getBody()->rewind();
            echo "\n--- [ERRO REAL] ---\n" . (string) $resultResponse->getBody() . "\n-------------------\n";
        }

        $this->assertSame(201, $resultResponse->getStatusCode());

        $body = json_decode((string) $resultResponse->getBody(), true);
        $this->assertTrue($body['status']);
        $this->assertGreaterThan(0, (int) $body['id']);

        $this->cleanupUser((int) $body['id']);
    }

    public function testInsertUserValidationErrorMissingFields(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/insert');
        $request = $request->withParsedBody([
            'name'     => '',
            'email'    => '',
            'password' => '',
            'active'   => true,
        ]);

        $response = new Response();
        $resultResponse = $this->userController->insert($request, $response);

        $this->assertSame(400, $resultResponse->getStatusCode());
    }

    public function testUpdateUserSuccessAndDeleteIt(): void
    {
        $password = 'Senha#1234';
        $email1 = 'user.' . uniqid() . '.' . rand(1, 999) . '@example.com';
        $name1 = 'Usuário ' . uniqid();

        $insertReq = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/insert');
        $insertReq = $insertReq->withParsedBody($this->getValidPayload($name1, $email1, $password));

        $insertRes = new Response();
        $insertOut = $this->userController->insert($insertReq, $insertRes);
        $insertBody = json_decode((string) $insertOut->getBody(), true);

        $id = isset($insertBody['id']) ? (int) $insertBody['id'] : 0;
        $this->assertGreaterThan(0, $id);

        $email2 = 'user.upd.' . uniqid() . '.' . rand(1, 999) . '@example.com';
        $name2 = 'Usuário Atualizado ' . uniqid();

        $updateReq = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/update');

        $updatePayload = $this->getValidPayload($name2, $email2, $password);
        $updatePayload['id'] = $id;

        $updateReq = $updateReq->withParsedBody($updatePayload);
        $updateRes = new Response();
        $updateOut = $this->userController->update($updateReq, $updateRes);

        $this->assertSame(201, $updateOut->getStatusCode());
        $this->cleanupUser($id);
    }

    public function testUpdateUserWithoutIdReturns403(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/update');
        $request = $request->withParsedBody([
            'name'   => 'Qualquer',
            'email'  => 'any.' . uniqid() . '@example.com',
            'active' => true,
        ]);

        $response = new Response();
        $resultResponse = $this->userController->update($request, $response);

        $this->assertSame(403, $resultResponse->getStatusCode());
    }

    public function testDeleteUserSuccessAndInvalidWithoutId(): void
    {
        $password = 'Senha#1234';
        $email = 'user.' . uniqid() . '.' . rand(1, 999) . '@example.com';
        $name = 'Usuário ' . uniqid();

        $insertReq = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/insert');
        $insertReq = $insertReq->withParsedBody($this->getValidPayload($name, $email, $password));

        $insertRes = new Response();
        $insertOut = $this->userController->insert($insertReq, $insertRes);
        $insertBody = json_decode((string) $insertOut->getBody(), true);

        $id = isset($insertBody['id']) ? (int) $insertBody['id'] : 0;
        $this->assertGreaterThan(0, $id);

        $deleteReq = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/delete');
        $deleteReq = $deleteReq->withParsedBody(['id' => $id]);

        $deleteRes = new Response();
        $deleteOut = $this->userController->delete($deleteReq, $deleteRes);

        $this->assertSame(200, $deleteOut->getStatusCode());

        $deleteReq2 = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/delete');
        $deleteReq2 = $deleteReq2->withParsedBody([]);

        $deleteRes2 = new Response();
        $deleteOut2 = $this->userController->delete($deleteReq2, $deleteRes2);

        $this->assertSame(403, $deleteOut2->getStatusCode());
    }

    private function cleanupUser(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $deleteReq = (new ServerRequestFactory())->createServerRequest('POST', '/usuario/delete');
        $deleteReq = $deleteReq->withParsedBody(['id' => $id]);
        $deleteRes = new Response();
        $this->userController->delete($deleteReq, $deleteRes);
    }
}

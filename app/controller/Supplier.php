<?php

declare(strict_types=1);

namespace app\controller;

final class Supplier extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-supplier'), [
                'titulo' => 'Lista de fornecedores',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    // ... similar CRUD methods following User pattern
    // with supplier fields: nome_fantasia, razao_social, cpf_cnpj, enterprise_id, address_id, active, created_at, updated_at
    
    public function listingdata($request, $response)
    {
        // Similar to User.listingdata, but for suppliers table
        // Add joins for enterprise and address if needed
    }
}


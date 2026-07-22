<?php

namespace Tests\Unit\Authorization;

use App\Http\Controllers\Api\IamV2\ContextRoleAssignmentCatalogueController;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeContextRoleAssignmentCatalogue;

class ContextRoleAssignmentCatalogueControllerTest extends TestCase
{
    public function test_it_returns_assignment_catalogue(): void
    {
        $catalogue = new FakeContextRoleAssignmentCatalogue([
            'identities' => [
                [
                    'id' => 1,
                    'bfrn_user_id' => 19,
                    'label' => 'th***@example.com · ID 1',
                ],
            ],
            'roles' => [
                [
                    'id' => 6,
                    'name' => 'supervisor',
                    'label' => 'Supervisor',
                ],
            ],
            'companies' => [
                [
                    'id' => 25,
                    'name' => 'Nasaco Southern Africa',
                ],
            ],
            'business_units' => [
                [
                    'id' => 1,
                    'company_id' => 25,
                    'name' => 'Nasaco Southern Africa',
                ],
            ],
            'systems' => [
                [
                    'id' => 1,
                    'name' => 'IAM Platform',
                    'slug' => 'iam',
                ],
            ],
        ]);

        $response = (
            new ContextRoleAssignmentCatalogueController()
        )($catalogue);

        $payload = $response->getData(true);

        $this->assertSame(
            200,
            $response->status()
        );

        $this->assertSame(
            'th***@example.com · ID 1',
            $payload['identities'][0]['label']
        );

        $this->assertSame(
            'supervisor',
            $payload['roles'][0]['name']
        );

        $this->assertSame(
            25,
            $payload['business_units'][0]['company_id']
        );
    }
}

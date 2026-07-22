<?php

namespace App\Http\Controllers\Api\IamV2;

use App\Authorization\Contracts\ContextRoleAssignmentCatalogue;
use Illuminate\Http\JsonResponse;

class ContextRoleAssignmentCatalogueController
{
    public function __invoke(
        ContextRoleAssignmentCatalogue $catalogue
    ): JsonResponse {
        return new JsonResponse(
            $catalogue->get()
        );
    }
}

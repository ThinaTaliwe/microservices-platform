<?php

namespace App\Http\Controllers\IamV2;

use Illuminate\Contracts\View\View;

class ContextRoleAssignmentPageController
{
    public function __invoke(): View
    {
        return view(
            'iam-v2.role-assignments.index',
            [
                'assignmentEndpoint' => route(
                    'iam-v2.role-assignments.index'
                ),
                'assignmentStoreEndpoint' => route(
                    'iam-v2.role-assignments.store'
                ),
                'assignmentCatalogueEndpoint' => route(
                    'iam-v2.role-assignments.catalogue'
                ),
            ]
        );
    }
}

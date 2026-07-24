<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use App\Models\User;
use App\Models\VisaApplication;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisaManagerVisaDeleteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_visa_manager_can_edit_but_cannot_delete_visa_application(): void
    {
        $visaManager = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => ProposalRoleMapping::permissions(
                ProposalRoleMapping::VISA_MANAGER,
            ),
        ]);

        $this->actingAs($visaManager);

        $application = new VisaApplication;

        $this->assertTrue(VisaApplicationResource::canViewAny());
        $this->assertTrue(VisaApplicationResource::canCreate());
        $this->assertTrue(
            VisaApplicationResource::canEdit($application),
        );
        $this->assertFalse(
            VisaApplicationResource::canDelete($application),
        );
        $this->assertFalse(
            VisaApplicationResource::canDeleteAny(),
        );
    }

    public function test_visa_delete_header_action_is_bound_to_resource_authorization(): void
    {
        $source = file_get_contents(
            app_path(
                'Filament/Resources/VisaApplications/Pages/EditVisaApplication.php',
            ),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'DeleteAction::make()',
            $source,
        );
        $this->assertStringContainsString(
            '->hidden(',
            $source,
        );
        $this->assertStringContainsString(
            'VisaApplicationResource::canDelete(',
            $source,
        );
        $this->assertStringContainsString(
            '$this->record',
            $source,
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CustomerDocuments\CustomerDocumentResource;
use App\Filament\Resources\CustomerFamilyMembers\CustomerFamilyMemberResource;
use App\Filament\Resources\CustomerNotifications\CustomerNotificationResource;
use App\Filament\Resources\CustomerPaymentUploads\CustomerPaymentUploadResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerFamilyMember;
use App\Models\CustomerNotification;
use App\Models\CustomerPaymentUpload;
use App\Models\User;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmrahManagerCustomerDeleteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_umrah_manager_keeps_customer_operations_without_delete(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => ProposalRoleMapping::permissions(
                ProposalRoleMapping::UMRAH_MANAGER,
            ),
        ]);

        $this->actingAs($user);

        foreach ($this->resources() as [$resource, $record]) {
            $this->assertTrue($resource::canAccess());
            $this->assertTrue(
                $resource::shouldRegisterNavigation(),
            );
            $this->assertTrue($resource::canViewAny());
            $this->assertTrue($resource::canView($record));
            $this->assertTrue($resource::canCreate());
            $this->assertTrue($resource::canEdit($record));
            $this->assertFalse($resource::canDelete($record));
            $this->assertFalse($resource::canDeleteAny());
        }
    }

    public function test_exact_customer_delete_permissions_control_delete_actions(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => [
                'customers_view',
                'customers_create',
                'customers_edit',
                'customers_delete',
                'customer_documents_view',
                'customer_documents_create',
                'customer_documents_edit',
                'customer_documents_delete',
            ],
        ]);

        $this->actingAs($user);

        $this->assertTrue(
            CustomerResource::canDelete(new Customer),
        );
        $this->assertTrue(CustomerResource::canDeleteAny());

        $this->assertTrue(
            CustomerDocumentResource::canDelete(
                new CustomerDocument,
            ),
        );
        $this->assertTrue(
            CustomerDocumentResource::canDeleteAny(),
        );

        $this->assertTrue(
            CustomerFamilyMemberResource::canDelete(
                new CustomerFamilyMember,
            ),
        );
        $this->assertTrue(
            CustomerFamilyMemberResource::canDeleteAny(),
        );

        $this->assertTrue(
            CustomerNotificationResource::canDelete(
                new CustomerNotification,
            ),
        );
        $this->assertTrue(
            CustomerNotificationResource::canDeleteAny(),
        );

        $this->assertFalse(
            CustomerPaymentUploadResource::canDelete(
                new CustomerPaymentUpload,
            ),
        );
        $this->assertFalse(
            CustomerPaymentUploadResource::canDeleteAny(),
        );
    }

    public function test_customer_delete_ui_actions_are_resource_guarded(): void
    {
        $sources = [
            app_path(
                'Filament/Resources/Customers/Pages/EditCustomer.php',
            ) => 'CustomerResource::canDelete(',
            app_path(
                'Filament/Resources/Customers/Tables/CustomersTable.php',
            ) => 'CustomerResource::canDeleteAny()',
            app_path(
                'Filament/Resources/CustomerDocuments/Pages/EditCustomerDocument.php',
            ) => 'CustomerDocumentResource::canDelete(',
            app_path(
                'Filament/Resources/CustomerDocuments/Tables/CustomerDocumentsTable.php',
            ) => 'CustomerDocumentResource::canDelete(',
            app_path(
                'Filament/Resources/CustomerFamilyMembers/Pages/EditCustomerFamilyMember.php',
            ) => 'CustomerFamilyMemberResource::canDelete(',
            app_path(
                'Filament/Resources/CustomerFamilyMembers/Tables/CustomerFamilyMembersTable.php',
            ) => 'CustomerFamilyMemberResource::canDeleteAny()',
            app_path(
                'Filament/Resources/CustomerNotifications/Pages/EditCustomerNotification.php',
            ) => 'CustomerNotificationResource::canDelete(',
            app_path(
                'Filament/Resources/CustomerNotifications/Tables/CustomerNotificationsTable.php',
            ) => 'CustomerNotificationResource::canDeleteAny()',
            app_path(
                'Filament/Resources/CustomerPaymentUploads/Pages/EditCustomerPaymentUpload.php',
            ) => 'CustomerPaymentUploadResource::canDelete(',
        ];

        foreach ($sources as $path => $expected) {
            $source = file_get_contents($path);

            $this->assertIsString($source);
            $this->assertStringContainsString(
                $expected,
                $source,
            );
        }
    }

    public function test_inactive_customer_operator_is_denied(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => false,
            'permissions' => [
                'customers_view',
                'customers_create',
                'customers_edit',
                'customers_delete',
                'customer_documents_view',
                'customer_documents_create',
                'customer_documents_edit',
                'customer_documents_delete',
            ],
        ]);

        $this->actingAs($user);

        foreach ($this->resources() as [$resource, $record]) {
            $this->assertFalse($resource::canAccess());
            $this->assertFalse($resource::canViewAny());
            $this->assertFalse($resource::canCreate());
            $this->assertFalse($resource::canEdit($record));
            $this->assertFalse($resource::canDelete($record));
            $this->assertFalse($resource::canDeleteAny());
        }
    }

    /**
     * @return list<array{0: class-string, 1: object}>
     */
    private function resources(): array
    {
        return [
            [CustomerResource::class, new Customer],
            [
                CustomerDocumentResource::class,
                new CustomerDocument,
            ],
            [
                CustomerFamilyMemberResource::class,
                new CustomerFamilyMember,
            ],
            [
                CustomerNotificationResource::class,
                new CustomerNotification,
            ],
            [
                CustomerPaymentUploadResource::class,
                new CustomerPaymentUpload,
            ],
        ];
    }
}

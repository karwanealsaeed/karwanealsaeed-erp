<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\CustomPages\Dashboard;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\ProposalRoleMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class CustomerSupportUiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_support_dashboard_uses_proposal_label(): void
    {
        $mapping = ProposalRoleMapping::mappings()[
            ProposalRoleMapping::CUSTOMER_SUPPORT
        ];

        $user = User::factory()->create([
            'role' => $mapping['base_role'],
            'permissions' => $mapping['permissions'],
            'is_active' => true,
        ]);

        $this->assertSame(
            ProposalRoleMapping::CUSTOMER_SUPPORT,
            $this->resolveDashboardRoleLabel($user),
        );
    }

    public function test_unmapped_staff_falls_back_to_base_role_label(): void
    {
        $user = User::factory()->create([
            'role' => 'Sales Staff',
            'permissions' => ['customers_view'],
            'is_active' => true,
        ]);

        $this->assertSame(
            'Sales Staff',
            $this->resolveDashboardRoleLabel($user),
        );
    }

    public function test_customer_support_can_edit_but_cannot_delete_ticket(): void
    {
        $mapping = ProposalRoleMapping::mappings()[
            ProposalRoleMapping::CUSTOMER_SUPPORT
        ];

        $user = User::factory()->create([
            'role' => $mapping['base_role'],
            'permissions' => $mapping['permissions'],
            'is_active' => true,
        ]);

        $ticket = SupportTicket::query()->create([
            'name' => 'Controlled Customer',
            'mobile' => '03000000000',
            'subject' => 'Controlled Support Ticket',
            'message' => 'Controlled support workflow test.',
            'priority' => 'Normal',
            'status' => 'Open',
        ]);

        $this->actingAs($user);

        $this->assertTrue(
            SupportTicketResource::canEdit($ticket),
        );
        $this->assertFalse(
            SupportTicketResource::canDelete($ticket),
        );
        $this->assertFalse(
            SupportTicketResource::canDeleteAny(),
        );
    }

    public function test_support_ticket_delete_ui_is_resource_guarded(): void
    {
        $editSource = file_get_contents(
            app_path(
                'Filament/Resources/SupportTickets/Pages/'
                .'EditSupportTicket.php',
            ),
        );
        $tableSource = file_get_contents(
            app_path(
                'Filament/Resources/SupportTickets/Tables/'
                .'SupportTicketsTable.php',
            ),
        );

        $this->assertIsString($editSource);
        $this->assertIsString($tableSource);

        $this->assertMatchesRegularExpression(
            '/DeleteAction::make\(\)\s*'
            .'->visible\(\s*'
            .'fn \(\$record\): bool =>\s*'
            .'SupportTicketResource::canDelete\(\$record\)/s',
            $editSource,
        );

        $this->assertMatchesRegularExpression(
            '/DeleteBulkAction::make\(\)\s*'
            .'->visible\(\s*'
            .'fn \(\): bool =>\s*'
            .'SupportTicketResource::canDeleteAny\(\)/s',
            $tableSource,
        );
    }

    private function resolveDashboardRoleLabel(User $user): string
    {
        $dashboard = app(Dashboard::class);
        $resolver = new ReflectionMethod(
            Dashboard::class,
            'dashboardRoleLabel',
        );
        $resolver->setAccessible(true);

        $value = $resolver->invoke($dashboard, $user);

        $this->assertIsString($value);

        return $value;
    }
}

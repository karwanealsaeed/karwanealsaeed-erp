<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use App\Models\UmrahBooking;
use App\Models\User;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmrahBookingSafeEditWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_uses_real_columns_and_preserves_legacy_draft_editing(): void
    {
        $form = file_get_contents(
            app_path(
                'Filament/Resources/UmrahBookings/Schemas/UmrahBookingForm.php',
            ),
        );
        $model = file_get_contents(
            app_path('Models/UmrahBooking.php'),
        );

        $this->assertIsString($form);
        $this->assertIsString($model);

        $this->assertStringContainsString(
            "'Draft' => 'Draft'",
            $form,
        );
        $this->assertStringContainsString(
            "DatePicker::make('travel_date')",
            $form,
        );
        $this->assertStringNotContainsString(
            "DatePicker::make('departure_date')",
            $form,
        );
        $this->assertStringContainsString(
            "TextInput::make('madinah_hotel')",
            $form,
        );
        $this->assertStringNotContainsString(
            "TextInput::make('madina_hotel')",
            $form,
        );
        $this->assertStringContainsString(
            "fn (string \$operation): bool => \$operation === 'create'",
            $form,
        );
        $this->assertStringContainsString(
            "'travel_date' => 'date'",
            $model,
        );
        $this->assertStringNotContainsString(
            "'departure_date' => 'date'",
            $model,
        );
    }

    public function test_umrah_manager_can_edit_but_cannot_delete_booking(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => ProposalRoleMapping::permissions(
                ProposalRoleMapping::UMRAH_MANAGER,
            ),
        ]);

        $this->actingAs($user);

        $booking = new UmrahBooking([
            'status' => 'Draft',
            'notes' => 'Safe workflow test',
        ]);

        $this->assertTrue(
            UmrahBookingResource::canEdit($booking),
        );
        $this->assertFalse(
            UmrahBookingResource::canDelete($booking),
        );
        $this->assertFalse(
            UmrahBookingResource::canDeleteAny(),
        );
    }

    public function test_delete_actions_are_bound_to_resource_authorization(): void
    {
        $editPage = file_get_contents(
            app_path(
                'Filament/Resources/UmrahBookings/Pages/EditUmrahBooking.php',
            ),
        );
        $table = file_get_contents(
            app_path(
                'Filament/Resources/UmrahBookings/Tables/UmrahBookingsTable.php',
            ),
        );

        $this->assertIsString($editPage);
        $this->assertIsString($table);

        $this->assertStringContainsString(
            'UmrahBookingResource::canDelete(',
            $editPage,
        );
        $this->assertStringContainsString(
            'UmrahBookingResource::canDeleteAny()',
            $table,
        );
    }

    public function test_notes_only_model_change_does_not_mark_seat_fields_dirty(): void
    {
        $booking = new UmrahBooking([
            'umrah_package_id' => 4,
            'seats_count' => 1,
            'status' => 'Draft',
            'package_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'notes' => 'Original',
        ]);

        $booking->syncOriginal();
        $booking->notes = 'Updated';

        $this->assertTrue($booking->isDirty('notes'));
        $this->assertFalse(
            $booking->isDirty('umrah_package_id'),
        );
        $this->assertFalse(
            $booking->isDirty('seats_count'),
        );
        $this->assertFalse($booking->isDirty('status'));
    }
}

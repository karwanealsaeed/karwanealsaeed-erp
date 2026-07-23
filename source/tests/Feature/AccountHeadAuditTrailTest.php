<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountHead;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

final class AccountHeadAuditTrailTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_account_head_creation_is_recorded_in_central_activity_log(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $this->actingAs($admin);

        $head =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-AUDIT-HEAD-001',
                    'name' => 'Audited Bank Head',
                    'notes' => 'Creation audit test',
                ],
            );

        $log =
            ActivityLog::query()
                ->where(
                    'subject_type',
                    AccountHead::class,
                )
                ->where(
                    'subject_id',
                    $head->id,
                )
                ->where(
                    'action',
                    'Created',
                )
                ->latest('id')
                ->firstOrFail();

        $this->assertSame(
            $admin->id,
            $log->user_id,
        );
        $this->assertSame(
            'Chart of Accounts',
            $log->module,
        );
        $this->assertSame(
            'Audited Bank Head',
            $log->record_title,
        );
        $this->assertNull(
            $log->old_values,
        );
        $this->assertSame(
            'A-AUDIT-HEAD-001',
            $log->new_values['code'] ?? null,
        );
        $this->assertSame(
            'Audited Bank Head',
            $log->new_values['name'] ?? null,
        );
    }

    public function test_account_head_update_records_only_changed_values(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $this->actingAs($admin);

        $head =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-AUDIT-HEAD-002',
                    'name' => 'Original Head Name',
                    'notes' => 'Original note',
                ],
            );

        $head->update([
            'name' => 'Updated Head Name',
            'notes' => 'Updated note',
        ]);

        $log =
            ActivityLog::query()
                ->where(
                    'subject_type',
                    AccountHead::class,
                )
                ->where(
                    'subject_id',
                    $head->id,
                )
                ->where(
                    'action',
                    'Updated',
                )
                ->latest('id')
                ->firstOrFail();

        $this->assertSame(
            'Chart of Accounts',
            $log->module,
        );
        $this->assertSame(
            'Original Head Name',
            $log->old_values['name'] ?? null,
        );
        $this->assertSame(
            'Updated Head Name',
            $log->new_values['name'] ?? null,
        );
        $this->assertSame(
            'Original note',
            $log->old_values['notes'] ?? null,
        );
        $this->assertSame(
            'Updated note',
            $log->new_values['notes'] ?? null,
        );
        $this->assertArrayNotHasKey(
            'code',
            $log->old_values ?? [],
        );
        $this->assertArrayNotHasKey(
            'code',
            $log->new_values ?? [],
        );
    }

    public function test_account_head_deletion_is_recorded_with_original_values(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $this->actingAs($admin);

        $head =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Other',
                overrides: [
                    'code' => 'A-AUDIT-HEAD-003',
                    'name' => 'Disposable Audit Head',
                    'notes' => 'Deletion audit test',
                ],
            );

        $headId = $head->id;

        $head->delete();

        $log =
            ActivityLog::query()
                ->where(
                    'subject_type',
                    AccountHead::class,
                )
                ->where(
                    'subject_id',
                    $headId,
                )
                ->where(
                    'action',
                    'Deleted',
                )
                ->latest('id')
                ->firstOrFail();

        $this->assertSame(
            'Chart of Accounts',
            $log->module,
        );
        $this->assertSame(
            'Disposable Audit Head',
            $log->record_title,
        );
        $this->assertSame(
            'A-AUDIT-HEAD-003',
            $log->old_values['code'] ?? null,
        );
        $this->assertSame(
            'Disposable Audit Head',
            $log->old_values['name'] ?? null,
        );
        $this->assertNull(
            $log->new_values,
        );
    }
}
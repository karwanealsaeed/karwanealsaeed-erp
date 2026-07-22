<?php

namespace Tests\Feature;

use App\Models\AccountHead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class ChartOfAccountsIntegrityTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_parent_and_child_accounts_preserve_hierarchy(): void
    {
        $parent = $this->createAccountingHead(
            type: 'Asset',
            overrides: [
                'code' => 'A-TEST-100',
                'name' => 'Test Current Assets',
            ]
        );

        $childOne = $this->createAccountingHead(
            type: 'Asset',
            subType: 'Cash',
            overrides: [
                'code' => 'A-TEST-110',
                'name' => 'Test Cash',
                'parent_id' => $parent->id,
            ]
        );

        $childTwo = $this->createAccountingHead(
            type: 'Asset',
            subType: 'Bank',
            overrides: [
                'code' => 'A-TEST-120',
                'name' => 'Test Bank',
                'parent_id' => $parent->id,
            ]
        );

        $this->assertTrue(
            $childOne->parent->is($parent)
        );

        $this->assertTrue(
            $childTwo->parent->is($parent)
        );

        $this->assertSame(
            [
                'A-TEST-110',
                'A-TEST-120',
            ],
            $parent->children()
                ->orderBy('code')
                ->pluck('code')
                ->all()
        );

        $this->assertSame(
            2,
            AccountHead::query()
                ->where('parent_id', $parent->id)
                ->count()
        );
    }
}
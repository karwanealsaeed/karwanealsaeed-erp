<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentationCompletenessTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function requiredDocuments(): array
    {
        return [
            'docs/system-requirements-specification.md',
            'docs/deployment-guide.md',
            'docs/backup-restore-guide.md',
            'docs/admin-user-manual.md',
            'docs/customer-agent-user-guide.md',
            'docs/final-uat-checklist.md',
            'docs/role-permission-mapping.md',
        ];
    }

    public function test_required_project_documents_exist_and_are_substantial(): void
    {
        foreach ($this->requiredDocuments() as $path) {
            $absolute =
                base_path($path);

            $this->assertFileExists(
                $absolute,
            );

            $contents =
                file_get_contents($absolute);

            $this->assertIsString($contents);

            $this->assertGreaterThan(
                1000,
                strlen($contents),
                "{$path} is too short to be an operational document.",
            );

            $this->assertStringStartsWith(
                '# ',
                $contents,
            );
        }
    }

    public function test_readme_indexes_every_required_document(): void
    {
        $readme =
            file_get_contents(
                base_path('README.md'),
            );

        $this->assertIsString($readme);

        foreach ($this->requiredDocuments() as $path) {
            $this->assertStringContainsString(
                $path,
                $readme,
            );
        }

        $this->assertStringContainsString(
            'docs/karwan-ai-final-uat.md',
            $readme,
        );
    }

    public function test_deployment_and_uat_documents_keep_live_release_blocked(): void
    {
        $deployment =
            file_get_contents(
                base_path(
                    'docs/deployment-guide.md',
                ),
            );

        $uat =
            file_get_contents(
                base_path(
                    'docs/final-uat-checklist.md',
                ),
            );

        $this->assertIsString($deployment);
        $this->assertIsString($uat);

        $this->assertStringContainsString(
            'Live deployment is not authorized',
            $deployment,
        );

        $this->assertStringContainsString(
            'Live deployment is not authorized',
            $uat,
        );

        $this->assertStringContainsString(
            'Project owner explicitly authorizes production deployment',
            $uat,
        );
    }

    public function test_role_documentation_references_the_executable_contract_and_all_labels(): void
    {
        $roleDocument =
            file_get_contents(
                base_path(
                    'docs/role-permission-mapping.md',
                ),
            );

        $this->assertIsString($roleDocument);

        $this->assertStringContainsString(
            'App\Support\ProposalRoleMapping',
            $roleDocument,
        );

        foreach (
            [
                'Super Admin',
                'Director',
                'Accountant',
                'Ticketing Manager',
                'Visa Manager',
                'Umrah Manager',
                'HR Manager',
                'Marketing Manager',
                'Customer Support',
                'Customer',
            ]
            as $label
        ) {
            $this->assertStringContainsString(
                $label,
                $roleDocument,
            );
        }
    }
}
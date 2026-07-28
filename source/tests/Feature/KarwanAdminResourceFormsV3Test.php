<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminResourceFormsV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    public function test_resource_forms_v3_css_covers_real_filament_surfaces(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2C2 ADMIN CREATE EDIT FORMS INPUTS UPLOADS MODALS V3 START',
                'PHASE 16F-2C2 ADMIN CREATE EDIT FORMS INPUTS UPLOADS MODALS V3 END',
                '.fi-resource-create-record-page',
                '.fi-resource-edit-record-page',
                '.fi-fo',
                '.fi-fo-component-ctn',
                '.fi-fo-field-wrp',
                '.fi-fo-field-wrp-label',
                '.fi-fo-field-wrp-helper-text',
                '.fi-fo-field-wrp-error-message',
                '.fi-input-wrp',
                '.fi-input',
                '.fi-select-input',
                '.fi-fo-textarea',
                '.fi-fo-file-upload',
                '.fi-fo-repeater',
                '.fi-fo-builder',
                '.fi-fo-rich-editor',
                '.fi-fo-markdown-editor',
                '.fi-fo-tags-input',
                '.fi-fo-key-value',
                '.fi-fo-toggle',
                '.fi-fo-checkbox-list',
                '.fi-fo-radio',
                '.fi-section',
                '.fi-fieldset',
                '.fi-tabs',
                '.fi-wizard',
                '.fi-fo-actions',
                '.fi-modal-window',
                '.fi-modal-header',
                '.fi-modal-footer',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }

        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2C2 ADMIN CREATE EDIT FORMS INPUTS UPLOADS MODALS V3 START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2C2 ADMIN CREATE EDIT FORMS INPUTS UPLOADS MODALS V3 END',
            ),
        );
    }

    public function test_resource_forms_use_public_v3_responsive_accessible_contracts(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'var(--display)',
                'var(--gold)',
                'var(--green)',
                'var(--green-2)',
                'var(--ink)',
                'var(--muted)',
                'var(--line)',
                'radial-gradient(',
                'linear-gradient(',
                ':focus-within',
                ':focus-visible',
                '[aria-invalid="true"]',
                ':disabled',
                '@media (max-width: 1024px)',
                '@media (max-width: 760px)',
                'html[dir="rtl"]',
                '@media (prefers-reduced-motion: reduce)',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_admin_create_edit_route_inventory_is_exact(): void
    {
        $routes = Route::getRoutes();
        $adminNames = [];
        $agentNames = [];
        $createNames = [];
        $editNames = [];
        $viewNames = [];

        foreach ($routes as $route) {
            $name = (string) ($route->getName() ?? '');

            if (str_starts_with($name, 'filament.admin.')) {
                $adminNames[] = $name;
            }

            if (str_starts_with($name, 'customer.portal.')) {
                $agentNames[] = $name;
            }

            if (
                str_starts_with(
                    $name,
                    'filament.admin.resources.',
                )
                && str_ends_with($name, '.create')
            ) {
                $createNames[] = $name;
            }

            if (
                str_starts_with(
                    $name,
                    'filament.admin.resources.',
                )
                && str_ends_with($name, '.edit')
            ) {
                $editNames[] = $name;
            }

            if (
                str_starts_with(
                    $name,
                    'filament.admin.resources.',
                )
                && str_ends_with($name, '.view')
            ) {
                $viewNames[] = $name;
            }
        }

        $adminNames =
            array_values(array_unique($adminNames));
        $agentNames =
            array_values(array_unique($agentNames));
        sort($adminNames);

        $json = json_encode(
            $adminNames,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE,
        );

        $this->assertIsString($json);
        $this->assertCount(430, $routes);
        $this->assertCount(313, $adminNames);
        $this->assertCount(36, $agentNames);
        $this->assertCount(64, array_unique($createNames));
        $this->assertCount(65, array_unique($editNames));
        $this->assertCount(67, array_unique($viewNames));
        $this->assertSame(
            self::ADMIN_ROUTE_HASH,
            hash('sha256', $json),
            'Admin named-route inventory changed.',
        );

        foreach (
            [
                'filament.admin.resources.account-heads.create',
                'filament.admin.resources.customers.create',
                'filament.admin.resources.flight-bookings.create',
                'filament.admin.resources.visa-applications.create',
            ] as $routeName
        ) {
            $this->assertContains(
                $routeName,
                $adminNames,
            );
        }
    }
}

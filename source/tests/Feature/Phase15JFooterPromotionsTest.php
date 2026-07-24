<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerPromotionController;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Tests\TestCase;

class Phase15JFooterPromotionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_controller_reads_customer_portal_session_key(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Phase 15J Portal Agent',
            'mobile' => '03000000991',
            'whatsapp' => '03000000991',
            'email' => 'phase15j-promotions@example.test',
            'address' => 'Phase 15J controlled test address',
            'city' => 'Chichawatni',
            'country' => 'Pakistan',
            'customer_type' => 'Agent',
            'status' => 'Active',
            'portal_password' => Hash::make(
                'PortalPassword123!',
            ),
            'portal_registered_at' => now(),
        ]);

        $request = Request::create(
            '/customer/promotions',
            'GET',
        );

        $session = app('session')->driver();
        $session->start();
        $session->put(
            'customer_portal_id',
            $customer->id,
        );

        $request->setLaravelSession($session);

        $view = app(
            CustomerPromotionController::class,
        )($request);

        $this->assertInstanceOf(
            View::class,
            $view,
        );

        $this->assertSame(
            'customer-portal.promotions',
            $view->name(),
        );

        $this->assertTrue(
            $view->getData()['customer']->is($customer),
        );
    }

    public function test_public_footer_contains_travel_insurance_link(): void
    {
        $response = $this->get(
            route('website.home'),
        );

        $response->assertOk();

        $html = (string) $response->getContent();
        $footerStart = strpos($html, '<footer');
        $footerEnd = $footerStart === false
            ? false
            : strpos(
                $html,
                '</footer>',
                $footerStart,
            );

        $this->assertNotFalse(
            $footerStart,
            'Public website footer was not rendered.',
        );

        $this->assertNotFalse(
            $footerEnd,
            'Public website footer closing tag was not rendered.',
        );

        $footerHtml = substr(
            $html,
            (int) $footerStart,
            ((int) $footerEnd - (int) $footerStart)
                + strlen('</footer>'),
        );

        $this->assertStringContainsString(
            route('website.insurance'),
            $footerHtml,
        );
    }

    public function test_mobile_and_footer_service_lists_both_include_insurance(): void
    {
        $layoutSource = (string) file_get_contents(
            resource_path(
                'views/website/layout.blade.php',
            ),
        );

        $this->assertSame(
            2,
            substr_count(
                $layoutSource,
                "route('website.insurance')",
            ),
        );
    }
}
